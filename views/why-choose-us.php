<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
require_once('../model/WhyChooseUsModel.php');
require_once('../helpers/ImageUpload.php');

$model = new WhyChooseUsModel($conn);

$message = null;
$messageType = 'success';

// Pull any message left over from a redirect (see Post/Redirect/Get below)
if (isset($_SESSION['why_flash'])) {
    $message = $_SESSION['why_flash']['message'];
    $messageType = $_SESSION['why_flash']['type'];
    unset($_SESSION['why_flash']);
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ---------------------------------------------------------
// Create / Update
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id          = (int) ($_POST['id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $body        = trim($_POST['body'] ?? '');
    $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
    $isActive    = isset($_POST['is_active']) ? 1 : 0;
    $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

    $existing  = $id ? $model->getById($id) : null;
    $imagePath = $existing['image_path'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        try {
            $imagePath = uploadOptimizedImage($_FILES['image'], $uploadDir, 1600, 78, 'whyus');

            if (!empty($existing['image_path'])) {
                $oldFullPath = __DIR__ . '/../' . $existing['image_path'];
                if (file_exists($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
    } elseif ($removeImage && !empty($existing['image_path'])) {
        $oldFullPath = __DIR__ . '/../' . $existing['image_path'];
        if (file_exists($oldFullPath)) {
            @unlink($oldFullPath);
        }
        $imagePath = null;
    }

    if ($message === null) {
        $data = [
            'title'      => $title,
            'body'       => $body,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive,
        ];

        if ($title === '' || $body === '') {
            $message = 'Title and description are required.';
            $messageType = 'danger';
        } elseif ($id && $existing) {
            $ok = $model->update($id, $data);
            $message = $ok ? 'Item updated successfully.' : 'Could not update item.';
            $messageType = $ok ? 'success' : 'danger';
        } else {
            $ok = $model->create($data);
            $message = $ok ? 'Item added successfully.' : 'Could not add item.';
            $messageType = $ok ? 'success' : 'danger';
        }
    }

    // Redirect so a page reload doesn't resubmit the form
    $_SESSION['why_flash'] = ['message' => $message, 'type' => $messageType];
    header('Location: admin.php?page=why-choose-us');
    exit;
}

// ---------------------------------------------------------
// Delete
// ---------------------------------------------------------
if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['id'])) {
    $itemToDelete = $model->getById((int) $_GET['id']);
    if ($itemToDelete) {
        if (!empty($itemToDelete['image_path'])) {
            $fullPath = __DIR__ . '/../' . $itemToDelete['image_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $model->delete((int) $_GET['id']);
    }
    header('Location: admin.php?page=website-why-choose-us');
    exit;
}

$items = $model->getAll();
?>

<div class="container-fluid py-3">
    <div class="lg-page-header">
        <h4 class="lg-page-title mb-0"><i class="fa-solid fa-star me-2" style="color:var(--gold);"></i>Why Choose Us</h4>
        <div class="d-flex gap-2">
            <a href="admin.php?page=settings-general" class="lg-btn-add">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="lg-btn-add" data-bs-toggle="modal" data-bs-target="#whyModal" onclick="resetWhyForm()">
                <i class="fa fa-plus"></i> Add New Card
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <p class="text-muted" style="font-size:13.5px;">
        These show as a rotating "Why Choose Us" section on the homepage — one card at a
        time, text on the left and its image on the right. Add as many as you like; drag
        the sort order to change how they cycle, and untick "Active" to hide one without
        deleting it.
    </p>

    <?php if (!$items): ?>
        <div class="alert alert-info">No cards yet — click "Add New Card" to create the first one.</div>
    <?php else: ?>
        <div class="lg-form-card p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th style="width:80px;">Order</th>
                        <th style="width:90px;">Active</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt=""
                                         style="width:56px;height:56px;object-fit:cover;border-radius:.5rem;">
                                <?php else: ?>
                                    <span class="text-muted"><i class="fa-regular fa-image"></i></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td style="max-width:360px;"><?= htmlspecialchars(mb_strimwidth($item['body'], 0, 100, '…')) ?></td>
                            <td><?= (int) $item['sort_order'] ?></td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick='editWhyItem(<?= json_encode($item) ?>)'
                                        data-bs-toggle="modal" data-bs-target="#whyModal">
                                    <i class="fa fa-pen"></i>
                                </button>
                                <a href="admin.php?page=website-why-choose-us&action=delete&id=<?= $item['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this card? This cannot be undone.');">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add / Edit modal -->
<div class="modal fade" id="whyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="whyId" value="">

            <div class="modal-header">
                <h5 class="modal-title" id="whyModalLabel">Add New Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="whyTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="body" id="whyBody" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <div id="whyCurrentImageWrap" class="mb-2" style="display:none;">
                        <img id="whyCurrentImage" src="" alt="" style="max-height:80px;border-radius:.5rem;display:block;margin-bottom:6px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="whyRemoveImage" name="remove_image" value="1">
                            <label class="form-check-label" for="whyRemoveImage" style="font-size:13px;">
                                Remove current image
                            </label>
                        </div>
                    </div>
                    <input type="file" name="image" id="whyImageInput" class="form-control" accept="image/*">
                    <div class="form-text">Leave blank to keep the existing image when editing. Uploading a new image replaces the old one automatically.</div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="whySortOrder" class="form-control" value="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="whyIsActive" checked>
                            <label class="form-check-label" for="whyIsActive">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-ledger-solid"><i class="fa fa-save me-1"></i> Save Card</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetWhyForm() {
    document.getElementById('whyModalLabel').textContent = 'Add New Card';
    document.getElementById('whyId').value = '';
    document.getElementById('whyTitle').value = '';
    document.getElementById('whyBody').value = '';
    document.getElementById('whySortOrder').value = 0;
    document.getElementById('whyIsActive').checked = true;
    document.getElementById('whyCurrentImageWrap').style.display = 'none';
    document.getElementById('whyRemoveImage').checked = false;
    document.getElementById('whyImageInput').value = '';
}

function editWhyItem(item) {
    document.getElementById('whyModalLabel').textContent = 'Edit Card';
    document.getElementById('whyId').value = item.id;
    document.getElementById('whyTitle').value = item.title;
    document.getElementById('whyBody').value = item.body;
    document.getElementById('whySortOrder').value = item.sort_order;
    document.getElementById('whyIsActive').checked = item.is_active == 1;
    document.getElementById('whyRemoveImage').checked = false;
    document.getElementById('whyImageInput').value = '';

    const imgWrap = document.getElementById('whyCurrentImageWrap');
    const img = document.getElementById('whyCurrentImage');
    if (item.image_path) {
        img.src = '../' + item.image_path;
        imgWrap.style.display = 'block';
    } else {
        imgWrap.style.display = 'none';
    }
}
</script>