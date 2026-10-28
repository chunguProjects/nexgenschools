<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
require_once('../model/GalleryModel.php');
require_once('../helpers/ImageUpload.php');

$model = new GalleryModel($conn);
$message = null;
$messageType = 'success';

// Pull any message left over from a redirect (Post/Redirect/Get)
if (isset($_SESSION['gallery_flash'])) {
    $message = $_SESSION['gallery_flash']['message'];
    $messageType = $_SESSION['gallery_flash']['type'];
    unset($_SESSION['gallery_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // =========================
    // Upload image
    // =========================
    if ($_POST['action'] === 'upload' && !empty($_FILES['image']['name'])) {

        try {
            $uploadDir = __DIR__ . '/../uploads/gallery/';

            // Automatically resize + compress
            $imagePath = uploadOptimizedImage(
                $_FILES['image'],
                $uploadDir,
                1600, // max width
                78    // JPEG quality
            );

            $caption = trim($_POST['caption'] ?? '');

            if ($model->create($imagePath, $caption ?: null)) {
                $message = 'Image added to the gallery.';
                $messageType = 'success';
            } else {
                $message = 'Image saved but database insert failed.';
                $messageType = 'danger';
            }

        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }

        // Redirect so a page reload doesn't resubmit the form
        $_SESSION['gallery_flash'] = ['message' => $message, 'type' => $messageType];
        header('Location: admin.php?page=gallery');
        exit;
    }

    // =========================
    // Delete image
    // =========================
    if ($_POST['action'] === 'delete' && !empty($_POST['gallery_id'])) {

        $galleryId = (int) $_POST['gallery_id'];
        $item = $model->getById($galleryId);

        if ($item) {

            if ($model->delete($galleryId)) {

                $fullPath = __DIR__ . '/../' . $item['image_path'];

                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }

                $message = 'Image removed.';
                $messageType = 'success';
            } else {
                $message = 'Could not remove image.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Image not found.';
            $messageType = 'danger';
        }

        // Redirect so a page reload doesn't resubmit the delete
        $_SESSION['gallery_flash'] = ['message' => $message, 'type' => $messageType];
        header('Location: admin.php?page=gallery');
        exit;
    }
}

$images = $model->getAll();
?>

<div class="container-fluid py-3">

    <div class="lg-page-header">
        <h4 class="lg-page-title mb-0">
            <i class="fa-solid fa-images me-2" style="color:var(--gold);"></i>Gallery
        </h4>

        <a href="admin.php?page=settings-general" class="lg-btn-back">
            <i class="fa fa-arrow-left"></i> Back to Website Management
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Upload form -->
    <div class="lg-form-card mb-4">

        <h4>Add an Image</h4>
        <div class="lg-form-divider"></div>

        <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">

            <input type="hidden" name="action" value="upload">

            <div class="col-md-6">
                <label class="form-label">Image</label>

                <input type="file"
                       name="image"
                       class="form-control"
                       accept="image/jpeg,image/png,image/webp"
                       required>

                <small class="text-muted">
                    Large photos are automatically resized and compressed for faster website loading.
                </small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Caption (optional)</label>

                <input type="text"
                       name="caption"
                       class="form-control"
                       maxlength="255">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-ledger-solid w-100">
                    <i class="fa fa-upload me-1"></i> Upload
                </button>
            </div>

        </form>
    </div>

    <?php if (empty($images)): ?>

        <div class="alert alert-warning">No images in the gallery yet.</div>

    <?php else: ?>

        <div class="row g-3">

            <?php foreach ($images as $img): ?>

                <div class="col-6 col-md-4 col-lg-3">

                    <div class="card shadow-sm h-100">

                        <img src="../<?= htmlspecialchars($img['image_path']) ?>"
                             class="card-img-top"
                             width="400"
                             height="300"
                             loading="lazy"
                             decoding="async"
                             style="height:150px;object-fit:cover;"
                             alt="<?= htmlspecialchars($img['caption'] ?? '') ?>">

                        <div class="card-body p-2">

                            <p class="mb-2" style="font-size:13px;color:var(--muted);">
                                <?= $img['caption']
                                    ? htmlspecialchars($img['caption'])
                                    : '<em>No caption</em>' ?>
                            </p>

                            <form method="post"
                                  onsubmit="return confirm('Remove this image from the gallery?');">

                                <input type="hidden" name="action" value="delete">
                                <input type="hidden"
                                       name="gallery_id"
                                       value="<?= (int) $img['gallery_id'] ?>">

                                <button type="submit" class="btn btn-ledger-danger btn-sm w-100">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </form>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>