<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$faqs = [
    ['q' => 'How long does shipping take?', 'a' => 'Standard delivery takes 3–5 business days. Express delivery arrives in 1–2 business days. Orders over $75 qualify for free standard shipping.'],
    ['q' => 'What payment methods do you accept?', 'a' => 'We currently accept Cash on Delivery and Bank Transfer. Online card payments are coming soon.'],
    ['q' => 'Can I return or exchange a product?', 'a' => 'Yes — unopened products can be returned within 14 days of delivery for a full refund. Please contact us to start a return.'],
    ['q' => 'Are MADIXX products cruelty-free?', 'a' => 'Yes, all MADIXX formulas are cruelty-free and never tested on animals.'],
    ['q' => 'How do I track my order?', 'a' => 'Use the order number from your confirmation email or account on our Order Tracking page to see real-time status updates.'],
    ['q' => 'Do you ship internationally?', 'a' => 'We currently ship within Sri Lanka. International shipping is on our roadmap — sign up to our newsletter for updates.'],
    ['q' => 'How do I know which products are right for my skin?', 'a' => 'Take our 1-minute Beauty Quiz for personalized product recommendations based on your skin type and goals.'],
    ['q' => 'Can I cancel or change my order after placing it?', 'a' => 'Please contact us as soon as possible — we can usually amend or cancel orders that have not yet shipped.'],
];

$pageTitle = 'FAQ — MADIXX';
$metaDescription = 'Frequently asked questions about MADIXX orders, shipping, returns and products.';
$canonicalPath = 'faq.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight" style="max-width:820px;">
  <?= render_breadcrumbs([['label' => 'FAQ', 'url' => null]]) ?>
  <div class="section-heading" style="margin-bottom:40px;">
    <p class="eyebrow">Support</p>
    <h1 style="font-size:2.2rem;">Frequently Asked Questions</h1>
    <p>Everything you need to know about shopping with MADIXX.</p>
  </div>

  <div class="faq-list">
    <?php foreach ($faqs as $faq): ?>
    <div class="faq-item">
      <button type="button" class="faq-question"><?= e($faq['q']) ?> <i class="fa-solid fa-plus"></i></button>
      <div class="faq-answer"><p><?= e($faq['a']) ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center" style="margin-top:48px;">
    <p style="color:var(--text-muted);margin-bottom:16px;">Still have questions?</p>
    <a href="<?= e(base_url('contact.php')) ?>" class="btn btn-primary">Contact Us</a>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
