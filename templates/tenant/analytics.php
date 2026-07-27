<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Scans</p>
                <h3 class="fw-bold mb-0"><?= number_format($totalScans) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Today</p>
                <h3 class="fw-bold mb-0"><?= number_format($todayScans) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">This Month</p>
                <h3 class="fw-bold mb-0"><?= number_format($monthScans) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Top Event</p>
                <h6 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($topEvent ?? 'N/A') ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Scans by Event</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Event</th><th>Type</th><th class="text-end">Scans</th><th class="text-end">Last Scan</th></tr></thead>
                        <tbody>
                            <?php foreach ($eventScans as $es): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($es['name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($es['type_name']) ?></td>
                                <td class="text-end"><?= number_format($es['total_scans']) ?></td>
                                <td class="text-end text-muted small"><?= $es['last_scan'] ? date('M j, g:i A', strtotime($es['last_scan'])) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($eventScans)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No scan data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Devices</div>
            <div class="card-body">
                <?php foreach ($deviceStats as $d): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <span><?= htmlspecialchars(ucfirst($d['device'])) ?></span>
                    <span class="fw-semibold"><?= $d['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($deviceStats)): ?>
                <p class="text-muted small text-center mb-0">No data</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Browsers</div>
            <div class="card-body">
                <?php foreach ($browserStats as $b): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <span><?= htmlspecialchars($b['browser']) ?></span>
                    <span class="fw-semibold"><?= $b['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($browserStats)): ?>
                <p class="text-muted small text-center mb-0">No data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
