<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($event['name']) ?></h4>
                        <span class="badge bg-secondary"><i class="bi <?= htmlspecialchars($event['type_icon'] ?? 'bi-calendar-event') ?>"></i> <?= htmlspecialchars($event['type_name']) ?></span>
                        <?php $col = ['published'=>'success','draft'=>'secondary','archived'=>'warning','cancelled'=>'danger'][$event['status']] ?? 'secondary'; ?>
                        <span class="badge bg-<?= $col ?>"><?= ucfirst($event['status']) ?></span>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="/dashboard/events/<?= $event['id'] ?>/edit" class="btn btn-outline-dark"><i class="bi bi-pencil"></i> Edit</a>
                        <?php if ($event['status'] === 'draft'): ?>
                        <form method="POST" action="/dashboard/events/<?= $event['id'] ?>/publish" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-success btn-sm"><i class="bi bi-globe"></i> Publish</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($event['description']): ?>
                <p class="text-muted"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                <?php endif; ?>

                <div class="row g-3 mt-2">
                    <?php if ($event['venue']): ?>
                    <div class="col-md-6"><div class="small"><strong><i class="bi bi-geo-alt"></i> Venue</strong><br><?= htmlspecialchars($event['venue']) ?></div></div>
                    <?php endif; ?>
                    <?php if ($event['start_date']): ?>
                    <div class="col-md-6"><div class="small"><strong><i class="bi bi-calendar3"></i> Date</strong><br><?= date('M j, Y', strtotime($event['start_date'])) ?><?= $event['start_time'] ? ' at ' . date('g:i A', strtotime($event['start_time'])) : '' ?></div></div>
                    <?php endif; ?>
                    <?php if ($event['contact_person']): ?>
                    <div class="col-md-6"><div class="small"><strong><i class="bi bi-person"></i> Contact</strong><br><?= htmlspecialchars($event['contact_person']) ?><?= $event['phone'] ? ' — ' . htmlspecialchars($event['phone']) : '' ?></div></div>
                    <?php endif; ?>
                    <?php if ($event['email']): ?>
                    <div class="col-md-6"><div class="small"><strong><i class="bi bi-envelope"></i> Email</strong><br><?= htmlspecialchars($event['email']) ?></div></div>
                    <?php endif; ?>
                </div>

                <?php
                $dynamic = json_decode($event['dynamic_fields'] ?? '{}', true);
                if ($dynamic):
                ?>
                <hr>
                <h6 class="fw-bold">Details</h6>
                <div class="row g-2">
                    <?php foreach ($dynamic as $key => $val): if (!$val) continue; ?>
                    <div class="col-md-6 small">
                        <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                        <?= nl2br(htmlspecialchars($val)) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Media -->
        <?php if (!empty($media)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Files & Media</div>
            <div class="card-body">
                <?php foreach ($media as $m): ?>
                <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?= str_starts_with($m['mime_type'] ?? '', 'image') ? 'bi-image' : 'bi-file-earmark-pdf' ?> fs-5"></i>
                        <div>
                            <div class="small fw-semibold"><?= htmlspecialchars($m['original_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= strtoupper($m['type']) ?> &middot; <?= number_format($m['file_size'] / 1024) ?> KB</div>
                        </div>
                    </div>
                    <a href="/<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Scan Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Scan Analytics</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4"><h4 class="fw-bold mb-0"><?= number_format($event['total_scans']) ?></h4><div class="small text-muted">Total Scans</div></div>
                    <div class="col-4"><h4 class="fw-bold mb-0"><?= $todayScans ?></h4><div class="small text-muted">Today</div></div>
                    <div class="col-4"><h4 class="fw-bold mb-0"><?= $weekScans ?></h4><div class="small text-muted">This Week</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- QR Code -->
        <?php if ($qrCode): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">QR Code</div>
            <div class="card-body text-center">
                <img src="/storage/qrcodes/<?= htmlspecialchars($qrCode['filename']) ?>" class="img-fluid mb-3" style="max-width:200px" alt="QR Code">
                <p class="small text-muted text-break mb-3"><?= htmlspecialchars($qrCode['url']) ?></p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="/storage/qrcodes/<?= htmlspecialchars($qrCode['filename']) ?>" download class="btn btn-sm btn-outline-dark"><i class="bi bi-download"></i> PNG</a>
                    <button onclick="printQR()" class="btn btn-sm btn-outline-dark"><i class="bi bi-printer"></i> Print</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Public Link -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Public Link</div>
            <div class="card-body">
                <?php $publicUrl = APP_URL . '/e/' . htmlspecialchars($currentTenant['slug']) . '/' . htmlspecialchars($event['slug']); ?>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="pubLink" readonly value="<?= $publicUrl ?>">
                    <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('pubLink').value);this.innerHTML='<i class=\'bi bi-check\'></i>'"><i class="bi bi-clipboard"></i></button>
                </div>
                <a href="<?= $publicUrl ?>" target="_blank" class="btn btn-sm btn-outline-dark w-100 mt-2"><i class="bi bi-box-arrow-up-right"></i> View Public Page</a>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="/dashboard/events/<?= $event['id'] ?>/edit" class="btn btn-outline-dark btn-sm"><i class="bi bi-pencil"></i> Edit Event</a>
                <form method="POST" action="/dashboard/events/<?= $event['id'] ?>/delete" onsubmit="return confirm('Delete this event, QR code, and all files?')">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash"></i> Delete Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function printQR() {
    var img = document.querySelector('.card-body img[alt="QR Code"]').src;
    var name = <?= json_encode($event['name']) ?>;
    var w = window.open('','_blank');
    w.document.write('<html><head><title>QR - '+name+'</title><style>body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;font-family:Arial;margin:0}img{width:300px}h2{margin:1rem 0;font-size:1.2rem}</style></head><body><img src="'+img+'"><h2>'+name+'</h2></body></html>');
    w.document.close();
    w.onload=function(){w.print()};
}
</script>
