<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h2 class="fw-bold mb-4">
            <i class="bi bi-shield-lock"></i> Change Password
        </h2>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-pw" onclick="togglePw(this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary toggle-pw" onclick="togglePw(this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">At least 6 characters</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary toggle-pw" onclick="togglePw(this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg"></i> Update Password
                        </button>
                        <a href="/admin" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(btn) {
    var input = btn.parentElement.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
