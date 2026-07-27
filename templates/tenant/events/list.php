<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted small mb-0"><?= count($events) ?> event(s)</p>
    <a href="/dashboard/events/create" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> New Event</a>
</div>

<form class="mb-4" method="GET">
    <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search events…" value="<?= htmlspecialchars($search ?? '') ?>">
        <select name="type" class="form-select" style="max-width:180px" onchange="this.form.submit()">
            <option value="">All Types</option>
            <?php foreach ($eventTypes as $et): ?>
            <option value="<?= $et['id'] ?>" <?= ($filterType ?? '') == $et['id'] ? 'selected' : '' ?>><?= htmlspecialchars($et['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($search) || !empty($filterType)): ?>
        <a href="/dashboard/events" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($events)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-calendar-x display-3"></i>
    <p class="mt-3">No events yet</p>
    <a href="/dashboard/events/create" class="btn btn-brand btn-sm">Create your first event</a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($events as $e): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <?php if ($e['cover_image']): ?>
            <img src="/<?= htmlspecialchars($e['cover_image']) ?>" class="card-img-top" style="height:140px;object-fit:cover" alt="">
            <?php else: ?>
            <div class="card-img-top d-flex align-items-center justify-content-center" style="height:140px;background:#f0f0f0">
                <i class="bi <?= htmlspecialchars($e['type_icon'] ?? 'bi-calendar-event') ?> display-4 text-muted"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($e['name']) ?></h6>
                    <?php $col = ['published'=>'success','draft'=>'secondary','archived'=>'warning','cancelled'=>'danger'][$e['status']] ?? 'secondary'; ?>
                    <span class="badge bg-<?= $col ?>"><?= ucfirst($e['status']) ?></span>
                </div>
                <p class="small text-muted mb-2">
                    <i class="bi <?= htmlspecialchars($e['type_icon'] ?? '') ?>"></i> <?= htmlspecialchars($e['type_name']) ?>
                    <?php if ($e['start_date']): ?>
                    &middot; <?= date('M j, Y', strtotime($e['start_date'])) ?>
                    <?php endif; ?>
                </p>
                <?php if ($e['venue']): ?>
                <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['venue']) ?></p>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="bi bi-qr-code-scan"></i> <?= $e['scan_count'] ?? $e['total_scans'] ?? 0 ?> scans</span>
                    <div class="btn-group btn-group-sm">
                        <a href="/dashboard/events/<?= $e['id'] ?>" class="btn btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                        <a href="/dashboard/events/<?= $e['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
