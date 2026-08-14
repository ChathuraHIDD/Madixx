<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$faqs = [
    ['q' => 'How long does shipping take?', 'a' => 'Standard delivery takes 3–5 business days. Express delivery arrives in 1–2 business days. Orders over $75 qualify for free standard shipping.'],
    ['q' => 'What payment methods do you accept?', 'a' => 'We accept Credit/Debit Card (securely processed by Stripe), Cash on Delivery, and Bank Transfer.'],
    ['q' => 'Can I return or exchange a product?', 'a' => 'Yes — unused frames in original packaging can be returned within 14 days of delivery for a full refund. Please contact us to start a return.'],
    ['q' => 'Can I fit my own prescription lenses into MADIXX spectacles?', 'a' => 'Yes — every spectacle frame is sold unglazed and ready for your optician to fit with single-vision, progressive or blue-light lenses.'],
    ['q' => 'Do MADIXX sunglasses block UV rays?', 'a' => 'Every pair of MADIXX sunglasses meets the UV400 standard, blocking 100% of UVA and UVB rays.'],
    ['q' => 'How do I track my order?', 'a' => 'Use the order number from your confirmation email or account on our Order Tracking page to see real-time status updates.'],
    ['q' => 'Do you ship internationally?', 'a' => 'We currently ship within Sri Lanka. International shipping is on our roadmap — sign up to our newsletter for updates.'],
    ['q' => 'How do I know which frame shape suits me?', 'a' => 'Take our 1-minute Frame Finder Quiz for personalized recommendations based on your face shape and style.'],
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
