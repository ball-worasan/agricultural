<?php

declare(strict_types=1);

// ถ้าเข้าสู่ระบบแล้ว ให้รีไดเรกต์ไปหน้าโฮม
if ($user['is_logged_in']) {
  header('Location: ?page=home');
  exit;
}

// -----------------------------------------------------------------------------
// Signup Logic
// -----------------------------------------------------------------------------
$errorMsg = '';
$oldInput = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // รับค่าและตัดช่องว่าง
  $fullName = trim($_POST['full_name'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $phoneRaw = trim($_POST['phone'] ?? '');
  $address  = trim($_POST['address'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm  = $_POST['password_confirm'] ?? '';

  // กรองเอาแต่ตัวเลขจากเบอร์โทรศัพท์
  $phone = preg_replace('/\D/', '', $phoneRaw); // เอาเฉพาะตัวเลข

  // ตรวจสอบข้อมูลเบื้องต้น
  $errors = [];
  if (empty($fullName)) $errors[] = "กรุณากรอกชื่อ-นามสกุล";
  if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
  if (empty($phone)) $errors[] = "กรุณากรอกเบอร์โทรศัพท์";
  if ($password !== $confirm) $errors[] = "รหัสผ่านไม่ตรงกัน";
  if (strlen($password) < 6) $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";

  if (!empty($errors)) {
    $errorMsg = implode('<br>', $errors);
    $oldInput = $_POST;
  } else {
    try {
      // ตรวจสอบว่าชื่อผู้ใช้หรือเบอร์โทรศัพท์ซ้ำหรือไม่
      $check = Database::fetchOne("SELECT user_id FROM users WHERE username = ? OR phone = ? LIMIT 1", [$username, $phone]);

      if ($check) {
        $errorMsg = "ชื่อผู้ใช้หรือเบอร์โทรศัพท์นี้ถูกใช้งานแล้ว";
        $oldInput = $_POST;
      } else {
        // เพิ่มผู้ใช้ใหม่
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::execute(
          "INSERT INTO users (username, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)",
          [$username, $hash, $fullName, $phone, $address, 2] // 2 = member | 1 = admin
        );

        // สร้าง session อัตโนมัติหลังสมัคร
        header('Location: ?page=login');
        exit;
      }
    } catch (Exception $e) {
      $errorMsg = "เกิดข้อผิดพลาดระบบ";
      $oldInput = $_POST;
    }
  }
}
?>

<div class="auth-container" data-page="register">
  <div class="auth-wrapper">

    <!-- หน้าต้อนรับ ซ้าย-->
    <div class="auth-hero">
      <div class="hero-content">
        <h2>สมัครสมาชิก</h2>
        <p>เริ่มต้นเป็นส่วนหนึ่งของชุมชนพื้นที่เกษตรคุณภาพ</p>
        <div class="hero-features">
          <div class="feature-item">✓ ลงประกาศเช่าพื้นที่ฟรี</div>
          <div class="feature-item">✓ ค้นหาทำเลที่ใช่ได้ง่ายๆ</div>
          <div class="feature-item">✓ ระบบจัดการที่ทันสมัย</div>
        </div>
      </div>
    </div>

    <!-- ฟอร์มสมัครสมาชิก ขวา -->
    <div class="auth-form-wrapper">
      <div class="auth-content">
        <div class="auth-header">
          <h1>สร้างบัญชีใหม่</h1>
          <p>กรอกข้อมูลด้านล่างเพื่อเริ่มต้นใช้งาน</p>
        </div>

        <!-- ข้อความแสดงข้อผิดพลาด -->
        <?php if (!empty($errorMsg)): ?>
          <div class="alert alert-danger mb-3"><?= $errorMsg ?></div>
        <?php endif; ?>

        <!-- ฟอร์มสมัครสมาชิก -->
        <form action="?page=register" method="POST" class="auth-form" novalidate>
          <div class="form-group">
            <label for="full_name">ชื่อ-นามสกุล</label>
            <input type="text" id="full_name" name="full_name" class="form-control" placeholder="เช่น สมชาย ใจดี" required value="<?= e($oldInput['full_name'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="username">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="อังกฤษ/ตัวเลข" required value="<?= e($oldInput['username'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="phone">เบอร์โทรศัพท์</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="081xxxxxxx" required value="<?= e($oldInput['phone'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="address">ที่อยู่</label>
            <input type="text" id="address" name="address" class="form-control" placeholder="ที่อยู่ปัจจุบัน" required value="<?= e($oldInput['address'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <div class="password-input-wrapper">
              <input type="password" id="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" required>

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

          <div class="form-group mb-4">
            <label for="password_confirm">ยืนยันรหัสผ่าน</label>
            <div class="password-input-wrapper">
              <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="กรอกอีกครั้ง" required>

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

          <button type="submit" class="btn btn-primary w-100 py-2">ลงทะเบียน</button>
        </form>

        <div class="auth-footer mt-4">
          <p>มีบัญชีอยู่แล้ว? <a href="?page=login">เข้าสู่ระบบ</a></p>
        </div>
      </div>
    </div>
  </div>
</div>v