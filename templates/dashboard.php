<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Brochures</h2>
        <p class="text-muted small mb-0"><?= $total ?> total</p>
    </div>
    <a href="/admin/upload" class="btn btn-dark">
        <i class="bi bi-plus-lg"></i> Upload New
    </a>
</div>

<form class="mb-4" method="GET">
    <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search by name, location, or title…"
               value="<?= htmlspecialchars($search) ?>">
        <?php if ($search): ?>
        <a href="/admin" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($brochures)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-inbox display-3"></i>
    <p class="mt-3"><?= $search ? 'No results found' : 'No brochures yet' ?></p>
    <?php if (!$search): ?>
    <a href="/admin/upload" class="btn btn-outline-dark btn-sm">Upload your first brochure</a>
    <?php endif; ?>
</div>

<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width:70px">QR</th>
                <th>Deceased</th>
                <th>Location</th>
                <th>Title</th>
                <th>Date</th>
                <th style="width:100px" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($brochures as $b): ?>
            <tr>
                <td>
                    <img src="/admin/qr/<?= $b['id'] ?>" alt="QR" width="50" height="50"
                         class="rounded border" style="cursor:pointer"
                         onclick="showQR(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['slug'])) ?>', '<?= htmlspecialchars(addslashes($b['deceased_name'])) ?>')">
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($b['deceased_name']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($b['funeral_location']) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($b['title'] ?? '—') ?></td>
                <td class="text-muted small"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="/brochure/<?= htmlspecialchars($b['slug']) ?>" target="_blank">
                                    <i class="bi bi-eye"></i> View Page
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/admin/edit/<?= $b['id'] ?>">
                                    <i class="bi bi-pencil"></i> Edit Details
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="showQR(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['slug'])) ?>', '<?= htmlspecialchars(addslashes($b['deceased_name'])) ?>')">
                                    <i class="bi bi-qr-code"></i> View QR Code
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/admin/qr/<?= $b['id'] ?>?download=1">
                                    <i class="bi bi-download"></i> Download QR
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="printQR(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['deceased_name'])) ?>')">
                                    <i class="bi bi-printer"></i> Print QR
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showReplace(<?= $b['id'] ?>)">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Replace PDF
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#"
                                   onclick="confirmDelete(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['deceased_name'])) ?>')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- QR Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="qrModalTitle">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrModalImg" src="" class="img-fluid mb-3" style="max-width:250px">
                <p class="text-muted small mb-0" id="qrModalUrl"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <a id="qrModalDownload" href="" class="btn btn-dark btn-sm">
                    <i class="bi bi-download"></i> Download
                </a>
                <button onclick="printQRFromModal()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Replace PDF Modal -->
<div class="modal fade" id="replaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="replaceForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Replace PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">The QR code URL stays the same. The old PDF will be deleted.</p>
                    <input type="file" name="pdf" accept=".pdf" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-file-earmark-arrow-up"></i> Replace
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Delete <strong id="deleteName"></strong>?</p>
                <p class="text-danger small mb-0">This permanently removes the brochure, PDF, and QR code.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showQR(id, slug, name) {
    document.getElementById('qrModalTitle').textContent = name;
    document.getElementById('qrModalImg').src = '/admin/qr/' + id;
    document.getElementById('qrModalUrl').textContent = location.origin + '/brochure/' + slug;
    document.getElementById('qrModalDownload').href = '/admin/qr/' + id + '?download=1';
    new bootstrap.Modal(document.getElementById('qrModal')).show();
}

function printQR(id, name) {
    var w = window.open('', '_blank');
    w.document.write('<html><head><title>QR - ' + name + '</title><style>body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;font-family:Arial,sans-serif;margin:0}img{width:300px;height:300px}h2{margin:1rem 0 .5rem;font-size:1.2rem}</style></head><body><img src="/admin/qr/' + id + '"><h2>' + name + '</h2></body></html>');
    w.document.close();
    w.onload = function() { w.print(); };
}

function printQRFromModal() {
    var img = document.getElementById('qrModalImg').src;
    var name = document.getElementById('qrModalTitle').textContent;
    var w = window.open('', '_blank');
    w.document.write('<html><head><title>QR - ' + name + '</title><style>body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;font-family:Arial,sans-serif;margin:0}img{width:300px;height:300px}h2{margin:1rem 0 .5rem;font-size:1.2rem}</style></head><body><img src="' + img + '"><h2>' + name + '</h2></body></html>');
    w.document.close();
    w.onload = function() { w.print(); };
}

function showReplace(id) {
    document.getElementById('replaceForm').action = '/admin/replace/' + id;
    new bootstrap.Modal(document.getElementById('replaceModal')).show();
}

function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/delete/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
