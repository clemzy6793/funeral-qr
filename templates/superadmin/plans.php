<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted small mb-0"><?= count($plans) ?> plans</p>
    <a href="/superadmin/plans/create" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> New Plan</a>
</div>

<div class="row g-3">
    <?php foreach ($plans as $p): ?>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 <?= $p['is_default'] ? 'border-primary border-2' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($p['name']) ?></h5>
                    <?php if ($p['is_default']): ?><span class="badge bg-primary">Default</span><?php endif; ?>
                    <?php if (!$p['is_active']): ?><span class="badge bg-secondary">Disabled</span><?php endif; ?>
                </div>
                <p class="text-muted small mb-3"><?= htmlspecialchars($p['description'] ?? '') ?></p>
                <div class="mb-3">
                    <span class="fs-4 fw-bold">GHS <?= number_format($p['price_monthly'], 2) ?></span>
                    <span class="text-muted">/mo</span>
                </div>
                <ul class="list-unstyled small">
                    <li class="mb-1"><i class="bi bi-check-circle text-success"></i> <?= $p['event_limit'] < 0 ? 'Unlimited' : $p['event_limit'] ?> events</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-success"></i> <?= $p['storage_limit_mb'] < 0 ? 'Unlimited' : $p['storage_limit_mb'] . 'MB' ?> storage</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-success"></i> <?= $p['qr_code_limit'] < 0 ? 'Unlimited' : $p['qr_code_limit'] ?> QR codes</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-success"></i> <?= $p['user_limit'] < 0 ? 'Unlimited' : $p['user_limit'] ?> users</li>
                    <li class="mb-1"><?= $p['api_access'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?> API access</li>
                    <li class="mb-1"><?= $p['analytics'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?> Analytics</li>
                    <li class="mb-1"><?= $p['white_label'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?> White label</li>
                    <li class="mb-1"><?= $p['custom_domain'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?> Custom domain</li>
                </ul>
                <div class="text-muted small mb-3"><?= $subscriberCounts[$p['id']] ?? 0 ?> subscribers</div>
                <div class="d-flex gap-1">
                    <a href="/superadmin/plans/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-dark flex-fill"><i class="bi bi-pencil"></i> Edit</a>
                    <form method="POST" action="/superadmin/plans/<?= $p['id'] ?>/delete" onsubmit="return confirm('Delete this plan?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
