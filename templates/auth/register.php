<div class="auth-logo">
    <i class="bi bi-qr-code"></i>
    <h4><?= htmlspecialchars($appName) ?></h4>
    <p class="text-muted small">Create your account</p>
</div>

<?php if ($error ?? null): ?>
<div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <?php if (!empty($_GET['ref'])): ?>
    <input type="hidden" name="referral_code" value="<?= htmlspecialchars($_GET['ref']) ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label small fw-semibold">Account Type</label>
        <select name="account_type" class="form-select" required>
            <?php foreach ([
                'individual' => 'Individual',
                'business' => 'Business',
                'church' => 'Church',
                'funeral_home' => 'Funeral Home',
                'wedding_planner' => 'Wedding Planner',
                'event_planner' => 'Event Planner',
                'school' => 'School',
                'university' => 'University',
                'ngo' => 'NGO',
                'company' => 'Company',
                'other' => 'Other',
            ] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($_POST['account_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Business / Organization Name <span class="text-danger">*</span></label>
        <input type="text" name="business_name" class="form-control" required value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Contact Person <span class="text-danger">*</span></label>
        <input type="text" name="contact_person" class="form-control" required value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>">
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Phone <span class="text-danger">*</span></label>
            <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Country</label>
        <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($_POST['country'] ?? 'Ghana') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="password" name="password" class="form-control" required minlength="8">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
        </div>
        <div class="form-text">At least 8 characters</div>
    </div>
    <div class="mb-4">
        <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" name="password_confirm" class="form-control" required minlength="8">
    </div>

    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-person-plus"></i> Create Account</button>
</form>

<div class="text-center mt-3">
    <span class="small text-muted">Already have an account?</span>
    <a href="/login" class="small fw-semibold">Sign In</a>
</div>
