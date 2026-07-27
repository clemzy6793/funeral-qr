<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Brand Identity</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <?php if (!empty($currentTenant['logo'])): ?>
                            <div class="mt-2"><img src="/storage/uploads/<?= htmlspecialchars($currentTenant['logo']) ?>" style="height:40px" alt="Logo"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*">
                            <?php if (!empty($currentTenant['favicon'])): ?>
                            <div class="mt-2"><img src="/storage/uploads/<?= htmlspecialchars($currentTenant['favicon']) ?>" style="height:32px" alt="Favicon"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="<?= htmlspecialchars($currentTenant['business_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Details</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($currentTenant['contact_person'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Brand Colors</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Color</label>
                            <div class="input-group">
                                <input type="color" name="brand_color_primary" class="form-control form-control-color" value="<?= htmlspecialchars($currentTenant['brand_color_primary'] ?? '#212529') ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($currentTenant['brand_color_primary'] ?? '#212529') ?>" readonly style="max-width:100px">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="brand_color_secondary" class="form-control form-control-color" value="<?= htmlspecialchars($currentTenant['brand_color_secondary'] ?? '#6c757d') ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($currentTenant['brand_color_secondary'] ?? '#6c757d') ?>" readonly style="max-width:100px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Footer</div>
                <div class="card-body">
                    <textarea name="footer_text" class="form-control" rows="2" placeholder="Custom footer text for your event pages"><?= htmlspecialchars($currentTenant['footer_text'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Save Branding</button>
        </form>
    </div>
</div>
