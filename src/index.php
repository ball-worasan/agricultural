<?php
ob_start();
require_once 'bootstrap.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <!-- ประกาศ meta พื้นฐาน -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- แสดง title ของเว็บตามหน้าเว็บ และ เอาชื่อเว็บมาตามต่อหลัง -->
  <title><?= e($title); ?> | <?= e(APP_NAME); ?></title>

  <!-- โหลด CSS พื้นฐาน -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/styles/css/variables.css">
  <link rel="stylesheet" href="/styles/css/base.css">
  <link rel="stylesheet" href="/styles/css/navbar.css">

  <!-- โหลด JS พื้นฐาน -->
  <script src="/styles/js/app.core.js" defer></script>
  <script src="/styles/js/app.navbar.js" defer></script>
  <script src="/styles/js/app.js" defer></script>

  <!-- โหลด CSS ของหน้าเว็บที่เปิดอยู่  -->
  <?php foreach ($pageCss as $css): ?>
    <link rel="stylesheet" href="<?= e((string)$css) ?>">
  <?php endforeach; ?>

  <!-- โหลด JS ของหน้าเว็บที่เปิดอยู่ -->
  <?php foreach ($pageJs as $js): ?>
    <script src="<?= e((string)$js) ?>" defer></script>
  <?php endforeach; ?>
</head>

<body>
  <header>
    <?php
    // กำหนดที่อยู่ของ Navbar สำหรับดึงมาแสดงผล
    $navbarPath = APP_PATH . '/components/navbar.php';
    if (file_exists($navbarPath)) {
      include $navbarPath;
    } else {
      // บันทึกลง Error Log ของระบบ
      app_log("Navbar missing at: $navbarPath");
    }
    ?>
  </header>

  <main>
    <?php
    $viewPath = APP_PATH . ($current_config['view'] ?? '');
    if (file_exists($viewPath) && !is_dir($viewPath)) {
      include $viewPath;
    } else {
      echo "<div class='alert alert-danger'>View file not found: " . e($current_config['view'] ?? 'Unknown') . "</div>";
    }
    ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>