<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0"><?= $totalTenants ?> total tenants</p>
    </div>
</div>

<form class="mb-4" method="GET">
    <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search by name, email, slug…" value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="form-select" style="max-width:150px" onchange="this.form.submit()">
            <option value="">All Status</option>
            <?php foreach (['active','pending','suspended','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($search || $filterStatus): ?>
        <a href="/superadmin/tenants" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Tenant</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Events</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($t['name']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($t['email']) ?></div>
                    </td>
                    <td class="small"><?= ucfirst(str_replace('_', ' ', $t['account_type'])) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($t['plan_name'] ?? 'None') ?></span></td>
                    <td><?= $t['event_count'] ?? 0 ?></td>
                    <td>
                        <?php $col = ['active'=>'success','pending'=>'warning','suspended'=>'danger','cancelled'=>'secondary'][$t['status']] ?? 'secondary'; ?>
                        <span class="badge bg-<?= $col ?>"><?= ucfirst($t['status']) ?></span>
                    </td>
                    <td class="text-muted small"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/superadmin/tenants/<?= $t['id'] ?>"><i class="bi bi-eye"></i> View</a></li>
                                <li><a class="dropdown-item" href="/superadmin/tenants/<?= $t['id'] ?>/edit"><i class="bi bi-pencil"></i> Edit</a></li>
                                <li>
                                    <form method="POST" action="/superadmin/tenants/<?= $t['id'] ?>/impersonate" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button class="dropdown-item" type="submit"><i class="bi bi-person-badge"></i> Login As</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($t['status'] === 'active'): ?>
                                <li>
                                    <form method="POST" action="/superadmin/tenants/<?= $t['id'] ?>/suspend">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button class="dropdown-item text-warning" type="submit"><i class="bi bi-pause-circle"></i> Suspend</button>
                                    </form>
                                </li>
                                <?php elseif ($t['status'] !== 'active'): ?>
                                <li>
                                    <form method="POST" action="/superadmin/tenants/<?= $t['id'] ?>/activate">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button class="dropdown-item text-success" type="submit"><i class="bi bi-play-circle"></i> Activate</button>
                                    </form>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <form method="POST" action="/superadmin/tenants/<?= $t['id'] ?>/delete" onsubmit="return confirm('Permanently delete this tenant and ALL their data?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tenants)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No tenants found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
