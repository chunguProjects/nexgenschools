<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/db.php');
require_once('model/GeneralSettingsModel.php');
require_once('model/GalleryModel.php');
require_once('model/AnnouncementModel.php');
require_once('model/WhyChooseUsModel.php');

$settings = [];

if ($conn) {
    $model = new GeneralSettingsModel($conn);
    $settings = $model->getSettings();
}

$schoolName   = $settings['school_name'] ?? 'NextGen Schools';
$logoPath     = $settings['logo_path'] ?? null;
$motto        = $settings['motto'] ?? 'Smart School Management System';
$contactEmail = $settings['contact_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? '';
$contactAddress = $settings['address'] ?? 'Lilongwe, Malawi';
// Check if logo file exists
$logoUrl = null;

if (!empty($logoPath)) {
    $fullPath = __DIR__ . '/' . ltrim($logoPath, '/');

    if (file_exists($fullPath)) {
        $logoUrl = $logoPath;
    }
}

// Hero carousel: sourced from the gallery, each slide keeping its own
// caption. Falls back to the single admin-uploaded hero image (no caption),
// then the default illustration, so the hero never looks broken before any
// photos have been uploaded.
$galleryModel = $conn ? new GalleryModel($conn) : null;
$galleryImages = $galleryModel ? array_slice($galleryModel->getAll(), 0, 8) : [];

$heroSlides = [];
if ($galleryImages) {
    foreach ($galleryImages as $img) {
        $heroSlides[] = ['path' => $img['image_path'], 'caption' => $img['caption'] ?? ''];
    }
} else {
    $fallback = $settings['hero_image_path'] ?? null;
    $path = ($fallback && file_exists(__DIR__ . '/' . $fallback)) ? $fallback : 'images/school.png';
    $heroSlides[] = ['path' => $path, 'caption' => ''];
}

// Hero overlay copy — admin-editable (hero_heading already existed in your
// schema; hero_subtext is new). Falls back to sensible defaults.
$heroHeading = $settings['hero_heading'] ?? 'Excellence in Education, Discipline and Character';
$heroSubtext = $settings['hero_subtext'] ?? 'Preparing learners for university, careers and responsible citizenship through quality teaching, strong values and holistic development.';
// Latest published announcements (5) — shown one at a time, rotating.
$latestAnnouncements = [];
if ($conn) {
    $announcementModel = new AnnouncementModel($conn);
    $latestAnnouncements = array_slice($announcementModel->getAll(true), 0, 5);
}

// ---------------------------------------------------------------------
// Quick stats — students / teachers / classes / years active.
// Matches the actual NextGen schema:
//  - students.is_deleted is a soft-delete flag, so it's excluded
//  - there is no separate "teachers" table — teachers are users
//    whose role_id points to the 'teacher' row in roles
//  - classes are scoped per academic year, so we count only classes
//    belonging to the current academic year (academic_years.is_current = 1)
// ---------------------------------------------------------------------
$totalStudents = 0;
$totalTeachers = 0;
$totalClasses  = 0;

if ($conn) {
    if ($res = $conn->query(
        "SELECT COUNT(*) AS c FROM students WHERE is_deleted = 0"
    )) {
        $totalStudents = (int) $res->fetch_assoc()['c'];
    }

    if ($res = $conn->query(
        "SELECT COUNT(*) AS c
         FROM users u
         INNER JOIN roles r ON u.role_id = r.role_id
         WHERE r.name = 'teacher'"
    )) {
        $totalTeachers = (int) $res->fetch_assoc()['c'];
    }

    if ($res = $conn->query(
        "SELECT COUNT(*) AS c
         FROM classes cl
         INNER JOIN academic_years ay ON cl.academic_year_id = ay.academic_year_id
         WHERE ay.is_current = 1"
    )) {
        $totalClasses = (int) $res->fetch_assoc()['c'];
    }
}

// Founding year — admin-editable, falls back to current year (0 years shown)
$foundedYear = $settings['founded_year'] ?? date('Y');
$yearsActive = max(0, (int) date('Y') - (int) $foundedYear);

// ---------------------------------------------------------------------
// About Us — one flowing paragraph combining about/vision/mission text,
// no subheadings, no core values. Admin edits three separate fields
// (about_text, vision_text, mission_text) but they're joined together
// here into a single block of copy. Full text shows on the card itself;
// "Learn More" links out to a dedicated About page (views/about.php)
// for anyone who wants more.
// ---------------------------------------------------------------------
$aboutText   = $settings['about_text'] ?? "Our system provides a modern solution for school management. From student admissions to report cards and communication, everything is simplified in one platform.";
$visionText  = $settings['vision_text']  ?? 'To be a leading institution shaping future-ready learners.';
$missionText = $settings['mission_text'] ?? 'To provide quality, holistic education that empowers every student to reach their full potential.';

$aboutParagraph = trim($aboutText . ' ' . $visionText . ' ' . $missionText);

// Image shown beside the About paragraph — admin-editable, falls back to
// the hero image, then the default illustration.
$aboutImagePath = $settings['about_image'] ?? null;
if ($aboutImagePath && file_exists(__DIR__ . '/' . $aboutImagePath)) {
    $aboutImageUrl = $aboutImagePath;
} else {
    $fallbackHero = $settings['hero_image_path'] ?? null;
    $aboutImageUrl = ($fallbackHero && file_exists(__DIR__ . '/' . $fallbackHero)) ? $fallbackHero : 'images/school.png';
}

// ---------------------------------------------------------------------
// Leadership spotlight — only shown if admin has filled in a name.
// head_teacher_name already existed in the settings table; message/photo
// were added by migration_add_landing_fields.sql
// ---------------------------------------------------------------------
$htName    = $settings['head_teacher_name'] ?? '';
$htMessage = $settings['head_teacher_message'] ?? '';
$htPhoto   = $settings['head_teacher_photo'] ?? null;
$htPhotoUrl = ($htPhoto && file_exists(__DIR__ . '/' . $htPhoto)) ? $htPhoto : 'images/avatar-placeholder.png';

// ---------------------------------------------------------------------
// Admissions — admissions_open controls whether the hero's
// "Apply Now" button shows at all.
// ---------------------------------------------------------------------
$admissionsOpen = $settings['admissions_open'] ?? true;
$admissionsLink = $settings['admissions_link'] ?? 'views/admissions.php';

// ---------------------------------------------------------------------
// Why Choose Us — dynamic list from the database (any number of items,
// admin-managed via website-why-choose-us.php in the dashboard).
// Shown on the homepage as a one-at-a-time rotator: text left, image
// right. Each item's image falls back to the about image, then the
// default illustration, so a card is never shown with a broken image.
// ---------------------------------------------------------------------
$whyModel = $conn ? new WhyChooseUsModel($conn) : null;
$whyChooseUsRaw = $whyModel ? $whyModel->getAll(true) : [];

$whyChooseUs = [];
foreach ($whyChooseUsRaw as $item) {
    $imgPath = $item['image_path'] ?? null;
    if ($imgPath && file_exists(__DIR__ . '/' . $imgPath)) {
        $imgUrl = $imgPath;
    } else {
        $imgUrl = $aboutImageUrl; // falls back to the About section's image
    }
    $whyChooseUs[] = [
        'title' => $item['title'],
        'text'  => $item['body'],
        'image' => $imgUrl,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="description" content="School Management System for managing students, teachers, examinations and reports.">
  <meta name="keywords" content="School Management System, Student Portal, Results, Education, Malawi">
  <meta name="author" content="Steven Chungu">
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($schoolName) ?></title>
   <title><?= htmlspecialchars($schoolName) ?></title>

    <?php if (!empty($logoUrl)): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap (grid + navbar collapse JS + Carousel JS — visuals overridden by css/style.css) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Ledger theme fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

  <!-- Ledger theme — ALL styles (shared dashboard + landing page) now live
       in this single stylesheet. No inline <style> blocks, no second file. -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark lp-navbar fixed-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <?php if ($logoUrl): ?>
          <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($schoolName) ?> Logo"
               class="me-2 rounded-circle" style="max-height: 34px; max-width: 34px; object-fit: contain;">
        <?php else: ?>
          <i class="fa-solid fa-school me-2"></i>
        <?php endif; ?>
        <span class="lp-brand-text"><?= htmlspecialchars($schoolName) ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="views/about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#why-us">Why Choose Us</a></li>
          <li class="nav-item"><a class="nav-link" href="views/admissions.php">Admissions</a></li>
          <li class="nav-item"><a class="nav-link" href="views/view-announcements.php"><i class="fa-solid fa-bullhorn me-1"></i> Announcements</a></li>
        </ul>
        <a class="lp-btn-ghost ms-lg-3 mt-2 mt-lg-0 d-inline-block text-center" href="views/student-portal.php">
          <i class="fa-solid fa-file-lines me-1"></i> View Results
        </a>
        <a class="lp-btn-solid ms-lg-2 mt-2 mt-lg-0 d-inline-block text-center" href="views/login.php">
          <i class="fa-solid fa-user-lock me-1"></i> Staff Login
        </a>
      </div>
    </div>
</nav>
  <!-- ============================================================= -->
  <!-- 1. HERO: carousel + overlay CTA -->
  <!-- ============================================================= -->
  <div class="lp-hero-carousel position-relative">

    <div class="lp-hero-overlay">
      <div class="container h-100 d-flex align-items-center">
        <div class="lp-hero-content text-white">

          <div class="d-flex flex-wrap gap-2">
            <?php if ($admissionsOpen): ?>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

      <?php if (count($heroSlides) > 1): ?>
        <div class="carousel-indicators">
          <?php foreach ($heroSlides as $i => $slide): ?>
            <button type="button"
                    data-bs-target="#heroCarousel"
                    data-bs-slide-to="<?= $i ?>"
                    class="<?= $i === 0 ? 'active' : '' ?>"
                    aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                    aria-label="Slide <?= $i + 1 ?>">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="carousel-inner">
        <?php foreach ($heroSlides as $i => $slide): ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars($slide['path']) ?>"
                 alt="<?= htmlspecialchars($schoolName) ?>">

            <?php if (!empty($slide['caption'])): ?>
              <div class="carousel-caption d-none d-md-block">
                <p><?= htmlspecialchars($slide['caption']) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

  <!-- ============================================================= -->
  <!-- 2. STATS STRIP -->
  <!-- ============================================================= -->
  <section class="lp-stats lp-reveal">
    <div class="container">
      <div class="row g-4 text-center">
        <div class="col-6 col-md-3">
          <div class="lp-stat-num" data-count="<?= $totalStudents ?>">0</div>
          <div class="lp-stat-label">Students</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="lp-stat-num" data-count="<?= $totalTeachers ?>">0</div>
          <div class="lp-stat-label">Teachers</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="lp-stat-num" data-count="<?= $totalClasses ?>">0</div>
          <div class="lp-stat-label">Classes</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="lp-stat-num" data-count="<?= $yearsActive ?>">0</div>
          <div class="lp-stat-label">Years of Excellence</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================= -->
  <!-- 3. ABOUT: portrait text card (title + full text + Learn More) on
       the left, image on the right (about + vision + mission combined,
       no subheadings, no core values) -->
  <!-- ============================================================= -->
  <section id="about" class="lp-about lp-reveal">
    <div class="container">
      <div class="row g-4 align-items-stretch">
        <div class="col-md-5">
          <div class="lp-about-card h-100">
            <div class="lp-about-card-body">
              <h2 class="lp-section-title text-start mb-0">About Us</h2>
              <div class="lp-divider" style="margin-left:0;"></div>
              <p class="mb-4" style="white-space:pre-line;"><?= htmlspecialchars($aboutParagraph) ?></p>
              <a href="views/about.php" class="lp-about-learn-more">
                Learn More <i class="fa-solid fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="lp-about-img-frame h-100">
            <img src="<?= htmlspecialchars($aboutImageUrl) ?>" alt="<?= htmlspecialchars($schoolName) ?>" class="lp-about-img">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================= -->
  <!-- 4. WHY CHOOSE US: dynamic list, one at a time, text left / image
       right, fading/sliding transition between items -->
  <!-- ============================================================= -->
  <?php if ($whyChooseUs): ?>
  <section id="why-us" class="lp-features lp-reveal">
    <div class="container">
      <h2 class="lp-section-title">Why Choose Us</h2>
      <div class="lp-divider"></div>

      <div class="lp-why-rotator" id="whyRotator">
        <?php foreach ($whyChooseUs as $i => $item): ?>
          <div class="lp-why-slide <?= $i === 0 ? 'lp-why-active' : '' ?>">
            <div class="row g-4 align-items-center">
              <div class="col-md-6">
                <h4><?= htmlspecialchars($item['title']) ?></h4>
                <p style="color:var(--muted);"><?= htmlspecialchars($item['text']) ?></p>
              </div>
              <div class="col-md-6">
                <div class="lp-why-img-frame">
                  <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="lp-why-img">
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($whyChooseUs) > 1): ?>
        <div class="lp-why-dots" id="whyDots">
          <?php foreach ($whyChooseUs as $i => $item): ?>
            <span class="<?= $i === 0 ? 'lp-why-dot-active' : '' ?>"></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <script>
    (function () {
      const slides = document.querySelectorAll('#whyRotator .lp-why-slide');
      const dots = document.querySelectorAll('#whyDots span');
      if (slides.length <= 1) return;

      let current = 0;
      setInterval(() => {
        const next = (current + 1) % slides.length;

        slides[current].classList.remove('lp-why-active');
        slides[current].classList.add('lp-why-leaving');
        slides[next].classList.add('lp-why-active');

        if (dots.length) {
          dots[current].classList.remove('lp-why-dot-active');
          dots[next].classList.add('lp-why-dot-active');
        }

        setTimeout(() => {
          slides[current].classList.remove('lp-why-leaving');
        }, 500);

        current = next;
      }, 5000);
    })();
  </script>
  <?php endif; ?>

  <!-- ============================================================= -->
  <!-- 5. LEADERSHIP MESSAGE (only shows if admin set a name) -->
  <!-- ============================================================= -->
  <?php if ($htName): ?>
  <section class="lp-about lp-reveal">
    <div class="container">
      <h2 class="lp-section-title text-center">A Message From Our Head Teacher</h2>
      <div class="lp-divider"></div>
      <div class="row justify-content-center align-items-center g-4 mt-2">
        <div class="col-md-3 text-center">
          <img src="<?= htmlspecialchars($htPhotoUrl) ?>" alt="<?= htmlspecialchars($htName) ?>"
               class="rounded-circle lp-ht-photo">
        </div>
        <div class="col-md-7">
          <p class="lp-ht-quote">"<?= htmlspecialchars($htMessage) ?>"</p>
          <p class="lp-eyebrow mb-0">— <?= htmlspecialchars($htName) ?>, Head Teacher</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================= -->
  <!-- 6. ANNOUNCEMENTS: one at a time, LEFT-ALIGNED, vertical transition -->
  <!-- ============================================================= -->
  <?php if ($latestAnnouncements): ?>
  <section class="lp-ann-section lp-reveal">
    <div class="container">
      <h2 class="lp-section-title">Latest Announcements</h2>
      <div class="lp-divider"></div>

      <div class="lp-ann-rotator" id="annRotator">
        <?php foreach ($latestAnnouncements as $i => $a): ?>
          <div class="lp-ann-slide <?= $i === 0 ? 'lp-ann-active' : '' ?>">
            <p class="lp-eyebrow mb-1"><?= htmlspecialchars(date('d M Y', strtotime($a['created_at']))) ?></p>
            <h5><?= htmlspecialchars($a['title']) ?></h5>
            <p class="mb-0" style="color:var(--muted);">
              <?= htmlspecialchars(mb_strimwidth($a['body'], 0, 160, '…')) ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($latestAnnouncements) > 1): ?>
        <div class="lp-ann-dots" id="annDots">
          <?php foreach ($latestAnnouncements as $i => $a): ?>
            <span class="<?= $i === 0 ? 'lp-ann-dot-active' : '' ?>"></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="mt-4">
        <a href="views/view-announcements.php" class="lp-btn-solid" style="display:inline-block;">View all announcements</a>
      </div>
    </div>
  </section>
  <script>
    (function () {
      const slides = document.querySelectorAll('#annRotator .lp-ann-slide');
      const dots = document.querySelectorAll('#annDots span');
      if (slides.length <= 1) return;

      let current = 0;
      setInterval(() => {
        const next = (current + 1) % slides.length;

        slides[current].classList.remove('lp-ann-active');
        slides[current].classList.add('lp-ann-leaving');
        slides[next].classList.add('lp-ann-active');

        if (dots.length) {
          dots[current].classList.remove('lp-ann-dot-active');
          dots[next].classList.add('lp-ann-dot-active');
        }

        setTimeout(() => {
          slides[current].classList.remove('lp-ann-leaving');
        }, 500);

        current = next;
      }, 4500);
    })();
  </script>
  <?php endif; ?>

<?php include __DIR__ . '/views/general-footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
  // Count-up animation for the stats strip, triggered when it scrolls into view
  (function () {
    const nums = document.querySelectorAll('.lp-stat-num');
    if (!nums.length) return;

    const animate = (el) => {
      const target = parseInt(el.dataset.count, 10) || 0;
      const duration = 1200;
      const start = performance.now();
      const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        el.textContent = Math.floor(progress * target);
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target;
      };
      requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animate(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    nums.forEach(el => observer.observe(el));
  })();
</script>

<script>
  // Scroll-reveal: sections fade + slide up into view as the user scrolls,
  // instead of just appearing rigidly. Skips animation entirely for anyone
  // with reduced-motion preferences (handled in CSS too, this is a fallback).
  (function () {
    const revealEls = document.querySelectorAll('.lp-reveal');
    if (!revealEls.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('lp-reveal-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    revealEls.forEach((el) => observer.observe(el));
  })();
</script>
</body>
</html>