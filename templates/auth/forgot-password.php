<div class="auth-logo">
    <i class="bi bi-shield-lock"></i>
    <h4>Reset Password</h4>
    <p class="text-muted small">Enter your email to receive a reset link</p>
</div>

<?php if ($sent ?? false): ?>
<div class="alert alert-success py-2 small">
    <i class="bi bi-check-circle-fill"></i> If an account exists with that email, a reset link has been sent.
</div>
<a href="/login" class="btn btn-outline-dark w-100">Back to Login</a>
<?php else: ?>
<?php if ($error ?? null): ?>
<div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <label class="form-label small fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required autofocus>
    </div>
    <button type="submit" class="btn btn-dark w-100">Send Reset Link</button>
</form>
<div class="text-center mt-3">
    <a href="/login" class="small text-muted">Back to Login</a>
</div>
<?php endif; ?>
