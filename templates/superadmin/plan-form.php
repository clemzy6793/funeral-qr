<?php $isEdit = !empty($plan); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if ($error ?? null): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($plan['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($plan['description'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3">Pricing</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><label class="form-label small">Monthly (GHS)</label><input type="number" name="price_monthly" class="form-control" step="0.01" value="<?= $plan['price_monthly'] ?? 0 ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Quarterly</label><input type="number" name="price_quarterly" class="form-control" step="0.01" value="<?= $plan['price_quarterly'] ?? 0 ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Yearly</label><input type="number" name="price_yearly" class="form-control" step="0.01" value="<?= $plan['price_yearly'] ?? 0 ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Lifetime</label><input type="number" name="price_lifetime" class="form-control" step="0.01" value="<?= $plan['price_lifetime'] ?? 0 ?>"></div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3">Limits <span class="text-muted fw-normal small">(-1 = unlimited)</span></h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label small">Events</label><input type="number" name="event_limit" class="form-control" value="<?= $plan['event_limit'] ?? 5 ?>"></div>
                        <div class="col-md-4"><label class="form-label small">Storage (MB)</label><input type="number" name="storage_limit_mb" class="form-control" value="<?= $plan['storage_limit_mb'] ?? 100 ?>"></div>
                        <div class="col-md-4"><label class="form-label small">QR Codes</label><input type="number" name="qr_code_limit" class="form-control" value="<?= $plan['qr_code_limit'] ?? 10 ?>"></div>
                        <div class="col-md-4"><label class="form-label small">Users</label><input type="number" name="user_limit" class="form-control" value="<?= $plan['user_limit'] ?? 1 ?>"></div>
                        <div class="col-md-4"><label class="form-label small">Monthly Scans</label><input type="number" name="monthly_scan_limit" class="form-control" value="<?= $plan['monthly_scan_limit'] ?? 1000 ?>"></div>
                        <div class="col-md-4"><label class="form-label small">Max File (MB)</label><input type="number" name="max_file_size_mb" class="form-control" value="<?= $plan['max_file_size_mb'] ?? 10 ?>"></div>
                    </div>
                    <div class="col-md-3"><label class="form-label small">Trial Days</label><input type="number" name="trial_days" class="form-control" value="<?= $plan['trial_days'] ?? 14 ?>"></div>

                    <h6 class="fw-bold mt-4 mb-3">Features</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="api_access" class="form-check-input" value="1" <?= ($plan['api_access'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label">API Access</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="white_label" class="form-check-input" value="1" <?= ($plan['white_label'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label">White Label</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="analytics" class="form-check-input" value="1" <?= ($plan['analytics'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label">Analytics</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="custom_domain" class="form-check-input" value="1" <?= ($plan['custom_domain'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label">Custom Domain</label></div></div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="is_default" class="form-check-input" value="1" <?= ($plan['is_default'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label">Set as default plan for new registrations</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> <?= $isEdit ? 'Save Changes' : 'Create Plan' ?></button>
                        <a href="/superadmin/plans" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
