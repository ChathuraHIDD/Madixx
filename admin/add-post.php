<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$errors = [];
$post = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $post = [
        'title'    => trim((string) ($_POST['title'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? '')),
        'author'   => trim((string) ($_POST['author'] ?? 'Glowelle Team')),
        'excerpt'  => trim((string) ($_POST['excerpt'] ?? '')),
        'content'  => (string) ($_POST['content'] ?? ''),
        'status'   => in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'draft',
    ];

    if ($post['title'] === '') $errors[] = 'Title is required.';
    if (trim($post['content']) === '') $errors[] = 'Content is required.';

    if (!$errors) {
        $image = 'assets/images/placeholder-blog.jpg';
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = upload_image($_FILES['featured_image'], 'blog');
            if ($uploaded) $image = $uploaded;
        }

        $slug = unique_slug('blog_posts', $post['title']);

        $stmt = $pdo->prepare(
            'INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, author, status)
             VALUES (:title, :slug, :excerpt, :content, :image, :category, :author, :status)'
        );
        $stmt->execute([
            'title'    => $post['title'],
            'slug'     => $slug,
            'excerpt'  => $post['excerpt'],
            'content'  => $post['content'],
            'image'    => $image,
            'category' => $post['category'],
            'author'   => $post['author'],
            'status'   => $post['status'],
        ]);

        flash('success', 'Post "' . $post['title'] . '" created.');
        redirect('admin/blog.php');
    }
}

$pageTitle = 'Add Blog Post';
$activeAdminPage = 'blog';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/post-form.php';
require __DIR__ . '/includes/admin-footer.php';
