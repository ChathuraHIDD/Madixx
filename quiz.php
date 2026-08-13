<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Beauty Quiz — Find Your Glow — Glowelle';
$metaDescription = 'Take the Glowelle Beauty Quiz and get personalized skincare recommendations in under a minute.';
$canonicalPath = 'quiz.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight quiz-wrap">
  <div class="text-center" style="margin-bottom:8px;">
    <p class="eyebrow">Beauty Quiz</p>
    <h1 style="font-size:2.2rem;">Find Your Glow</h1>
    <p style="color:var(--text-muted);">Answer three quick questions for personalized product recommendations.</p>
  </div>

  <div id="quizForm" style="margin-top:40px;">
    <div class="quiz-progress"><div class="quiz-progress-bar" id="quizProgressBar" style="width:33.3%;"></div></div>

    <div class="quiz-question active" data-question="1" data-field="skin_type">
      <h2 class="text-center">What is your skin type?</h2>
      <div class="quiz-options">
        <?php foreach (['Dry', 'Oily', 'Combination', 'Normal', 'Sensitive'] as $opt): ?>
        <div class="quiz-option" data-value="<?= e($opt) ?>"><?= e($opt) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="quiz-question" data-question="2" data-field="concern">
      <h2 class="text-center">What is your main concern?</h2>
      <div class="quiz-options">
        <?php foreach (['Acne', 'Dryness', 'Dullness', 'Aging', 'Sensitivity'] as $opt): ?>
        <div class="quiz-option" data-value="<?= e($opt) ?>"><?= e($opt) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="quiz-question" data-question="3" data-field="goal">
      <h2 class="text-center">What are you looking for?</h2>
      <div class="quiz-options">
        <?php foreach (['Hydration', 'Brightening', 'Anti-aging', 'Skin barrier', 'Glow'] as $opt): ?>
        <div class="quiz-option" data-value="<?= e($opt) ?>"><?= e($opt) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="quiz-nav">
      <button type="button" class="btn btn-outline" id="quizBackBtn" style="visibility:hidden;">Back</button>
      <span id="quizStepLabel" style="color:var(--text-muted);font-size:0.85rem;align-self:center;">Question 1 of 3</span>
      <span></span>
    </div>
  </div>

  <div id="quizResults" style="display:none;margin-top:24px;">
    <div class="text-center" style="margin-bottom:32px;">
      <p class="eyebrow">Personalized For You</p>
      <h2>Your Glowelle Recommendations</h2>
    </div>
    <div class="product-grid" id="quizResultsGrid"></div>
    <div class="text-center" style="margin-top:40px;">
      <button type="button" class="btn btn-text" id="quizRetakeBtn">Retake the Quiz</button>
    </div>
  </div>
</div>

<script>
(function () {
  const CFG = window.GLOWELLE;
  const answers = {};
  let currentStep = 1;
  const totalSteps = 3;

  const questions = document.querySelectorAll('.quiz-question');
  const progressBar = document.getElementById('quizProgressBar');
  const stepLabel = document.getElementById('quizStepLabel');
  const backBtn = document.getElementById('quizBackBtn');

  function showStep(step) {
    questions.forEach((q) => q.classList.toggle('active', parseInt(q.dataset.question, 10) === step));
    progressBar.style.width = ((step / totalSteps) * 100) + '%';
    stepLabel.textContent = 'Question ' + step + ' of ' + totalSteps;
    backBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
  }

  document.querySelectorAll('.quiz-question').forEach((question) => {
    question.querySelectorAll('.quiz-option').forEach((option) => {
      option.addEventListener('click', async () => {
        question.querySelectorAll('.quiz-option').forEach((o) => o.classList.remove('selected'));
        option.classList.add('selected');
        answers[question.dataset.field] = option.dataset.value;

        await new Promise((r) => setTimeout(r, 250));

        if (currentStep < totalSteps) {
          currentStep++;
          showStep(currentStep);
        } else {
          await submitQuiz();
        }
      });
    });
  });

  backBtn.addEventListener('click', () => {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  });

  async function submitQuiz() {
    document.getElementById('quizForm').style.display = 'none';
    document.getElementById('quizResults').style.display = 'block';
    document.getElementById('quizResultsGrid').innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--text-muted);">Finding your perfect matches…</p>';

    const res = await fetch(CFG.baseUrl + 'ajax/quiz_recommend.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ csrf_token: CFG.csrfToken, ...answers }),
    });
    const data = await res.json();
    document.getElementById('quizResultsGrid').innerHTML = data.html || '<p>No recommendations found.</p>';
  }

  document.getElementById('quizRetakeBtn').addEventListener('click', () => {
    Object.keys(answers).forEach((k) => delete answers[k]);
    currentStep = 1;
    document.querySelectorAll('.quiz-option.selected').forEach((o) => o.classList.remove('selected'));
    showStep(1);
    document.getElementById('quizResults').style.display = 'none';
    document.getElementById('quizForm').style.display = 'block';
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
