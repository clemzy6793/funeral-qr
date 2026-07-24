<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

use App\{Auth, Brochure};

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

function render(string $template, array $vars = [], string $layout = 'layout'): void
{
    $vars['csrfToken']  = Auth::csrfToken();
    $vars['isLoggedIn'] = Auth::check();
    extract($vars);
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    ob_start();
    require BASE_DIR . "/templates/{$template}.php";
    $content   = ob_get_clean();
    $pageTitle = $pageTitle ?? APP_NAME;
    require BASE_DIR . "/templates/{$layout}.php";
    exit;
}

function redirect(string $url, string $flash = '', string $type = 'success'): void
{
    if ($flash) $_SESSION['flash'] = ['message' => $flash, 'type' => $type];
    header("Location: {$url}");
    exit;
}

// ── Public: brochure page ──────────────────────────────────
if (preg_match('#^/brochure/([a-z0-9-]+)$#', $uri, $m)) {
    $brochure = Brochure::getBySlug($m[1]);
    if (!$brochure) { http_response_code(404); die('Not found'); }
    render('view', ['brochure' => $brochure, 'pageTitle' => $brochure['deceased_name']], 'layout-public');
}

// ── Public: serve PDF ──────────────────────────────────────
if (preg_match('#^/brochure/([a-z0-9-]+)/pdf$#', $uri, $m)) {
    $brochure = Brochure::getBySlug($m[1]);
    if (!$brochure) { http_response_code(404); die('Not found'); }
    $path = UPLOAD_DIR . '/' . $brochure['pdf_filename'];
    if (!file_exists($path)) { http_response_code(404); die('File not found'); }
    $name = ($brochure['title'] ?: $brochure['deceased_name']) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9 ._-]/', '', $name) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// ── Auth ───────────────────────────────────────────────────
if ($uri === '/login') {
    if (Auth::check()) redirect('/admin');
    $error = null;
    if ($method === 'POST') {
        Auth::verifyCsrf();
        if (Auth::attempt($_POST['username'] ?? '', $_POST['password'] ?? '')) {
            redirect('/admin');
        }
        $error = 'Invalid credentials';
    }
    render('login', ['error' => $error, 'pageTitle' => 'Login']);
}

if ($uri === '/logout') {
    Auth::logout();
    redirect('/login');
}

// ── Root redirect ──────────────────────────────────────────
if ($uri === '/') {
    redirect(Auth::check() ? '/admin' : '/login');
}

// ── Admin routes (all require auth) ────────────────────────
if (!str_starts_with($uri, '/admin')) {
    http_response_code(404);
    die('Not found');
}
Auth::requireLogin();

// Dashboard
if ($uri === '/admin') {
    $search    = trim($_GET['q'] ?? '');
    $brochures = Brochure::getAll($search);
    $total     = Brochure::count();
    render('dashboard', [
        'brochures' => $brochures,
        'search'    => $search,
        'total'     => $total,
        'pageTitle' => 'Dashboard',
    ]);
}

// Upload
if ($uri === '/admin/upload') {
    $error = null;
    if ($method === 'POST') {
        Auth::verifyCsrf();
        try {
            Brochure::create($_POST, $_FILES['pdf'] ?? []);
            redirect('/admin', 'Brochure created successfully');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
    render('form', ['brochure' => null, 'error' => $error, 'pageTitle' => 'Upload Brochure']);
}

// Change password
if ($uri === '/admin/password') {
    $error = null;
    $success = false;
    if ($method === 'POST') {
        Auth::verifyCsrf();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match';
        } elseif (!Auth::changePassword($current, $new)) {
            $error = 'Current password is incorrect';
        } else {
            redirect('/admin', 'Password changed successfully');
        }
    }
    render('password', ['error' => $error, 'pageTitle' => 'Change Password']);
}

// Edit
if (preg_match('#^/admin/edit/(\d+)$#', $uri, $m)) {
    $brochure = Brochure::getById((int) $m[1]);
    if (!$brochure) redirect('/admin', 'Not found', 'danger');
    $error = null;
    if ($method === 'POST') {
        Auth::verifyCsrf();
        try {
            Brochure::update((int) $m[1], $_POST);
            redirect('/admin', 'Brochure updated');
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $brochure = array_merge($brochure, $_POST);
        }
    }
    render('form', ['brochure' => $brochure, 'error' => $error, 'pageTitle' => 'Edit Brochure']);
}

// Replace PDF
if (preg_match('#^/admin/replace/(\d+)$#', $uri, $m) && $method === 'POST') {
    Auth::verifyCsrf();
    try {
        Brochure::replacePdf((int) $m[1], $_FILES['pdf'] ?? []);
        redirect('/admin', 'PDF replaced — QR code URL unchanged');
    } catch (\Exception $e) {
        redirect('/admin', $e->getMessage(), 'danger');
    }
}

// Delete
if (preg_match('#^/admin/delete/(\d+)$#', $uri, $m) && $method === 'POST') {
    Auth::verifyCsrf();
    Brochure::delete((int) $m[1]);
    redirect('/admin', 'Brochure deleted');
}

// Regenerate all QR codes
if ($uri === '/admin/regenerate-qr' && $method === 'POST') {
    Auth::verifyCsrf();
    $count = Brochure::regenerateAllQRs();
    redirect('/admin', "Regenerated {$count} QR code(s) with names");
}

// Serve QR image (inline or download)
if (preg_match('#^/admin/qr/(\d+)$#', $uri, $m)) {
    $brochure = Brochure::getById((int) $m[1]);
    if (!$brochure) { http_response_code(404); die('Not found'); }
    $path = QR_DIR . '/' . $brochure['qr_filename'];
    if (!file_exists($path)) { http_response_code(404); die('QR not found'); }
    header('Content-Type: image/png');
    if (isset($_GET['download'])) {
        header('Content-Disposition: attachment; filename="qr-' . $brochure['slug'] . '.png"');
    }
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

http_response_code(404);
die('Not found');
