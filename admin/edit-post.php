<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$postId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = :id');
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch();

if (!$post) {
    flash('error', 'Post not found.');
    redirect('admin/blog.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $title = trim((string) ($_POST['title'] ?? ''));
    $content = (string) ($_POST['content'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if (trim($content) === '') $errors[] = 'Content is required.';

    if (!$errors) {
        $image = $post['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = upload_image($_FILES['featured_image'], 'blog');
            if ($uploaded) $image = $uploaded;
        }

        $slug = $title !== $post['title'] ? unique_slug('blog_posts', $title, $postId) : $post['slug'];

        $pdo->prepare(
            'UPDATE blog_posts SET title = :title, slug = :slug, excerpt = :excerpt, content = :content,
             featured_image = :image, category = :category, author = :author, status = :status WHERE id = :id'
        )->execute([
            'title'    => $title,
            'slug'     => $slug,
            'excerpt'  => trim((string) ($_POST['excerpt'] ?? '')),
            'content'  => $content,
            'image'    => $image,
            'category' => trim((string) ($_POST['category'] ?? '')),
            'author'   => trim((string) ($_POST['author'] ?? 'Glowelle Team')),
            'status'   => in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'draft',
            'id'       => $postId,
        ]);

        flash('success', 'Post updated.');
        redirect('admin/edit-post.php?id=' . $postId);
    }

    $stmt->execute(['id' => $postId]);
    $post = $stmt->fetch();
}

$pageTitle = 'Edit Blog Post';
$activeAdminPage = 'blog';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/post-form.php';
require __DIR__ . '/includes/admin-footer.php';
