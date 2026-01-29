<?php

declare(strict_types=1);

// รับค่า ID จากพารามิเตอร์ GET
$id = (int)($_GET['id'] ?? 0);

// ถ้า ID ไม่ถูกต้อง ให้รีไดเรกต์กลับไปหน้าโฮม
if ($id <= 0) {
  header('Location: ?page=home');
  exit;
}

try {
  // ดึงข้อมูลพื้นที่
  $sql = "SELECT ra.*, d.district_name, p.province_name, u.phone as owner_phone, u.full_name as owner_name
          FROM rental_area ra
          JOIN district d ON ra.district_id = d.district_id
          JOIN province p ON d.province_id = p.province_id
          LEFT JOIN users u ON ra.user_id = u.user_id
          WHERE ra.area_id = :id LIMIT 1";

  // ดึงข้อมูลพื้นที่
  $item = Database::fetchOne($sql, [':id' => $id]);

  // ถ้าไม่พบข้อมูล ให้รีไดเรกต์กลับไปหน้าโฮม
  if (!$item) {
    header('Location: ?page=home');
    exit;
  }

  // ดึงรูปภาพ
  $images = Database::fetchAll("SELECT image_url FROM area_image WHERE area_id = :id ORDER BY image_id ASC", [':id' => $id]);
  $imageUrls = array_column($images, 'image_url');

  // ถ้าไม่มีรูป ให้ใช้รูปสำรอง
  if (empty($imageUrls)) {
    $imageUrls[] = 'https://placehold.co/600x400?text=No+Image';
  }
} catch (Exception $e) {
  app_log('detail_error', ['message' => $e->getMessage()]);
  header('Location: ?page=home');
  exit;
}

// แปรงค่าสถานะพื้นที่
$statusMap = [
  'available' => ['text' => 'ว่างให้เช่า', 'class' => 'status-available'],
  'booked'    => ['text' => 'ติดจอง',     'class' => 'status-booked'],
  'sold'      => ['text' => 'เช่าแล้ว',   'class' => 'status-sold'],
];
$statusKey = $item['area_status'] ?? 'available';
$statusInfo = $statusMap[$statusKey] ?? $statusMap['available'];

// กำหนดตัวแปรสำหรับแสดงผล
$price      = number_format((float)$item['price_per_year']);
$size       = number_format((float)$item['area_size'], 2);
$location   = e($item['district_name']) . ', ' . e($item['province_name']);
$created    = date('d/m/Y', strtotime($item['created_at']));
$desc       = nl2br(e($item['description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม'));
$jsonImages = json_encode($imageUrls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

?>

<div class="detail-container"
  data-page="detail"
  data-id="<?= $id ?>"
  data-images='<?= $jsonImages ?>'>

  <div class="detail-wrapper">
    <div class="detail-header">
      <a href="?page=home" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
        ย้อนกลับ
      </a>
      <div class="header-meta">
        <span class="meta-date">ลงประกาศ: <?= $created ?></span>
        <span class="status-badge <?= $statusInfo['class'] ?>"><?= $statusInfo['text'] ?></span>
      </div>
    </div>

    <div class="detail-grid">

      <!-- รูปภาพ -->
      <div class="detail-gallery">
        <div class="main-image-frame">
          <img id="mainImage" src="<?= e($imageUrls[0]) ?>" alt="Main Property Image">
          <?php if (count($imageUrls) > 1): ?>
            <button class="nav-btn prev">❮</button>
            <button class="nav-btn next">❯</button>
            <div class="image-counter">1 / <?= count($imageUrls) ?></div>
          <?php endif; ?>
        </div>

        <!-- ตัวอย่างรูปภาพ -->
        <?php if (count($imageUrls) > 1): ?>
          <div class="thumb-list">
            <?php foreach ($imageUrls as $idx => $url): ?>
              <img src="<?= e($url) ?>" class="thumb-item <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ข้อมูลรายละเอียด -->
      <div class="detail-info">
        <h1 class="property-title"><?= e($item['area_name']) ?></h1>
        <p class="property-location">📍 <?= $location ?></p>

        <!-- ข้อมูลราคา -->
        <div class="price-card">
          <div class="price-row">
            <span class="label">ราคาเช่าต่อปี</span>
            <span class="value"><?= $price ?> บาท</span>
          </div>

          <!-- ขนาดพื้นที่ -->
          <div class="price-sub">
            ขนาดพื้นที่: <strong><?= $size ?> ไร่</strong>
          </div>
        </div>

        <div class="info-section">
          <h3>รายละเอียด</h3>
          <div class="desc-text"><?= $desc ?></div>
        </div>

        <div class="info-section">
          <h3>ข้อมูลผู้ปล่อยเช่า</h3>
          <div class="owner-card">
            <div class="owner-avatar"><?= mb_substr($item['owner_name'], 0, 1) ?></div>
            <div class="owner-details">

              <!-- ชื่อผู้ปล่อยเช่า -->
              <strong><?= e($item['owner_name']) ?></strong>
              <!-- ถ้าผู้ใช้เป็น admin หรือเจ้าของพื้นที่ จะแสดงเบอร์โทรศัพท์แบบเต็ม -->
              <span><?= ($user['role'] == "member" || $id == $user['id']) ? e($item['owner_phone']) : substr($item['owner_phone'], 0, 3) . '-xxx-xxxx' ?></span>
            </div>
          </div>
        </div>

        <div class="action-area">
          <!-- ปุ่มสำหรับเจ้าของพื้นที่ -->
          <?php if ($id == $user['id'] || $user['role'] == "admin"): ?>
            <a href="?page=edit&id=<?= $id ?>" class="btn btn-outline w-100">แก้ไขข้อมูล</a>

            <!-- ถ้าพื้นที่ไม่ว่างให้เช่า -->
          <?php elseif ($statusKey !== 'available'): ?>
            <button class="btn btn-disabled w-100" disabled>ไม่ว่างให้เช่า</button>

            <!-- ถ้าผู้ใช้ล็อกอินแล้ว -->
          <?php elseif ($user['id'] > 0 && $user['role'] == "member"): ?>
            <button id="btnShowBooking" class="btn btn-primary w-100">สนใจจองพื้นที่นี้</button>

            <!-- ถ้าผู้ใช้ยังไม่ล็อกอิน -->
          <?php else: ?>
            <a href="?page=login" class="btn btn-primary w-100">เข้าสู่ระบบเพื่อจอง</a>
          <?php endif; ?>
        </div>

        <!-- ฟอร์มจอง แสดงเมื่อคลิกปุ่ม -->
        <div id="bookingForm" class="booking-form" style="display: none;">
          <h3>📅 เลือกวันนัดดูพื้นที่/ทำสัญญา</h3>
          <form id="formBook" method="POST" action="api/booking.php">
            <input type="hidden" name="area_id" value="<?= $id ?>">
            <div class="form-group mb-3">
              <label>วันที่ต้องการนัด</label>
              <input type="date" name="booking_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>
            <div class="form-actions">
              <button type="button" id="btnCancelBooking" class="btn btn-outline">ยกเลิก</button>
              <button type="submit" class="btn btn-primary">ยืนยันการจอง</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>