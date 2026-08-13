<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = (string) ($_POST['action'] ?? '');
    $postId = (int) ($_POST['post_id'] ?? 0);

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM blog_posts WHERE id = :id')->execute(['id' => $postId]);
        flash('success', 'Post deleted.');
    } elseif ($action === 'toggle_status') {
        $newStatus = (string) $_POST['new_status'] === 'published' ? 'published' : 'draft';
        $pdo->prepare('UPDATE blog_posts SET status = :status WHERE id = :id')->execute(['status' => $newStatus, 'id' => $postId]);
        flash('success', 'Post status updated.');
    }
    redirect('admin/blog.php');
}

$posts = $pdo->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Blog Posts';
$activeAdminPage = 'blog';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3 style="margin:0;"><?= count($posts) ?> Posts</h3>
    <a href="<?= e(base_url('admin/add-post.php')) ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Post</a>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th></th><th>Title</th><th>Category</th><th>Author</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
          <td><img src="<?= e(base_url($post['featured_image'])) ?>" class="table-thumb" alt=""></td>
          <td><a href="<?= e(base_url('admin/edit-post.php?id=' . (int) $post['id'])) ?>"><?= e($post['title']) ?></a></td>
          <td><?= e($post['category']) ?></td>
          <td><?= e($post['author']) ?></td>
          <td><?= e(date('M j, Y', strtotime($post['created_at']))) ?></td>
          <td><span class="status-pill status-<?= e($post['status']) ?>"><?= e(ucfirst($post['status'])) ?></span></td>
          <td>
            <div style="display:flex;gap:8px;">
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="new_status" value="<?= $post['status'] === 'published' ? 'draft' : 'published' ?>">
                <button type="submit" class="btn btn-outline btn-sm"><?= $post['status'] === 'published' ? 'Unpublish' : 'Publish' ?></button>
              </form>
              <a href="<?= e(base_url('admin/edit-post.php?id=' . (int) $post['id'])) ?>" class="btn btn-outline btn-sm">Edit</a>
              <form method="post" data-confirm="Delete this post?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?><tr><td colspan="7" class="empty-row">No posts yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
