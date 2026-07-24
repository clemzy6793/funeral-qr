<?php $isEdit = $brochure !== null; ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <h2 class="fw-bold mb-4">
            <i class="bi bi-<?= $isEdit ? 'pencil' : 'cloud-arrow-up' ?>"></i>
            <?= $isEdit ? 'Edit' : 'Upload' ?> Brochure
        </h2>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" <?= $isEdit ? '' : 'enctype="multipart/form-data"' ?>>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deceased Name <span class="text-danger">*</span></label>
                        <input type="text" name="deceased_name" class="form-control" required
                               placeholder="e.g. John Kwame Awuku"
                               value="<?= htmlspecialchars($brochure['deceased_name'] ?? $_POST['deceased_name'] ?? '') ?>">
                        <?php if (!$isEdit): ?>
                        <div class="form-text">Used to generate the brochure URL slug</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Funeral Location <span class="text-danger">*</span></label>
                        <input type="text" name="funeral_location" class="form-control" required
                               placeholder="e.g. Holy Trinity Cathedral, Accra"
                               value="<?= htmlspecialchars($brochure['funeral_location'] ?? $_POST['funeral_location'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Digital Address <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="digital_address" class="form-control"
                               placeholder="e.g. GA-123-4567"
                               value="<?= htmlspecialchars($brochure['digital_address'] ?? $_POST['digital_address'] ?? '') ?>">
                        <div class="form-text">Ghana Post GPS address for the funeral location</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brochure Title <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="title" class="form-control"
                               placeholder="e.g. Celebration of Life"
                               value="<?= htmlspecialchars($brochure['title'] ?? $_POST['title'] ?? '') ?>">
                    </div>

                    <?php if (!$isEdit): ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">PDF Brochure <span class="text-danger">*</span></label>
                        <input type="file" name="pdf" accept=".pdf" class="form-control" required>
                        <div class="form-text">PDF only, max 50 MB</div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light border small mb-4">
                        <i class="bi bi-info-circle"></i>
                        To replace the PDF, use <strong>Replace PDF</strong> from the dashboard.
                        The QR code URL will stay the same.
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Save Changes' : 'Upload' ?>
                        </button>
                        <a href="/admin" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card mt-3 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-2">QR Code URL</h6>
                <code class="small"><?= APP_URL ?>/brochure/<?= htmlspecialchars($brochure['slug']) ?></code>
                <p class="text-muted small mt-2 mb-0">
                    Created <?= date('M j, Y g:i A', strtotime($brochure['created_at'])) ?>
                    &middot; Updated <?= date('M j, Y g:i A', strtotime($brochure['updated_at'])) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
