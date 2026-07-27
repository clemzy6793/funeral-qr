<?php $isEdit = !empty($event); ?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <?php if ($error ?? null): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <!-- Event Type & Basic Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">Basic Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Type <span class="text-danger">*</span></label>
                            <select name="event_type_id" class="form-select" required id="eventType" onchange="loadDynamicFields(this.value)">
                                <option value="">Select type…</option>
                                <?php foreach ($eventTypes as $et): ?>
                                <option value="<?= $et['id'] ?>" data-fields='<?= htmlspecialchars($et['default_fields'] ?? '{}') ?>'
                                    <?= ($event['event_type_id'] ?? '') == $et['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($et['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($event['name'] ?? '') ?>" placeholder="e.g. Memorial Service for Nana Kwame">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the event"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Fields (populated by JS) -->
            <div class="card border-0 shadow-sm mb-3" id="dynamicFieldsCard" style="display:none">
                <div class="card-header bg-white fw-bold" id="dynamicFieldsTitle">Type-Specific Details</div>
                <div class="card-body" id="dynamicFieldsContainer"></div>
            </div>

            <!-- Location -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">Location</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold">Venue</label><input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($event['venue'] ?? '') ?>" placeholder="e.g. Holy Trinity Cathedral, Accra"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Digital Address</label><input type="text" name="digital_address" class="form-control" value="<?= htmlspecialchars($event['digital_address'] ?? '') ?>" placeholder="e.g. GA-123-4567"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">City</label><input type="text" name="city" class="form-control" value="<?= htmlspecialchars($event['city'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Region</label><input type="text" name="region" class="form-control" value="<?= htmlspecialchars($event['region'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label small fw-semibold">Google Maps URL</label><input type="url" name="google_maps_url" class="form-control" value="<?= htmlspecialchars($event['google_maps_url'] ?? '') ?>" placeholder="https://maps.google.com/..."></div>
                    </div>
                </div>
            </div>

            <!-- Date & Time -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">Date & Time</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label small fw-semibold">Start Date</label><input type="date" name="start_date" class="form-control" value="<?= $event['start_date'] ?? '' ?>"></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">End Date</label><input type="date" name="end_date" class="form-control" value="<?= $event['end_date'] ?? '' ?>"></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">Start Time</label><input type="time" name="start_time" class="form-control" value="<?= $event['start_time'] ?? '' ?>"></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">End Time</label><input type="time" name="end_time" class="form-control" value="<?= $event['end_time'] ?? '' ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">Contact Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold">Organizer</label><input type="text" name="organizer" class="form-control" value="<?= htmlspecialchars($event['organizer'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Contact Person</label><input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($event['contact_person'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">Phone</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($event['phone'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">WhatsApp</label><input type="tel" name="whatsapp" class="form-control" value="<?= htmlspecialchars($event['whatsapp'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($event['email'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label small fw-semibold">Website</label><input type="url" name="website" class="form-control" value="<?= htmlspecialchars($event['website'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">Media & Files</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <?php if (!empty($event['cover_image'])): ?>
                            <div class="mt-1 small text-muted">Current: <?= basename($event['cover_image']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Banner Image</label>
                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">PDF Brochure / Programme</label>
                            <input type="file" name="pdf" class="form-control" accept=".pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Gallery Images</label>
                            <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>
                    <?php if (!empty($existingMedia)): ?>
                    <div class="mt-3">
                        <label class="form-label small fw-semibold">Existing Files</label>
                        <div class="row g-2">
                            <?php foreach ($existingMedia as $m): ?>
                            <div class="col-auto">
                                <div class="border rounded p-2 small d-flex align-items-center gap-2">
                                    <i class="bi <?= str_starts_with($m['mime_type'] ?? '', 'image') ? 'bi-image' : 'bi-file-earmark-pdf' ?>"></i>
                                    <?= htmlspecialchars($m['original_name']) ?>
                                    <a href="/dashboard/media/<?= $m['id'] ?>/delete" class="text-danger" onclick="return confirm('Delete this file?')"><i class="bi bi-x-lg"></i></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- QR & Payment -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">QR Code & Payment</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">QR Destination</label>
                            <select name="qr_destination" class="form-select">
                                <?php foreach (['event_page'=>'Event Page','pdf'=>'PDF','programme'=>'Programme','gallery'=>'Gallery','google_maps'=>'Google Maps','whatsapp'=>'WhatsApp','phone'=>'Phone','email'=>'Email','website'=>'Website','youtube'=>'YouTube','google_drive'=>'Google Drive','onedrive'=>'OneDrive','dropbox'=>'Dropbox','custom_url'=>'Custom URL'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($event['qr_destination'] ?? 'event_page') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Custom URL (if selected above)</label>
                            <input type="url" name="qr_custom_url" class="form-control" value="<?= htmlspecialchars($event['qr_custom_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-2">
                                <input type="checkbox" name="payment_required" class="form-check-input" value="1" <?= ($event['payment_required'] ?? 0) ? 'checked' : '' ?> onchange="document.getElementById('paymentFields').style.display=this.checked?'':'none'">
                                <label class="form-check-label fw-semibold">Require Payment</label>
                            </div>
                        </div>
                    </div>
                    <div id="paymentFields" class="row g-3 mt-1" style="display:<?= ($event['payment_required'] ?? 0) ? '' : 'none' ?>">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Type</label>
                            <select name="payment_type" class="form-select">
                                <?php foreach (['one_time'=>'One-Time','registration'=>'Registration Fee','ticket'=>'Ticket','donation'=>'Donation','fixed_donation'=>'Fixed Donation','pay_what_you_want'=>'Pay What You Want','premium'=>'Premium Access'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($event['payment_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Amount (GHS)</label>
                            <input type="number" name="payment_amount" class="form-control" step="0.01" value="<?= $event['payment_amount'] ?? '' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Submit -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?= ($event['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= ($event['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= ($event['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex gap-2 justify-content-end">
                            <a href="/dashboard/events" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> <?= $isEdit ? 'Save Changes' : 'Create Event' ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var existingDynamic = <?= json_encode(json_decode($event['dynamic_fields'] ?? '{}', true) ?: new \stdClass()) ?>;

function loadDynamicFields(typeId) {
    var sel = document.getElementById('eventType');
    var opt = sel.options[sel.selectedIndex];
    var fields = {};
    try { fields = JSON.parse(opt.getAttribute('data-fields') || '{}'); } catch(e) {}
    var container = document.getElementById('dynamicFieldsContainer');
    var card = document.getElementById('dynamicFieldsCard');
    container.innerHTML = '';

    var keys = Object.keys(fields);
    if (keys.length === 0) { card.style.display = 'none'; return; }
    card.style.display = '';

    var row = document.createElement('div');
    row.className = 'row g-3';
    keys.forEach(function(key) {
        var type = fields[key];
        var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        var val = existingDynamic[key] || '';
        var col = document.createElement('div');
        col.className = type === 'textarea' ? 'col-12' : 'col-md-6';
        var html = '<label class="form-label small fw-semibold">' + label + '</label>';
        if (type === 'textarea') {
            html += '<textarea name="dynamic[' + key + ']" class="form-control" rows="3">' + val + '</textarea>';
        } else {
            html += '<input type="' + (type === 'url' ? 'url' : type === 'number' ? 'number' : 'text') + '" name="dynamic[' + key + ']" class="form-control" value="' + val + '">';
        }
        col.innerHTML = html;
        row.appendChild(col);
    });
    container.appendChild(row);
}

document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('eventType');
    if (sel.value) loadDynamicFields(sel.value);
});
</script>
