<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
require_once('../model/AnnouncementModel.php');

$model = new AnnouncementModel($conn);
$message = null;
$messageType = 'success';

// Pull any message left over from a redirect (Post/Redirect/Get)
if (isset($_SESSION['announcements_flash'])) {
    $message = $_SESSION['announcements_flash']['message'];
    $messageType = $_SESSION['announcements_flash']['type'];
    unset($_SESSION['announcements_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $published = isset($_POST['is_published']);

        if ($title === '' || $body === '') {
            $message = 'Title and body are both required.';
            $messageType = 'danger';
        } else {
            $model->create($title, $body, $published);
            $message = 'Announcement posted.';
            $messageType = 'success';
        }

        $_SESSION['announcements_flash'] = ['message' => $message, 'type' => $messageType];
        header('Location: admin.php?page=announcements');
        exit;
    }

    if ($_POST['action'] === 'toggle' && !empty($_POST['announcement_id'])) {
        $model->togglePublished((int) $_POST['announcement_id']);
        $message = 'Announcement updated.';
        $messageType = 'success';

        $_SESSION['announcements_flash'] = ['message' => $message, 'type' => $messageType];
        header('Location: admin.php?page=announcements');
        exit;
    }

    if ($_POST['action'] === 'delete' && !empty($_POST['announcement_id'])) {
        $model->delete((int) $_POST['announcement_id']);
        $message = 'Announcement deleted.';
        $messageType = 'success';

        $_SESSION['announcements_flash'] = ['message' => $message, 'type' => $messageType];
        header('Location: admin.php?page=announcements');
        exit;
    }
}

$announcements = $model->getAll();
?>

<div class="container-fluid py-3">
    <div class="lg-page-header">
        <h4 class="lg-page-title mb-0"><i class="fa-solid fa-bullhorn me-2" style="color:var(--gold);"></i>Announcements</h4>
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

    <div class="lg-form-card mb-4">
        <h4>New Announcement</h4>
        <div class="lg-form-divider"></div>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" maxlength="255" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Body</label>
                <textarea name="body" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_published" id="isPublished" class="form-check-input" checked>
                <label class="form-check-label" for="isPublished">Publish immediately</label>
            </div>
            <button type="submit" class="btn btn-ledger-solid">
                <i class="fa fa-paper-plane me-1"></i> Post Announcement
            </button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($announcements)): ?>
                <p class="text-muted mb-0">No announcements yet.</p>
            <?php else: ?>
                <table class="table lg-table align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($a['title']) ?></strong>
                                    <div class="text-muted" style="font-size:12.5px; max-width:420px;">
                                        <?= htmlspecialchars(mb_strimwidth($a['body'], 0, 120, '…')) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($a['is_published']): ?>
                                        <span class="badge" style="background:var(--green-dark);">Published</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:var(--muted);">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:13px;color:var(--muted);">
                                    <?= htmlspecialchars(date('d M Y', strtotime($a['created_at']))) ?>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="announcement_id" value="<?= (int) $a['announcement_id'] ?>">
                                        <button type="submit" class="btn btn-ledger-outline btn-sm">
                                            <?= $a['is_published'] ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this announcement permanently?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="announcement_id" value="<?= (int) $a['announcement_id'] ?>">
                                        <button type="submit" class="btn btn-ledger-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>