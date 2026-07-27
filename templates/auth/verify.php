<div class="auth-logo">
    <i class="bi bi-envelope-check"></i>
    <h4>Verify Your Email</h4>
</div>

<?php if ($verified ?? false): ?>
<div class="alert alert-success py-2">
    <i class="bi bi-check-circle-fill"></i> Your email has been verified! You can now sign in.
</div>
<a href="/login" class="btn btn-dark w-100">Sign In</a>
<?php elseif ($error ?? null): ?>
<div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<a href="/login" class="btn btn-outline-dark w-100">Back to Login</a>
<?php else: ?>
<p class="text-muted text-center">We sent a verification link to your email. Please check your inbox and click the link to activate your account.</p>
<p class="text-muted text-center small">Didn't receive it? Check your spam folder or <a href="/resend-verification">resend verification email</a>.</p>
<?php endif; ?>
