<?php

declare(strict_types=1);

// ถ้าเข้าสู่ระบบแล้ว ให้รีไดเรกต์ไปหน้าโฮม
if ($user['is_logged_in']) {
  header('Location: ?page=home');
  exit;
}

// -----------------------------------------------------------------------------
// Rate Limiter
// -----------------------------------------------------------------------------
// จำกัดการพยายามเข้าสู่ระบบไม่เกิน 5 ครั้งใน 1 นาที
if (!class_exists('SigninRateLimiter')) {
  class SigninRateLimiter
  {
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 60;

    // ตรวจสอบว่าผู้ใช้สามารถพยายามเข้าสู่ระบบได้หรือไม่
    public function check(string $ip): bool
    {
      if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
      $now = time();
      $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], fn($t) => ($now - $t) < self::WINDOW_SECONDS);
      return count($_SESSION['login_attempts']) < self::MAX_ATTEMPTS;
    }

    // บันทึกการพยายามเข้าสู่ระบบ
    public function addAttempt(string $ip): void
    {
      $_SESSION['login_attempts'][] = time();
    }
  }
}

// -----------------------------------------------------------------------------
// Login Logic
// -----------------------------------------------------------------------------
$error = '';
$usernameInput = '';

// ประมวลผลเมื่อฟอร์มถูกส่งมา
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usernameInput = trim($_POST['username'] ?? '');
  $passwordInput = $_POST['password'] ?? '';
  $limiter = new SigninRateLimiter();

  // ตรวจสอบการพยายามเข้าสู่ระบบ
  if (!$limiter->check($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
    $error = "พยายามเข้าระบบมากเกินไป กรุณารอ 1 นาที";
  } elseif (empty($usernameInput) || empty($passwordInput)) {
    $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
  } else {
    try {
      // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
      $userData = Database::fetchOne(
        "SELECT user_id, username, password, full_name, role FROM users WHERE username = :username LIMIT 1",
        [':username' => $usernameInput]
      );

      // ตรวจสอบรหัสผ่าน
      if ($userData && password_verify($passwordInput, $userData['password'])) {
        // ถ้ารหัสผ่านถูกต้อง สร้าง session ผู้ใช้
        session_regenerate_id(true);
        $_SESSION['user_id']   = (int)$userData['user_id'];
        $_SESSION['username']  = $userData['username'];
        $_SESSION['user_name'] = $userData['full_name'];
        $_SESSION['user_role'] = ((int)$userData['role'] == 1) ? 'admin' : 'member';
        unset($_SESSION['login_attempts']);
        header('Location: ?page=home');
        exit;
      } else {
        // ถ้ารหัสผ่านไม่ถูกต้อง บันทึกการพยายามเข้าสู่ระบบ
        $limiter->addAttempt($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
      }
    } catch (Exception $e) {
      $error = "เกิดข้อผิดพลาดระบบ";
    }
  }
}
?>

<div class="auth-container" data-page="login">
  <div class="auth-wrapper">

    <!-- หน้าต้อนรับ ซ้าย-->
    <div class="auth-hero">
      <div class="hero-content">
        <h2>ยินดีต้อนรับ</h2>
        <p>เข้าสู่ระบบเพื่อจัดการพื้นที่เกษตรของคุณ</p>
        <div class="hero-features">
          <div class="feature-item">✓ เข้าสู่ระบบได้ง่าย</div>
          <div class="feature-item">✓ จัดการข้อมูลอย่างปลอดภัย</div>
          <div class="feature-item">✓ ดูและจัดการรายการพื้นที่ของคุณ</div>
        </div>
      </div>
    </div>

    <!-- ฟอร์มเข้าสู่ระบบ ขวา -->
    <div class="auth-form-wrapper">
      <div class="auth-content">
        <div class="auth-header">
          <h1>เข้าสู่ระบบ</h1>
          <p>ยินดีต้อนรับกลับสู่พื้นที่เกษตรของสิริณัฐ</p>
        </div>

        <!-- ข้อความแสดงข้อผิดพลาด -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger mb-4"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- ฟอร์มเข้าสู่ระบบ -->
        <form action="?page=login" method="POST" class="auth-form" novalidate>
          <div class="form-group mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้" required value="<?= e($usernameInput); ?>" autocomplete="username">
          </div>

          <div class="form-group mb-4">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <div class="password-input-wrapper">
              <input type="password" id="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">

              <button type="button" class="toggle-password" tabindex="-1" aria-label="แสดงรหัสผ่าน">
                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="icon-eye-off d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">เข้าสู่ระบบ</button>
        </form>

        <div class="auth-footer mt-4">
          <p class="text-secondary">ยังไม่มีบัญชี? <a href="?page=register" class="text-primary fw-bold">สมัครสมาชิก</a></p>
        </div>
      </div>
    </div>
  </div>
</div>