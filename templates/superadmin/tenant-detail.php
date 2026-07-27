<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/superadmin/tenants" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> All Tenants</a>
        <h4 class="mb-0 mt-1"><?= htmlspecialchars($tenant['name']) ?></h4>
        <span class="text-muted small"><?= htmlspecialchars($tenant['slug']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <?php $col = ['active'=>'success','pending'=>'warning','suspended'=>'danger','cancelled'=>'secondary'][$tenant['status']] ?? 'secondary'; ?>
        <span class="badge bg-<?= $col ?> fs-6 align-self-center"><?= ucfirst($tenant['status']) ?></span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form method="POST" action="/superadmin/tenants/<?= $tenant['id'] ?>/impersonate" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button class="dropdown-item"><i class="bi bi-person-badge"></i> Login As Owner</button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php if ($tenant['status'] === 'active'): ?>
                <li>
                    <form method="POST" action="/superadmin/tenants/<?= $tenant['id'] ?>/suspend">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button class="dropdown-item text-warning"><i class="bi bi-pause-circle"></i> Suspend</button>
                    </form>
                </li>
                <?php else: ?>
                <li>
                    <form method="POST" action="/superadmin/tenants/<?= $tenant['id'] ?>/activate">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button class="dropdown-item text-success"><i class="bi bi-play-circle"></i> Activate</button>
                    </form>
                </li>
                <?php endif; ?>
                <li>
                    <form method="POST" action="/superadmin/tenants/<?= $tenant['id'] ?>/delete" onsubmit="return confirm('Permanently delete this tenant and ALL their data?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold"><?= count($events) ?></div>
            <div class="text-muted small">Events</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold"><?= $totalScans ?></div>
            <div class="text-muted small">Total Scans</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold"><?= count($users) ?></div>
            <div class="text-muted small">Users</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold"><?= $storageMB ?> MB</div>
            <div class="text-muted small">Storage Used</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Info -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle"></i> Details</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email</span> <span><?= htmlspecialchars($tenant['email']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Phone</span> <span><?= htmlspecialchars($tenant['phone'] ?: '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Contact</span> <span><?= htmlspecialchars($tenant['contact_person'] ?: '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Type</span> <span><?= ucfirst(str_replace('_', ' ', $tenant['account_type'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Country</span> <span><?= htmlspecialchars($tenant['country'] ?: '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Plan</span> <span class="badge bg-secondary"><?= htmlspecialchars($subscription['plan_name'] ?? 'None') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Referral Code</span> <span class="font-monospace"><?= htmlspecialchars($tenant['referral_code'] ?? '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Created</span> <span><?= date('M j, Y g:ia', strtotime($tenant['created_at'])) ?></span></li>
            </ul>
        </div>

        <!-- Users -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-people"></i> Users (<?= count($users) ?>)</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($users as $u): ?>
                <li class="list-group-item">
                    <div class="fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($u['email']) ?> &middot; <?= ucfirst(str_replace('_', ' ', $u['role'])) ?></div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <li class="list-group-item text-muted text-center py-3">No users</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Events -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar-event"></i> Events (<?= count($events) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Name</th><th>Status</th><th>Scans</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $ev): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($ev['name']) ?></div>
                                <a href="/e/<?= htmlspecialchars($tenant['slug']) ?>/<?= htmlspecialchars($ev['slug']) ?>" target="_blank" class="text-muted small text-decoration-none"><i class="bi bi-box-arrow-up-right"></i> <?= htmlspecialchars($ev['slug']) ?></a>
                            </td>
                            <td>
                                <?php $ec = ['published'=>'success','draft'=>'secondary','archived'=>'dark'][$ev['status']] ?? 'secondary'; ?>
                                <span class="badge bg-<?= $ec ?>"><?= ucfirst($ev['status']) ?></span>
                            </td>
                            <td><?= (int)$ev['total_scans'] ?></td>
                            <td class="text-muted small"><?= date('M j, Y', strtotime($ev['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($events)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No events yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
