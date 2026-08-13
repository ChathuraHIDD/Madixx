<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$errors = [];
$success = false;
$user = current_user();
$name = $user['name'] ?? '';
$email = $user['email'] ?? '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($message === '') $errors[] = 'Please enter a message.';

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)');
        $stmt->execute(['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message]);
        $success = true;
        $name = $email = $subject = $message = '';
    }
}

$pageTitle = 'Contact Us — Glowelle';
$metaDescription = 'Get in touch with the Glowelle team — we would love to hear from you.';
$canonicalPath = 'contact.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'Contact', 'url' => null]]) ?>

  <div class="editorial-split" style="align-items:flex-start;">
    <div>
      <p class="eyebrow">Get In Touch</p>
      <h1 style="font-size:2.2rem;margin-bottom:16px;">Contact Us</h1>
      <p style="color:var(--text-muted);margin-bottom:32px;">Have a question about an order, a product, or just want to say hello? We'd love to hear from you.</p>

      <div style="margin-bottom:20px;">
        <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:8px;">Email</h4>
        <p>hello@glowelle.com</p>
      </div>
      <div style="margin-bottom:20px;">
        <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:8px;">Phone</h4>
        <p>+94 77 000 0000</p>
      </div>
      <div>
        <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:8px;">Hours</h4>
        <p>Monday – Friday, 9am – 6pm</p>
      </div>
    </div>

    <div>
      <?php if ($success): ?>
      <div class="flash flash-success" style="max-width:none;">Thank you! Your message has been sent — we'll be in touch soon.</div>
      <?php else: ?>
      <?php if ($errors): ?>
      <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
      <?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group"><label>Your Name</label><input type="text" name="name" required value="<?= e($name) ?>"></div>
          <div class="form-group"><label>Email Address</label><input type="email" name="email" required value="<?= e($email) ?>"></div>
        </div>
        <div class="form-group"><label>Subject</label><input type="text" name="subject" value="<?= e($subject) ?>"></div>
        <div class="form-group"><label>Message</label><textarea name="message" required><?= e($message) ?></textarea></div>
        <button type="submit" class="btn btn-primary">Send Message</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
