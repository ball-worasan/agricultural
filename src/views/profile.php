<?

declare(strict_types=1);

// ดึงข้อมูลผู้ใช้จาก session
$userId = $user['is_logged_in'] ? (int)$user['id'] : 0;
if ($userId <= 0) {
  header('Location: ?page=login');
  exit;
}

// avatar
$avatarName = trim((string)($user['username'] ?? ''));
if ($avatarName === '') $avatarName = (string)($user['username'] ?? 'User');

$profileImageUrl = 'https://ui-avatars.com/api/?name=' .
  urlencode($avatarName) .
  '&size=200&background=1e40af&color=fff';
?>

<div class="profile-container" data-page="profile">
  <div class="profile-wrapper">
    <div class="profile-header">
      <h1>โปรไฟล์</h1>
      <p>จัดการข้อมูลส่วนตัวของคุณ</p>
    </div>

    <div class="profile-content">
      <div class="profile-picture-section">
        <div class="profile-picture">
          <img src="<?= e($profileImageUrl); ?>" alt="รูปโปรไฟล์" id="profileImage">
        </div>
        <h2 class="profile-name"><?= e((string)($user['username'] ?? '')); ?></h2>
        <p class="profile-role"><?= e($user['role'] ?? ''); ?></p>
      </div>

      <div class="profile-info-section">
        <div class="section-card">
          <h3>ข้อมูลส่วนตัว</h3>

          <!-- VIEW MODE -->
          <div id="profileView" class="profile-view-mode">
            <div class="info-grid">
              <div class="info-item">
                <label>ชื่อ-นามสกุล</label>
                <p><?= e((string)($user['name'] ?? '')); ?></p>
              </div>
              <div class="info-item">
                <label>เบอร์โทรศัพท์</label>
                <p><?= e((string)($user['phone'] ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
              <div class="info-item">
                <label>ที่อยู่</label>
                <p><?= e((string)($user['address'] ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
              <div class="info-item">
                <label>ชื่อผู้ใช้</label>
                <p><?= e((string)($user['username'] ?? '')); ?></p>
              </div>
              <div class="info-item">
                <label>สมาชิกตั้งแต่</label>
                <p><?= e(($createdAtText ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
            </div>

            <h4 style="margin-top: 2rem; margin-bottom: 1rem; color: var(--text-primary);">ข้อมูลบัญชีธนาคาร</h4>
            <div class="info-grid">
              <div class="info-item">
                <label>เลขบัญชี/พร้อมเพย์</label>
                <p><?= e((string)($user['account_number'] ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
              <div class="info-item">
                <label>ชื่อธนาคาร</label>
                <p><?= e((string)($user['bank_name'] ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
              <div class="info-item">
                <label>ชื่อบัญชี</label>
                <p><?= e((string)($user['account_name'] ?? 'ไม่ได้ระบุ')); ?></p>
              </div>
            </div>

            <button type="button" class="btn-edit" id="editProfileBtn" aria-label="แก้ไขข้อมูล">แก้ไขข้อมูล</button>
          </div>

          <!-- EDIT MODE -->
          <form method="POST" id="profileForm" class="profile-edit-form hidden" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($csrf); ?>">
            <input type="hidden" name="update_profile" value="1">

            <div class="info-grid">
              <div class="info-item">
                <label>ชื่อ-นามสกุล</label>
                <input type="text" name="full_name" value="<?= e((string)($user['full_name'] ?? '')); ?>" required class="edit-input">
              </div>

              <div class="info-item">
                <label>เบอร์โทรศัพท์</label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  value="<?= e((string)($user['phone'] ?? '')); ?>"
                  class="edit-input"
                  inputmode="numeric"
                  pattern="[0-9]{9,10}"
                  maxlength="10"
                  title="กรุณากรอกเบอร์โทรศัพท์ 9-10 หลัก">
              </div>

              <div class="info-item">
                <label>ที่อยู่</label>
                <textarea name="address" class="edit-input" rows="3"><?= e((string)($user['address'] ?? '')); ?></textarea>
              </div>

              <div class="info-item">
                <label>ชื่อผู้ใช้</label>
                <p><?= e((string)($user['username'] ?? '')); ?> <small>(ไม่สามารถเปลี่ยนได้)</small></p>
              </div>
            </div>

            <h4 style="margin-top: 2rem; margin-bottom: 1rem; color: var(--text-primary);">ข้อมูลบัญชีธนาคาร</h4>
            <div class="info-grid">
              <div class="info-item">
                <label>เลขบัญชี/พร้อมเพย์</label>
                <input type="text" name="account_number" value="<?= e((string)($user['account_number'] ?? '')); ?>" class="edit-input" placeholder="เช่น 0641365430 หรือ 123-4-56789-0">
                <small style="color: var(--text-secondary);">ระบุเลขบัญชีธนาคารหรือพร้อมเพย์</small>
              </div>

              <div class="info-item">
                <label>ชื่อธนาคาร</label>
                <input type="text" name="bank_name" value="<?= e((string)($user['bank_name'] ?? '')); ?>" class="edit-input" placeholder="เช่น ธนาคารกสิกรไทย">
              </div>

              <div class="info-item">
                <label>ชื่อบัญชี</label>
                <input type="text" name="account_name" value="<?= e((string)($user['account_name'] ?? '')); ?>" class="edit-input" placeholder="เช่น นายสมชาย ใจดี">
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-save">บันทึกการเปลี่ยนแปลง</button>
              <button type="button" class="btn-cancel" id="cancelEditBtn">ยกเลิก</button>
            </div>
          </form>
        </div>

        <div class="section-card">
          <h3>เปลี่ยนรหัสผ่าน</h3>

          <form method="POST" class="password-form" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($csrf); ?>">
            <input type="hidden" name="change_password" value="1">

            <div class="form-group">
              <label for="current_password">รหัสผ่านเดิม</label>
              <div class="password-input-wrapper">
                <input type="password" id="current_password" name="current_password" placeholder="กรอกรหัสผ่านเดิม" required autocomplete="current-password">
                <button type="button" class="toggle-password" data-target="current_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                  <span class="eye-icon">👁️</span>
                  <span class="eye-off-icon" style="display:none;">🙈</span>
                </button>
              </div>
            </div>

            <div class="password-row">
              <div class="form-group">
                <label for="new_password">รหัสผ่านใหม่</label>
                <div class="password-input-wrapper">
                  <input type="password" id="new_password" name="new_password" placeholder="กรอกรหัสผ่านใหม่" required minlength="8" autocomplete="new-password">
                  <button type="button" class="toggle-password" data-target="new_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                    <span class="eye-icon">👁️</span>
                    <span class="eye-off-icon" style="display:none;">🙈</span>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label for="confirm_new_password">ยืนยันรหัสผ่านใหม่</label>
                <div class="password-input-wrapper">
                  <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="ยืนยันรหัสผ่านใหม่" required minlength="8" autocomplete="new-password">
                  <button type="button" class="toggle-password" data-target="confirm_new_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                    <span class="eye-icon">👁️</span>
                    <span class="eye-off-icon" style="display:none;">🙈</span>
                  </button>
                </div>
              </div>
            </div>

            <button type="submit" class="btn-change-password">เปลี่ยนรหัสผ่าน</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>