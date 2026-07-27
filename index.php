<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

use App\Core\{App, DB};
use App\Models\{Tenant, User, Event, EventType, Plan, Subscription, Media, QrCode};
use App\Services\{QrService, EmailService};

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ─── Auth helpers ─────────────────────────────────────────
function requireAuth(): void
{
    if (empty($_SESSION['user_id'])) {
        App::redirect('/login');
    }
    $user = User::find((int)$_SESSION['user_id']);
    if (!$user || !$user['is_active']) {
        session_destroy();
        App::redirect('/login');
    }
    App::setUser($user);
    if ($user['tenant_id']) {
        $tenant = Tenant::find($user['tenant_id']);
        App::setTenant($tenant);
        $sub = Subscription::active($user['tenant_id']);
        App::setPlan($sub);
    }
}

function clientIp(): string
{
    // Behind nginx: REMOTE_ADDR is the proxy, real IP is in X-Forwarded-For (first hop)
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff) return trim(explode(',', $xff)[0]);
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/** Returns minutes to wait if throttled, or null if allowed. */
function loginThrottled(string $email): ?int
{
    $row = DB::fetchOne(
        "SELECT COUNT(*) AS n, EXTRACT(EPOCH FROM (MAX(attempted_at) + INTERVAL '15 minutes' - NOW())) AS wait_s
         FROM login_attempts
         WHERE success = FALSE
           AND attempted_at > NOW() - INTERVAL '15 minutes'
           AND (email = ? OR ip_address = ?)",
        [$email, clientIp()]
    );
    if ($row && (int)$row['n'] >= 5) {
        return max(1, (int)ceil(((float)$row['wait_s']) / 60));
    }
    return null;
}

function recordLoginAttempt(string $email, bool $success): void
{
    DB::insert('login_attempts', [
        'email'      => mb_substr($email, 0, 255),
        'ip_address' => mb_substr(clientIp(), 0, 45),
        'success'    => $success ? 'true' : 'false',
    ]);
    if ($success) {
        DB::query("DELETE FROM login_attempts WHERE email = ? AND success = FALSE", [$email]);
    }
    // Opportunistic cleanup of stale rows
    if (random_int(1, 50) === 1) {
        DB::query("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL '1 day'");
    }
}

function requireSuperAdmin(): void
{
    requireAuth();
    if (!App::isSuperAdmin()) App::abort(403, 'Forbidden');
}

function requireTenant(): void
{
    requireAuth();
    if (!App::tenant()) App::redirect('/login');
    if (App::tenant()['status'] === 'suspended') {
        App::render('auth/verify', ['error' => 'Your account has been suspended. Please contact support.', 'pageTitle' => 'Suspended'], 'layouts/auth');
    }
}

// ─── QR Code redirect (scan handler) ─────────────────────
if (preg_match('#^/qr/([a-f0-9-]+)$#', $uri, $m)) {
    $qr = QrCode::findByCode($m[1]);
    if (!$qr || $qr['tenant_status'] !== 'active') App::abort(404);
    if ($qr['event_status'] !== 'published') App::abort(404);

    QrCode::recordScan($qr['id'], $qr['event_id'], $qr['tenant_id']);

    $dest = $qr['qr_destination'] ?? 'event_page';
    if ($dest === 'event_page' || $dest === '') {
        App::redirect("/e/{$qr['tenant_slug']}/{$qr['event_slug']}");
    } elseif ($dest === 'custom_url' && $qr['qr_custom_url']) {
        App::redirect($qr['qr_custom_url']);
    } else {
        App::redirect("/e/{$qr['tenant_slug']}/{$qr['event_slug']}");
    }
}

// ─── Legacy: /brochure/:slug (backward compat) ───────────
if (preg_match('#^/brochure/([a-z0-9-]+)$#', $uri, $m)) {
    $event = Event::findLegacyBySlug($m[1]);
    if (!$event) App::abort(404, 'Not found');

    $pdf = DB::fetchOne("SELECT * FROM media WHERE event_id = ? AND type = 'pdf' LIMIT 1", [$event['id']]);
    $gallery = DB::fetchAll("SELECT * FROM media WHERE event_id = ? AND type = 'gallery'", [$event['id']]);

    $qr = Event::getQrCode($event['id']);
    if ($qr) {
        QrCode::recordScan((int)$qr['id'], $event['id'], $event['tenant_id']);
    } else {
        Event::incrementScans($event['id']);
    }

    App::render('public/event', [
        'event'           => $event,
        'pdf'             => $pdf,
        'gallery'         => $gallery,
        'pageTitle'       => $event['name'],
        'metaDescription' => $event['description'] ?? '',
        'brandPrimary'    => $event['brand_color_primary'] ?? '#212529',
        'brandSecondary'  => $event['brand_color_secondary'] ?? '#6c757d',
        'tenantFooter'    => $event['footer_text'] ?? '',
    ], 'layouts/event-public');
}

// ─── Legacy: /brochure/:slug/pdf ──────────────────────────
if (preg_match('#^/brochure/([a-z0-9-]+)/pdf$#', $uri, $m)) {
    $event = Event::findLegacyBySlug($m[1]);
    if (!$event) App::abort(404);
    $pdf = DB::fetchOne("SELECT * FROM media WHERE event_id = ? AND type = 'pdf' LIMIT 1", [$event['id']]);
    if (!$pdf) App::abort(404);
    $path = BASE_DIR . '/' . $pdf['file_path'];
    if (!file_exists($path)) App::abort(404);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9 ._-]/', '', $pdf['original_name']) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// ─── Public event page: /e/:tenant/:event ─────────────────
if (preg_match('#^/e/([a-z0-9-]+)/([a-z0-9-]+)$#', $uri, $m)) {
    $event = Event::findPublicBySlug($m[1], $m[2]);
    if (!$event) App::abort(404);

    $pdf = DB::fetchOne("SELECT * FROM media WHERE event_id = ? AND type = 'pdf' LIMIT 1", [$event['id']]);
    $gallery = DB::fetchAll("SELECT * FROM media WHERE event_id = ? AND type = 'gallery'", [$event['id']]);

    App::render('public/event', [
        'event'           => $event,
        'pdf'             => $pdf,
        'gallery'         => $gallery,
        'pageTitle'       => $event['name'],
        'metaDescription' => $event['description'] ?? '',
        'ogImage'         => $event['cover_image'] ? APP_URL . '/' . $event['cover_image'] : '',
        'brandPrimary'    => $event['brand_color_primary'] ?? '#212529',
        'brandSecondary'  => $event['brand_color_secondary'] ?? '#6c757d',
        'tenantFooter'    => $event['footer_text'] ?? '',
    ], 'layouts/event-public');
}

// ─── Auth: Login ──────────────────────────────────────────
if ($uri === '/login') {
    if (!empty($_SESSION['user_id'])) {
        $u = User::find((int)$_SESSION['user_id']);
        App::redirect($u && $u['role'] === 'super_admin' ? '/superadmin' : '/dashboard');
    }
    $error = null;
    if ($method === 'POST') {
        App::verifyCsrf();
        $email = trim($_POST['email'] ?? '');
        $wait = loginThrottled($email);
        if ($wait !== null) {
            $error = "Too many failed attempts. Please try again in {$wait} minute(s).";
        } else {
            $user = User::attempt($email, $_POST['password'] ?? '');
            if ($user) {
                if (!$user['email_verified_at'] && $user['role'] !== 'super_admin') {
                    $error = 'Please verify your email first.';
                } else {
                    recordLoginAttempt($email, true);
                    $_SESSION['user_id'] = $user['id'];
                    session_regenerate_id(true);
                    App::log('LOGIN', 'user', $user['id']);
                    App::redirect($user['role'] === 'super_admin' ? '/superadmin' : '/dashboard');
                }
            } else {
                recordLoginAttempt($email, false);
                $error = 'Invalid email or password';
            }
        }
    }
    App::render('auth/login', ['error' => $error, 'pageTitle' => 'Login'], 'layouts/auth');
}

// ─── Auth: Register ───────────────────────────────────────
if ($uri === '/register') {
    if (App::setting('registration_enabled', '1') !== '1') {
        App::render('auth/verify', ['error' => 'Registration is currently disabled.', 'pageTitle' => 'Registration Disabled'], 'layouts/auth');
    }
    $error = null;
    if ($method === 'POST') {
        App::verifyCsrf();
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $passConfirm = $_POST['password_confirm'] ?? '';
        $name = trim($_POST['contact_person'] ?? '');
        $bizName = trim($_POST['business_name'] ?? '');

        if (!$email || !$pass || !$name || !$bizName) { $error = 'All required fields must be filled'; }
        elseif (strlen($pass) < 8) { $error = 'Password must be at least 8 characters'; }
        elseif ($pass !== $passConfirm) { $error = 'Passwords do not match'; }
        elseif (User::findByEmail($email)) { $error = 'An account with this email already exists'; }
        else {
            $tenantSlug = Tenant::generateSlug($bizName);
            $tenantId = Tenant::create([
                'name'           => $bizName,
                'slug'           => $tenantSlug,
                'account_type'   => $_POST['account_type'] ?? 'individual',
                'contact_person' => $name,
                'email'          => $email,
                'phone'          => trim($_POST['phone'] ?? ''),
                'country'        => trim($_POST['country'] ?? 'Ghana'),
                'business_name'  => $bizName,
                'status'         => 'active',
            ]);

            $verifyToken = User::generateVerificationToken();
            User::create([
                'tenant_id'          => $tenantId,
                'email'              => $email,
                'password'           => $pass,
                'name'               => $name,
                'phone'              => trim($_POST['phone'] ?? ''),
                'role'               => 'tenant_owner',
                'verification_token' => $verifyToken,
            ]);

            $defaultPlan = Plan::getDefault();
            if ($defaultPlan) {
                Subscription::create($tenantId, $defaultPlan['id'], 'monthly', $defaultPlan['trial_days'] > 0);
            }

            if (!empty($_POST['referral_code'])) {
                $referrer = Tenant::findByReferralCode($_POST['referral_code']);
                if ($referrer) {
                    Tenant::update($tenantId, ['referred_by' => $referrer['id']]);
                    DB::insert('referrals', [
                        'referrer_tenant_id' => $referrer['id'],
                        'referred_tenant_id' => $tenantId,
                        'status'             => 'converted',
                        'reward_type'        => 'free_days',
                        'reward_value'       => 30,
                        'converted_at'       => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            EmailService::sendVerification($email, $name, $verifyToken);
            App::log('REGISTER', 'tenant', $tenantId);
            App::redirect('/verify', 'Registration successful! Please check your email to verify your account.');
        }
    }
    App::render('auth/register', ['error' => $error, 'pageTitle' => 'Register'], 'layouts/auth');
}

// ─── Auth: Verify email ───────────────────────────────────
if ($uri === '/verify') {
    $token = $_GET['token'] ?? null;
    $verified = false;
    $error = null;
    if ($token) {
        $user = User::findByToken('verification_token', $token);
        if ($user) {
            User::update($user['id'], [
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'verification_token' => null,
            ]);
            $verified = true;
            if ($user['tenant_id']) {
                EmailService::sendWelcome($user['email'], $user['name'], 'Free');
            }
        } else {
            $error = 'Invalid or expired verification link.';
        }
    }
    App::render('auth/verify', ['verified' => $verified, 'error' => $error, 'pageTitle' => 'Verify Email'], 'layouts/auth');
}

// ─── Auth: Forgot password ────────────────────────────────
if ($uri === '/forgot-password') {
    $sent = false;
    $error = null;
    if ($method === 'POST') {
        App::verifyCsrf();
        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);
        if ($user) {
            $token = User::generateResetToken();
            User::update($user['id'], [
                'reset_token'      => $token,
                'reset_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ]);
            EmailService::sendPasswordReset($user['email'], $user['name'], $token);
        }
        $sent = true;
    }
    App::render('auth/forgot-password', ['sent' => $sent, 'error' => $error, 'pageTitle' => 'Forgot Password'], 'layouts/auth');
}

// ─── Auth: Reset password ─────────────────────────────────
if ($uri === '/reset-password') {
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    $error = null;
    $done = false;
    if ($method === 'POST') {
        App::verifyCsrf();
        $user = User::findByToken('reset_token', $token);
        if (!$user || strtotime($user['reset_expires_at']) < time()) {
            $error = 'Invalid or expired reset link.';
        } else {
            $pass = $_POST['password'] ?? '';
            if (strlen($pass) < 8) {
                $error = 'Password must be at least 8 characters';
            } else {
                User::update($user['id'], ['password' => $pass, 'reset_token' => null, 'reset_expires_at' => null]);
                $done = true;
            }
        }
    }
    App::render('auth/forgot-password', ['sent' => $done, 'error' => $error, 'pageTitle' => 'Reset Password'], 'layouts/auth');
}

if ($uri === '/logout') {
    session_destroy();
    App::redirect('/login');
}

// ─── Root redirect ────────────────────────────────────────
if ($uri === '/') {
    if (!empty($_SESSION['user_id'])) {
        $u = User::find((int)$_SESSION['user_id']);
        App::redirect($u && $u['role'] === 'super_admin' ? '/superadmin' : '/dashboard');
    }
    App::redirect('/login');
}

// ════════════════════════════════════════════════════════════
// SUPER ADMIN ROUTES
// ════════════════════════════════════════════════════════════
if (str_starts_with($uri, '/superadmin')) {
    requireSuperAdmin();

    // Dashboard
    if ($uri === '/superadmin') {
        $tenantStats = Tenant::stats();
        $eventStats = Event::platformStats();
        $revenue = (float)(DB::fetchOne("SELECT COALESCE(SUM(amount),0) as t FROM platform_payments WHERE status='completed'")['t'] ?? 0);
        $recentTenants = Tenant::all('', '', 5);
        $totalStorage = DB::fetchOne("SELECT COALESCE(SUM(storage_used),0) as t FROM tenants")['t'] ?? 0;
        $storageUsed = $totalStorage > 1073741824 ? round($totalStorage / 1073741824, 1) . ' GB' : round($totalStorage / 1048576, 1) . ' MB';

        App::render('superadmin/dashboard', [
            'activePage'     => 'dashboard',
            'pageTitle'      => 'Dashboard',
            'tenantStats'    => $tenantStats,
            'eventStats'     => $eventStats,
            'revenue'        => $revenue,
            'recentTenants'  => $recentTenants,
            'storageUsed'    => $storageUsed,
            'maintenanceMode' => App::setting('maintenance_mode') === '1',
        ], 'layouts/admin');
    }

    // Tenants list
    if ($uri === '/superadmin/tenants') {
        $search = trim($_GET['q'] ?? '');
        $filterStatus = $_GET['status'] ?? '';
        $tenants = Tenant::all($search, $filterStatus);
        App::render('superadmin/tenants', [
            'activePage'    => 'tenants',
            'pageTitle'     => 'Tenants',
            'tenants'       => $tenants,
            'search'        => $search,
            'filterStatus'  => $filterStatus,
            'totalTenants'  => Tenant::count(),
        ], 'layouts/admin');
    }

    // Tenant detail
    if (preg_match('#^/superadmin/tenants/(\d+)$#', $uri, $m) && $method === 'GET') {
        $tenant = Tenant::find((int)$m[1]);
        if (!$tenant) App::abort(404);
        $users   = DB::fetchAll("SELECT * FROM users WHERE tenant_id = ? ORDER BY created_at", [$tenant['id']]);
        $events  = DB::fetchAll("SELECT * FROM events WHERE tenant_id = ? ORDER BY created_at DESC", [$tenant['id']]);
        $sub     = Tenant::getSubscription($tenant['id']);
        $scans   = DB::fetchOne("SELECT COALESCE(SUM(total_scans),0) AS total FROM events WHERE tenant_id = ?", [$tenant['id']]);
        $storage = round(($tenant['storage_used'] ?? 0) / 1048576, 2);
        App::render('superadmin/tenant-detail', [
            'activePage'  => 'tenants',
            'pageTitle'   => $tenant['name'],
            'tenant'      => $tenant,
            'users'       => $users,
            'events'      => $events,
            'subscription'=> $sub,
            'totalScans'  => (int)($scans['total'] ?? 0),
            'storageMB'   => $storage,
        ], 'layouts/admin');
    }

    // Tenant actions
    if (preg_match('#^/superadmin/tenants/(\d+)/(activate|suspend|delete|impersonate)$#', $uri, $m) && $method === 'POST') {
        App::verifyCsrf();
        $tid = (int)$m[1];
        $action = $m[2];
        if ($action === 'activate') {
            Tenant::update($tid, ['status' => 'active']);
            App::redirect('/superadmin/tenants', 'Tenant activated');
        } elseif ($action === 'suspend') {
            Tenant::update($tid, ['status' => 'suspended']);
            App::redirect('/superadmin/tenants', 'Tenant suspended');
        } elseif ($action === 'delete') {
            Tenant::delete($tid);
            App::redirect('/superadmin/tenants', 'Tenant deleted');
        } elseif ($action === 'impersonate') {
            $owner = DB::fetchOne("SELECT id FROM users WHERE tenant_id = ? AND role = 'tenant_owner' LIMIT 1", [$tid]);
            if ($owner) {
                $_SESSION['impersonator_id'] = $_SESSION['user_id'];
                $_SESSION['user_id'] = $owner['id'];
                User::update($owner['id'], ['impersonated_by' => (int)$_SESSION['impersonator_id']]);
                App::redirect('/dashboard');
            }
            App::redirect('/superadmin/tenants', 'No owner found for this tenant', 'danger');
        }
    }

    // Stop impersonation
    if ($uri === '/superadmin/stop-impersonate') {
        if (!empty($_SESSION['impersonator_id'])) {
            $currentUserId = $_SESSION['user_id'];
            User::update($currentUserId, ['impersonated_by' => null]);
            $_SESSION['user_id'] = $_SESSION['impersonator_id'];
            unset($_SESSION['impersonator_id']);
        }
        App::redirect('/superadmin/tenants');
    }

    // Plans
    if ($uri === '/superadmin/plans') {
        $plans = Plan::all();
        $subscriberCounts = [];
        foreach ($plans as $p) {
            $subscriberCounts[$p['id']] = Plan::subscriberCount($p['id']);
        }
        App::render('superadmin/plans', [
            'activePage'       => 'plans',
            'pageTitle'        => 'Subscription Plans',
            'plans'            => $plans,
            'subscriberCounts' => $subscriberCounts,
        ], 'layouts/admin');
    }

    // Create / edit plan
    if ($uri === '/superadmin/plans/create' || preg_match('#^/superadmin/plans/(\d+)/edit$#', $uri, $m)) {
        $planId = isset($m[1]) ? (int)$m[1] : null;
        $plan = $planId ? Plan::find($planId) : null;
        $error = null;

        if ($method === 'POST') {
            App::verifyCsrf();
            $data = [
                'name'              => trim($_POST['name'] ?? ''),
                'slug'              => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name'] ?? ''), '-')),
                'description'       => trim($_POST['description'] ?? ''),
                'price_monthly'     => (float)($_POST['price_monthly'] ?? 0),
                'price_quarterly'   => (float)($_POST['price_quarterly'] ?? 0),
                'price_yearly'      => (float)($_POST['price_yearly'] ?? 0),
                'price_lifetime'    => (float)($_POST['price_lifetime'] ?? 0),
                'trial_days'        => (int)($_POST['trial_days'] ?? 14),
                'storage_limit_mb'  => (int)($_POST['storage_limit_mb'] ?? 100),
                'event_limit'       => (int)($_POST['event_limit'] ?? 5),
                'user_limit'        => (int)($_POST['user_limit'] ?? 1),
                'qr_code_limit'     => (int)($_POST['qr_code_limit'] ?? 10),
                'monthly_scan_limit'=> (int)($_POST['monthly_scan_limit'] ?? 1000),
                'max_file_size_mb'  => (int)($_POST['max_file_size_mb'] ?? 10),
                'api_access'        => isset($_POST['api_access']) ? 1 : 0,
                'white_label'       => isset($_POST['white_label']) ? 1 : 0,
                'analytics'         => isset($_POST['analytics']) ? 1 : 0,
                'custom_domain'     => isset($_POST['custom_domain']) ? 1 : 0,
                'is_default'        => isset($_POST['is_default']) ? 1 : 0,
                'is_active'         => 1,
            ];
            if (!$data['name']) { $error = 'Plan name is required'; }
            else {
                if ($planId) { Plan::update($planId, $data); }
                else { Plan::create($data); }
                App::redirect('/superadmin/plans', $planId ? 'Plan updated' : 'Plan created');
            }
        }
        App::render('superadmin/plan-form', [
            'activePage' => 'plans',
            'pageTitle'  => $planId ? 'Edit Plan' : 'New Plan',
            'plan'       => $plan,
            'error'      => $error,
        ], 'layouts/admin');
    }

    // Delete plan
    if (preg_match('#^/superadmin/plans/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
        App::verifyCsrf();
        Plan::delete((int)$m[1]);
        App::redirect('/superadmin/plans', 'Plan deleted');
    }

    // Settings
    if ($uri === '/superadmin/settings') {
        if ($method === 'POST') {
            App::verifyCsrf();
            $keys = ['platform_name','platform_url','platform_email','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_name','smtp_encryption','commission_type','commission_value','default_currency','registration_enabled','referral_enabled','maintenance_mode'];
            foreach ($keys as $key) {
                $val = $_POST[$key] ?? '0';
                DB::query("INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value", [$key, $val]);
            }
            App::redirect('/superadmin/settings', 'Settings saved');
        }
        $settings = [];
        foreach (DB::fetchAll("SELECT * FROM platform_settings") as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        App::render('superadmin/settings', [
            'activePage' => 'settings',
            'pageTitle'  => 'Platform Settings',
            'settings'   => $settings,
        ], 'layouts/admin');
    }

    // Catch-all for unimplemented super admin pages
    if (preg_match('#^/superadmin/(providers|revenue|coupons|support|activity)$#', $uri, $m)) {
        App::render('superadmin/dashboard', [
            'activePage'      => $m[1],
            'pageTitle'       => ucfirst($m[1]),
            'tenantStats'     => Tenant::stats(),
            'eventStats'      => Event::platformStats(),
            'revenue'         => 0,
            'recentTenants'   => [],
            'storageUsed'     => '0 MB',
            'maintenanceMode' => false,
        ], 'layouts/admin');
    }

    App::abort(404);
}

// ════════════════════════════════════════════════════════════
// TENANT DASHBOARD ROUTES
// ════════════════════════════════════════════════════════════
if (str_starts_with($uri, '/dashboard')) {
    requireTenant();
    $tenantId = App::tenantId();

    // Dashboard
    if ($uri === '/dashboard') {
        $events = Event::byTenant($tenantId);
        $eventCount = count($events);
        $publishedCount = count(array_filter($events, fn($e) => $e['status'] === 'published'));
        $totalScans = array_sum(array_column($events, 'total_scans'));
        $sub = Subscription::active($tenantId);
        $storageBytes = App::tenant()['storage_used'] ?? 0;

        App::render('tenant/dashboard', [
            'activePage'           => 'dashboard',
            'pageTitle'            => 'Dashboard',
            'eventCount'           => $eventCount,
            'publishedCount'       => $publishedCount,
            'totalScans'           => $totalScans,
            'recentEvents'         => array_slice($events, 0, 5),
            'planName'             => $sub['name'] ?? 'None',
            'subStatus'            => $sub['status'] ?? 'none',
            'trialEnds'            => !empty($sub['trial_ends_at']) ? date('M j', strtotime($sub['trial_ends_at'])) : '',
            'storageUsedFormatted' => round($storageBytes / 1048576, 1) . ' MB',
            'storageLimitFormatted'=> ($sub['storage_limit_mb'] ?? 0) < 0 ? 'Unlimited' : ($sub['storage_limit_mb'] ?? 0) . ' MB',
        ]);
    }

    // Events list
    if ($uri === '/dashboard/events') {
        $search = trim($_GET['q'] ?? '');
        $filterType = (int)($_GET['type'] ?? 0);
        $events = Event::byTenant($tenantId, $search, '', $filterType);
        App::render('tenant/events/list', [
            'activePage'  => 'events',
            'pageTitle'   => 'Events',
            'events'      => $events,
            'eventTypes'  => EventType::all($tenantId),
            'search'      => $search,
            'filterType'  => $filterType,
        ]);
    }

    // Create event
    if ($uri === '/dashboard/events/create') {
        if (!App::checkLimit('events')) {
            App::redirect('/dashboard/events', 'Event limit reached. Upgrade your plan.', 'warning');
        }
        $error = null;
        if ($method === 'POST') {
            App::verifyCsrf();
            $name = trim($_POST['name'] ?? '');
            $typeId = (int)($_POST['event_type_id'] ?? 0);
            if (!$name || !$typeId) { $error = 'Event name and type are required'; }
            else {
                $slug = Event::generateSlug($tenantId, $name);
                $eventData = [
                    'tenant_id'        => $tenantId,
                    'event_type_id'    => $typeId,
                    'slug'             => $slug,
                    'name'             => $name,
                    'description'      => trim($_POST['description'] ?? '') ?: null,
                    'organizer'        => trim($_POST['organizer'] ?? '') ?: null,
                    'contact_person'   => trim($_POST['contact_person'] ?? '') ?: null,
                    'phone'            => trim($_POST['phone'] ?? '') ?: null,
                    'whatsapp'         => trim($_POST['whatsapp'] ?? '') ?: null,
                    'email'            => trim($_POST['email'] ?? '') ?: null,
                    'website'          => trim($_POST['website'] ?? '') ?: null,
                    'venue'            => trim($_POST['venue'] ?? '') ?: null,
                    'digital_address'  => trim($_POST['digital_address'] ?? '') ?: null,
                    'google_maps_url'  => trim($_POST['google_maps_url'] ?? '') ?: null,
                    'city'             => trim($_POST['city'] ?? '') ?: null,
                    'region'           => trim($_POST['region'] ?? '') ?: null,
                    'start_date'       => $_POST['start_date'] ?: null,
                    'end_date'         => $_POST['end_date'] ?: null,
                    'start_time'       => $_POST['start_time'] ?: null,
                    'end_time'         => $_POST['end_time'] ?: null,
                    'dynamic_fields'   => !empty($_POST['dynamic']) ? json_encode($_POST['dynamic']) : null,
                    'payment_required' => isset($_POST['payment_required']) ? 1 : 0,
                    'payment_type'     => $_POST['payment_type'] ?? 'free',
                    'payment_amount'   => $_POST['payment_amount'] ?: null,
                    'qr_destination'   => $_POST['qr_destination'] ?? 'event_page',
                    'qr_custom_url'    => trim($_POST['qr_custom_url'] ?? '') ?: null,
                    'status'           => $_POST['status'] ?? 'draft',
                ];

                $eventId = Event::create($eventData);

                // Handle file uploads
                foreach (['cover_image' => 'cover', 'banner_image' => 'banner', 'pdf' => 'pdf'] as $field => $type) {
                    if (!empty($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                        $mediaId = Media::saveUpload($_FILES[$field], $tenantId, $eventId, $type);
                        if ($field === 'cover_image') {
                            $media = Media::find($mediaId);
                            Event::update($eventId, ['cover_image' => $media['file_path']]);
                        } elseif ($field === 'banner_image') {
                            $media = Media::find($mediaId);
                            Event::update($eventId, ['banner_image' => $media['file_path']]);
                        }
                    }
                }
                if (!empty($_FILES['gallery'])) {
                    foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
                        if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                            Media::saveUpload([
                                'tmp_name' => $tmp,
                                'name'     => $_FILES['gallery']['name'][$i],
                                'size'     => $_FILES['gallery']['size'][$i],
                                'error'    => UPLOAD_ERR_OK,
                            ], $tenantId, $eventId, 'gallery');
                        }
                    }
                }

                // Generate QR
                QrService::generate($tenantId, $eventId, $slug, $name);
                Tenant::updateStorage($tenantId);
                App::log('CREATE_EVENT', 'event', $eventId);
                App::redirect("/dashboard/events/{$eventId}", 'Event created');
            }
        }
        App::render('tenant/events/form', [
            'activePage'  => 'events',
            'pageTitle'   => 'Create Event',
            'event'       => null,
            'eventTypes'  => EventType::all($tenantId),
            'existingMedia' => [],
            'error'       => $error,
        ]);
    }

    // View event
    if (preg_match('#^/dashboard/events/(\d+)$#', $uri, $m) && $method === 'GET') {
        $event = Event::find((int)$m[1]);
        if (!$event || $event['tenant_id'] != $tenantId) App::abort(404);
        $qrCode = Event::getQrCode($event['id']);
        $media = Event::getMedia($event['id']);
        $todayScans = DB::count('qr_scans', 'event_id = ? AND DATE(scanned_at) = CURRENT_DATE', [$event['id']]);
        $weekScans = DB::count('qr_scans', 'event_id = ? AND scanned_at >= NOW() - INTERVAL \'7 days\'', [$event['id']]);

        App::render('tenant/events/view', [
            'activePage'  => 'events',
            'pageTitle'   => $event['name'],
            'event'       => $event,
            'qrCode'      => $qrCode,
            'media'        => $media,
            'todayScans'  => $todayScans,
            'weekScans'   => $weekScans,
        ]);
    }

    // Edit event
    if (preg_match('#^/dashboard/events/(\d+)/edit$#', $uri, $m)) {
        $eventId = (int)$m[1];
        $event = Event::find($eventId);
        if (!$event || $event['tenant_id'] != $tenantId) App::abort(404);
        $error = null;

        if ($method === 'POST') {
            App::verifyCsrf();
            $updateData = [
                'event_type_id'    => (int)($_POST['event_type_id'] ?? $event['event_type_id']),
                'name'             => trim($_POST['name'] ?? ''),
                'description'      => trim($_POST['description'] ?? '') ?: null,
                'organizer'        => trim($_POST['organizer'] ?? '') ?: null,
                'contact_person'   => trim($_POST['contact_person'] ?? '') ?: null,
                'phone'            => trim($_POST['phone'] ?? '') ?: null,
                'whatsapp'         => trim($_POST['whatsapp'] ?? '') ?: null,
                'email'            => trim($_POST['email'] ?? '') ?: null,
                'website'          => trim($_POST['website'] ?? '') ?: null,
                'venue'            => trim($_POST['venue'] ?? '') ?: null,
                'digital_address'  => trim($_POST['digital_address'] ?? '') ?: null,
                'google_maps_url'  => trim($_POST['google_maps_url'] ?? '') ?: null,
                'city'             => trim($_POST['city'] ?? '') ?: null,
                'region'           => trim($_POST['region'] ?? '') ?: null,
                'start_date'       => $_POST['start_date'] ?: null,
                'end_date'         => $_POST['end_date'] ?: null,
                'start_time'       => $_POST['start_time'] ?: null,
                'end_time'         => $_POST['end_time'] ?: null,
                'dynamic_fields'   => !empty($_POST['dynamic']) ? json_encode($_POST['dynamic']) : $event['dynamic_fields'],
                'payment_required' => isset($_POST['payment_required']) ? 1 : 0,
                'payment_type'     => $_POST['payment_type'] ?? 'free',
                'payment_amount'   => $_POST['payment_amount'] ?: null,
                'qr_destination'   => $_POST['qr_destination'] ?? 'event_page',
                'qr_custom_url'    => trim($_POST['qr_custom_url'] ?? '') ?: null,
                'status'           => $_POST['status'] ?? $event['status'],
            ];

            Event::update($eventId, $updateData);

            foreach (['cover_image' => 'cover', 'banner_image' => 'banner', 'pdf' => 'pdf'] as $field => $type) {
                if (!empty($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $mediaId = Media::saveUpload($_FILES[$field], $tenantId, $eventId, $type);
                    if (in_array($field, ['cover_image', 'banner_image'])) {
                        $media = Media::find($mediaId);
                        Event::update($eventId, [$field => $media['file_path']]);
                    }
                }
            }
            if (!empty($_FILES['gallery'])) {
                foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
                    if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                        Media::saveUpload([
                            'tmp_name' => $tmp,
                            'name'     => $_FILES['gallery']['name'][$i],
                            'size'     => $_FILES['gallery']['size'][$i],
                            'error'    => UPLOAD_ERR_OK,
                        ], $tenantId, $eventId, 'gallery');
                    }
                }
            }

            Tenant::updateStorage($tenantId);
            App::log('UPDATE_EVENT', 'event', $eventId);
            App::redirect("/dashboard/events/{$eventId}", 'Event updated');
        }

        App::render('tenant/events/form', [
            'activePage'    => 'events',
            'pageTitle'     => 'Edit: ' . $event['name'],
            'event'         => $event,
            'eventTypes'    => EventType::all($tenantId),
            'existingMedia' => Event::getMedia($eventId),
            'error'         => $error,
        ]);
    }

    // Publish event
    if (preg_match('#^/dashboard/events/(\d+)/publish$#', $uri, $m) && $method === 'POST') {
        App::verifyCsrf();
        $event = Event::find((int)$m[1]);
        if ($event && $event['tenant_id'] == $tenantId) {
            Event::update($event['id'], ['status' => 'published']);
        }
        App::redirect("/dashboard/events/{$m[1]}", 'Event published');
    }

    // Delete event
    if (preg_match('#^/dashboard/events/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
        App::verifyCsrf();
        $event = Event::find((int)$m[1]);
        if ($event && $event['tenant_id'] == $tenantId) {
            $mediaList = Event::getMedia($event['id']);
            foreach ($mediaList as $med) { Media::delete($med['id']); }
            $qr = Event::getQrCode($event['id']);
            if ($qr) { QrCode::delete($qr['id']); }
            Event::delete($event['id']);
            Tenant::updateStorage($tenantId);
        }
        App::redirect('/dashboard/events', 'Event deleted');
    }

    // Delete media
    if (preg_match('#^/dashboard/media/(\d+)/delete$#', $uri, $m)) {
        $media = Media::find((int)$m[1]);
        if ($media && $media['tenant_id'] == $tenantId) {
            $eventId = $media['event_id'];
            Media::delete($media['id']);
            Tenant::updateStorage($tenantId);
            if ($eventId) App::redirect("/dashboard/events/{$eventId}/edit", 'File deleted');
        }
        App::redirect('/dashboard/events');
    }

    // Analytics
    if ($uri === '/dashboard/analytics') {
        $totalScans = DB::count('qr_scans', 'tenant_id = ?', [$tenantId]);
        $todayScans = DB::count('qr_scans', 'tenant_id = ? AND DATE(scanned_at) = CURRENT_DATE', [$tenantId]);
        $monthScans = DB::count('qr_scans', 'tenant_id = ? AND scanned_at >= DATE_TRUNC(\'month\', NOW())', [$tenantId]);
        $eventScans = DB::fetchAll(
            "SELECT e.name, et.name as type_name, e.total_scans, MAX(qs.scanned_at) as last_scan
             FROM events e
             JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN qr_scans qs ON qs.event_id = e.id
             WHERE e.tenant_id = ?
             GROUP BY e.id ORDER BY e.total_scans DESC",
            [$tenantId]
        );
        $topEvent = $eventScans[0]['name'] ?? 'N/A';
        $deviceStats = DB::fetchAll("SELECT device, COUNT(*) as count FROM qr_scans WHERE tenant_id = ? GROUP BY device ORDER BY count DESC", [$tenantId]);
        $browserStats = DB::fetchAll("SELECT browser, COUNT(*) as count FROM qr_scans WHERE tenant_id = ? GROUP BY browser ORDER BY count DESC LIMIT 5", [$tenantId]);

        App::render('tenant/analytics', [
            'activePage'   => 'analytics',
            'pageTitle'    => 'Analytics',
            'totalScans'   => $totalScans,
            'todayScans'   => $todayScans,
            'monthScans'   => $monthScans,
            'eventScans'   => $eventScans,
            'topEvent'     => $topEvent,
            'deviceStats'  => $deviceStats,
            'browserStats' => $browserStats,
        ]);
    }

    // Branding
    if ($uri === '/dashboard/branding') {
        if ($method === 'POST') {
            App::verifyCsrf();
            $data = [
                'business_name'        => trim($_POST['business_name'] ?? ''),
                'contact_person'       => trim($_POST['contact_person'] ?? ''),
                'brand_color_primary'  => $_POST['brand_color_primary'] ?? '#212529',
                'brand_color_secondary'=> $_POST['brand_color_secondary'] ?? '#6c757d',
                'footer_text'          => trim($_POST['footer_text'] ?? ''),
            ];
            foreach (['logo', 'favicon'] as $field) {
                if (!empty($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $mediaId = Media::saveUpload($_FILES[$field], $tenantId, null, $field);
                    $media = Media::find($mediaId);
                    $data[$field] = $media['file_path'];
                }
            }
            Tenant::update($tenantId, $data);
            if (!App::tenant()['setup_completed']) {
                Tenant::update($tenantId, ['setup_completed' => 1]);
            }
            App::redirect('/dashboard/branding', 'Branding updated');
        }
        App::render('tenant/branding', ['activePage' => 'branding', 'pageTitle' => 'Branding']);
    }

    // Settings
    if ($uri === '/dashboard/settings') {
        App::render('tenant/settings', ['activePage' => 'settings', 'pageTitle' => 'Settings']);
    }

    // Settings: update profile
    if ($uri === '/dashboard/settings/profile' && $method === 'POST') {
        App::verifyCsrf();
        User::update(App::user()['id'], [
            'name'  => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ]);
        App::redirect('/dashboard/settings', 'Profile updated');
    }

    // Settings: change password
    if ($uri === '/dashboard/settings/password' && $method === 'POST') {
        App::verifyCsrf();
        $user = App::user();
        if (!password_verify($_POST['current_password'] ?? '', $user['password_hash'])) {
            App::redirect('/dashboard/settings', 'Current password is incorrect', 'danger');
        }
        if (strlen($_POST['new_password'] ?? '') < 8) {
            App::redirect('/dashboard/settings', 'New password must be at least 8 characters', 'danger');
        }
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            App::redirect('/dashboard/settings', 'Passwords do not match', 'danger');
        }
        User::update($user['id'], ['password' => $_POST['new_password']]);
        App::redirect('/dashboard/settings', 'Password updated');
    }

    // Billing placeholder
    if ($uri === '/dashboard/billing') {
        App::render('tenant/dashboard', [
            'activePage'           => 'billing',
            'pageTitle'            => 'Billing',
            'eventCount'           => 0,
            'publishedCount'       => 0,
            'totalScans'           => 0,
            'recentEvents'         => [],
            'planName'             => 'Free',
            'subStatus'            => 'active',
            'trialEnds'            => '',
            'storageUsedFormatted' => '0 MB',
            'storageLimitFormatted'=> '50 MB',
        ]);
    }

    // Users placeholder
    if ($uri === '/dashboard/users') {
        App::render('tenant/settings', ['activePage' => 'users', 'pageTitle' => 'Team Users']);
    }

    // Support placeholder
    if ($uri === '/dashboard/support') {
        App::render('tenant/settings', ['activePage' => 'settings', 'pageTitle' => 'Support']);
    }

    App::abort(404);
}

// ─── Serve QR images from storage ─────────────────────────
if (preg_match('#^/storage/qrcodes/(.+\.png)$#', $uri, $m)) {
    QrService::serveQrImage($m[1], isset($_GET['download']), $m[1]);
}

// ─── Serve uploaded files (with basic security) ───────────
if (preg_match('#^/storage/uploads/(.+)$#', $uri, $m)) {
    $path = BASE_DIR . '/storage/uploads/' . $m[1];
    if (!file_exists($path)) App::abort(404);
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// ─── Help center placeholder ──────────────────────────────
if ($uri === '/help') {
    echo '<!DOCTYPE html><html><head><title>Help Center</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><div class="container py-5 text-center"><h2>Help Center</h2><p class="text-muted">Coming soon</p><a href="/" class="btn btn-dark">Back</a></div></body></html>';
    exit;
}

App::abort(404);
