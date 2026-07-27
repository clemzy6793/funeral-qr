<?php
$isFuneral = ($event['type_slug'] ?? '') === 'funeral';
$dynamic = json_decode($event['dynamic_fields'] ?? '{}', true) ?: [];
$hasCountdown = !empty($event['start_date']) && strtotime($event['start_date']) > time();
?>

<?php if (!empty($event['banner_image'])): ?>
<div style="height:280px;background:url('/<?= htmlspecialchars($event['banner_image']) ?>') center/cover no-repeat;position:relative">
    <div style="position:absolute;inset:0;background:linear-gradient(transparent 50%,rgba(0,0,0,.7))"></div>
</div>
<?php endif; ?>

<div class="container py-4" style="max-width:800px">
    <!-- Event Header -->
    <div class="text-center mb-4">
        <?php if ($isFuneral): ?>
        <div style="font-size:2.5rem;color:#c9a96e">&#10013;</div>
        <p class="text-muted text-uppercase small" style="letter-spacing:.05em">In Loving Memory of</p>
        <?php endif; ?>

        <h1 class="fw-bold" style="font-size:2rem"><?= htmlspecialchars($event['name']) ?></h1>

        <?php if (!empty($event['description'])): ?>
        <p class="text-muted mt-2"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        <?php endif; ?>

        <div style="width:60px;height:2px;background:<?= htmlspecialchars($event['brand_color_primary'] ?? '#c9a96e') ?>;margin:1.5rem auto"></div>
    </div>

    <!-- Countdown -->
    <?php if ($hasCountdown): ?>
    <div class="text-center mb-4" id="countdown">
        <div class="d-flex justify-content-center gap-3" id="countdownDisplay"></div>
    </div>
    <script>
    (function(){
        var target = new Date('<?= $event['start_date'] ?>T<?= $event['start_time'] ?? '00:00' ?>').getTime();
        function update(){
            var now = Date.now(), diff = target - now;
            if(diff <= 0){document.getElementById('countdown').innerHTML='<span class="badge bg-success fs-6">Event has started!</span>';return}
            var d=Math.floor(diff/86400000),h=Math.floor(diff%86400000/3600000),m=Math.floor(diff%3600000/60000),s=Math.floor(diff%60000/1000);
            document.getElementById('countdownDisplay').innerHTML=
                '<div class="text-center"><h3 class="fw-bold mb-0">'+d+'</h3><small class="text-muted">Days</small></div>'+
                '<div class="text-center"><h3 class="fw-bold mb-0">'+h+'</h3><small class="text-muted">Hours</small></div>'+
                '<div class="text-center"><h3 class="fw-bold mb-0">'+m+'</h3><small class="text-muted">Mins</small></div>'+
                '<div class="text-center"><h3 class="fw-bold mb-0">'+s+'</h3><small class="text-muted">Secs</small></div>';
        }
        update();setInterval(update,1000);
    })();
    </script>
    <?php endif; ?>

    <!-- Event Details -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <?php if ($event['venue']): ?>
            <div class="d-flex align-items-start gap-2 mb-3">
                <i class="bi bi-geo-alt-fill fs-5 text-muted"></i>
                <div>
                    <strong>Venue</strong><br>
                    <span class="text-muted"><?= htmlspecialchars($event['venue']) ?></span>
                    <?php if ($event['digital_address']): ?>
                    <br><a href="https://ghanapostgps.com/map#<?= urlencode($event['digital_address']) ?>" target="_blank" class="small"><?= htmlspecialchars($event['digital_address']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($event['start_date']): ?>
            <div class="d-flex align-items-start gap-2 mb-3">
                <i class="bi bi-calendar3 fs-5 text-muted"></i>
                <div>
                    <strong>Date & Time</strong><br>
                    <span class="text-muted">
                        <?= date('l, F j, Y', strtotime($event['start_date'])) ?>
                        <?= $event['start_time'] ? ' at ' . date('g:i A', strtotime($event['start_time'])) : '' ?>
                        <?= $event['end_date'] && $event['end_date'] !== $event['start_date'] ? ' — ' . date('M j, Y', strtotime($event['end_date'])) : '' ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($event['contact_person'] || $event['phone']): ?>
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-person fs-5 text-muted"></i>
                <div>
                    <strong>Contact</strong><br>
                    <span class="text-muted">
                        <?= htmlspecialchars($event['contact_person'] ?? '') ?>
                        <?= $event['phone'] ? ' — <a href="tel:' . htmlspecialchars($event['phone']) . '">' . htmlspecialchars($event['phone']) . '</a>' : '' ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dynamic Fields -->
    <?php if ($dynamic): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <?php foreach ($dynamic as $key => $val): if (!$val) continue; ?>
            <div class="mb-2">
                <strong class="small text-uppercase" style="letter-spacing:.03em"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></strong>
                <div class="text-muted"><?= nl2br(htmlspecialchars($val)) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Google Map -->
    <?php if ($event['google_maps_url']): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-0">
            <iframe src="<?= htmlspecialchars($event['google_maps_url']) ?>" width="100%" height="250" style="border:0;border-radius:.5rem" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gallery -->
    <?php if (!empty($gallery)): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-bold">Gallery</div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($gallery as $img): ?>
                <div class="col-4 col-md-3">
                    <a href="/<?= htmlspecialchars($img['file_path']) ?>" target="_blank">
                        <img src="/<?= htmlspecialchars($img['file_path']) ?>" class="img-fluid rounded" alt="" style="aspect-ratio:1;object-fit:cover">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Download / Action Buttons -->
    <div class="text-center mt-4">
        <?php if (!empty($pdf)): ?>
        <a href="/<?= htmlspecialchars($pdf['file_path']) ?>" class="btn btn-dark btn-lg" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> View Brochure
        </a>
        <?php endif; ?>

        <!-- Share Buttons -->
        <div class="mt-3">
            <span class="small text-muted d-block mb-2">Share this event</span>
            <?php $shareUrl = urlencode(APP_URL . '/e/' . $event['tenant_slug'] . '/' . $event['slug']); $shareText = urlencode($event['name']); ?>
            <a href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-whatsapp"></i></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-facebook"></i></a>
            <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-twitter-x"></i></a>
            <button onclick="navigator.clipboard.writeText(decodeURIComponent('<?= $shareUrl ?>'))" class="btn btn-sm btn-outline-secondary"><i class="bi bi-link-45deg"></i></button>
        </div>
    </div>

    <!-- Branding -->
    <?php if (!empty($event['business_name']) || !empty($event['logo'])): ?>
    <div class="text-center mt-4 pt-3 border-top">
        <?php if (!empty($event['logo'])): ?>
        <img src="/storage/uploads/<?= htmlspecialchars($event['logo']) ?>" alt="" style="height:30px" class="mb-1">
        <?php endif; ?>
        <p class="small text-muted mb-0">Powered by <?= htmlspecialchars($event['business_name'] ?? $event['tenant_name'] ?? '') ?></p>
    </div>
    <?php endif; ?>
</div>
