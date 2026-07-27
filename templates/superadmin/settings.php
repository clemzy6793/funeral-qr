<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Platform</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold">Platform Name</label><input type="text" name="platform_name" class="form-control" value="<?= htmlspecialchars($settings['platform_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Platform URL</label><input type="url" name="platform_url" class="form-control" value="<?= htmlspecialchars($settings['platform_url'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Platform Email</label><input type="email" name="platform_email" class="form-control" value="<?= htmlspecialchars($settings['platform_email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Default Currency</label><input type="text" name="default_currency" class="form-control" value="<?= htmlspecialchars($settings['default_currency'] ?? 'GHS') ?>" maxlength="3"></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Email (SMTP)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8"><label class="form-label small fw-semibold">SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">Port</label><input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">SMTP User</label><input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">SMTP Password</label><input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">From Name</label><input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Commission</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="none" <?= ($settings['commission_type'] ?? '') === 'none' ? 'selected' : '' ?>>No Commission</option>
                                <option value="fixed" <?= ($settings['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                                <option value="percentage" <?= ($settings['commission_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Commission Value</label><input type="number" name="commission_value" class="form-control" step="0.01" value="<?= htmlspecialchars($settings['commission_value'] ?? '0') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Features</div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="registration_enabled" class="form-check-input" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label">Allow self-registration</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="referral_enabled" class="form-check-input" value="1" <?= ($settings['referral_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label">Enable referral system</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="maintenance_mode" class="form-check-input" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label text-danger">Enable maintenance mode</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Save Settings</button>
        </form>
    </div>
</div>
