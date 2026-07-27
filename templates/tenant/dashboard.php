<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Events</p>
                <h3 class="fw-bold mb-0"><?= $eventCount ?></h3>
                <div class="small text-muted"><?= $publishedCount ?> published</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Scans</p>
                <h3 class="fw-bold mb-0"><?= number_format($totalScans) ?></h3>
                <div class="small text-muted">all time</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Storage</p>
                <h3 class="fw-bold mb-0"><?= $storageUsedFormatted ?></h3>
                <div class="small text-muted">of <?= $storageLimitFormatted ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Plan</p>
                <h3 class="fw-bold mb-0"><?= htmlspecialchars($planName) ?></h3>
                <div class="small">
                    <?php if ($subStatus === 'trial'): ?>
                    <span class="text-warning">Trial ends <?= $trialEnds ?></span>
                    <?php elseif ($subStatus === 'active'): ?>
                    <span class="text-success">Active</span>
                    <?php else: ?>
                    <span class="text-danger"><?= ucfirst($subStatus) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!($currentTenant['setup_completed'] ?? false)): ?>
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bi bi-magic fs-4 me-3"></i>
    <div>
        <strong>Complete your setup!</strong> Upload your logo, set brand colors, and create your first event.
        <a href="/dashboard/branding" class="alert-link">Get started</a>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Recent Events</span>
                <a href="/dashboard/events" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Event</th><th>Type</th><th>Status</th><th>Scans</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $e): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($e['name']) ?></td>
                                <td class="small"><i class="bi <?= htmlspecialchars($e['type_icon'] ?? 'bi-calendar-event') ?>"></i> <?= htmlspecialchars($e['type_name']) ?></td>
                                <td>
                                    <?php $col = ['published'=>'success','draft'=>'secondary','archived'=>'warning','cancelled'=>'danger'][$e['status']] ?? 'secondary'; ?>
                                    <span class="badge bg-<?= $col ?>"><?= ucfirst($e['status']) ?></span>
                                </td>
                                <td class="text-muted"><?= $e['scan_count'] ?? $e['total_scans'] ?? 0 ?></td>
                                <td><a href="/dashboard/events/<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentEvents)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">
                                No events yet. <a href="/dashboard/events/create">Create your first event</a>
                            </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Quick Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="/dashboard/events/create" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Create Event</a>
                <a href="/dashboard/branding" class="btn btn-outline-dark btn-sm"><i class="bi bi-palette"></i> Update Branding</a>
                <a href="/dashboard/analytics" class="btn btn-outline-dark btn-sm"><i class="bi bi-bar-chart-line"></i> View Analytics</a>
            </div>
        </div>
        <?php if (!empty($currentTenant['referral_code'])): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-bold">Referral Link</div>
            <div class="card-body">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="refLink" readonly value="<?= APP_URL ?>/register?ref=<?= htmlspecialchars($currentTenant['referral_code']) ?>">
                    <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('refLink').value);this.innerHTML='<i class=\'bi bi-check\'></i>'"><i class="bi bi-clipboard"></i></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
