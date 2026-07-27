<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Profile -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Account Settings</div>
            <div class="card-body">
                <form method="POST" action="/dashboard/settings/profile">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Phone</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"></div>
                    </div>
                    <button type="submit" class="btn btn-brand btn-sm mt-3"><i class="bi bi-check-lg"></i> Save</button>
                </form>
            </div>
        </div>

        <!-- Password -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Change Password</div>
            <div class="card-body">
                <form method="POST" action="/dashboard/settings/password">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label small fw-semibold">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">New Password</label><input type="password" name="new_password" class="form-control" required minlength="8"></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">Confirm</label><input type="password" name="confirm_password" class="form-control" required minlength="8"></div>
                    </div>
                    <button type="submit" class="btn btn-brand btn-sm mt-3"><i class="bi bi-shield-lock"></i> Update Password</button>
                </form>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-header bg-white fw-bold text-danger">Danger Zone</div>
            <div class="card-body">
                <p class="small text-muted mb-2">Permanently delete your account and all data. This cannot be undone.</p>
                <form method="POST" action="/dashboard/settings/delete-account" onsubmit="return confirm('Are you sure? This permanently deletes your account and ALL events, QR codes, and data.')">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
