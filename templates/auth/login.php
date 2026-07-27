<div class="auth-logo">
    <i class="bi bi-qr-code"></i>
    <h4><?= htmlspecialchars($appName) ?></h4>
    <p class="text-muted small">Sign in to your account</p>
</div>

<?php if ($error ?? null): ?>
<div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <label class="form-label small fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Password</label>
        <div class="input-group">
            <input type="password" name="password" class="form-control" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="/forgot-password" class="small text-muted">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-box-arrow-in-right"></i> Sign In</button>
</form>

<div class="text-center mt-3">
    <span class="small text-muted">Don't have an account?</span>
    <a href="/register" class="small fw-semibold">Register</a>
</div>
