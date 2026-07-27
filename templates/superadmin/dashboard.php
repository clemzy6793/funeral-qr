<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Tenants</p>
                        <h3 class="fw-bold mb-0"><?= number_format($tenantStats['total']) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3"><i class="bi bi-building fs-4 text-primary"></i></div>
                </div>
                <div class="mt-2 small">
                    <span class="text-success"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> <?= $tenantStats['active'] ?> active</span>
                    <span class="text-warning ms-2"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> <?= $tenantStats['pending'] ?> pending</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Events</p>
                        <h3 class="fw-bold mb-0"><?= number_format($eventStats['total']) ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-3 p-3"><i class="bi bi-calendar-event fs-4 text-success"></i></div>
                </div>
                <div class="mt-2 small">
                    <span class="text-success"><?= $eventStats['published'] ?> published</span>
                    <span class="text-muted ms-2"><?= $eventStats['draft'] ?> drafts</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total QR Scans</p>
                        <h3 class="fw-bold mb-0"><?= number_format($eventStats['total_scans'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-3 p-3"><i class="bi bi-qr-code-scan fs-4 text-info"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Revenue</p>
                        <h3 class="fw-bold mb-0">GHS <?= number_format($revenue, 2) ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3"><i class="bi bi-cash-stack fs-4 text-warning"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Recent Tenants</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tenant</th><th>Plan</th><th>Status</th><th>Created</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTenants as $t): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($t['name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($t['email']) ?></div>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($t['plan_name'] ?? 'None') ?></span></td>
                                <td>
                                    <?php
                                    $statusColors = ['active' => 'success', 'pending' => 'warning', 'suspended' => 'danger', 'cancelled' => 'secondary'];
                                    $col = $statusColors[$t['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $col ?>"><?= ucfirst($t['status']) ?></span>
                                </td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                                <td><a href="/superadmin/tenants/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentTenants)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No tenants yet</td></tr>
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
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/superadmin/tenants" class="btn btn-outline-dark btn-sm"><i class="bi bi-building"></i> Manage Tenants</a>
                    <a href="/superadmin/plans" class="btn btn-outline-dark btn-sm"><i class="bi bi-credit-card"></i> Manage Plans</a>
                    <a href="/superadmin/providers" class="btn btn-outline-dark btn-sm"><i class="bi bi-wallet2"></i> Payment Providers</a>
                    <a href="/superadmin/settings" class="btn btn-outline-dark btn-sm"><i class="bi bi-gear"></i> Platform Settings</a>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-bold">System Health</div>
            <div class="card-body">
                <div class="d-flex justify-content-between small mb-2">
                    <span>Database</span>
                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> OK</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span>Storage</span>
                    <span class="text-muted"><?= $storageUsed ?></span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Maintenance</span>
                    <?php if ($maintenanceMode): ?>
                    <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> ON</span>
                    <?php else: ?>
                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> OFF</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
