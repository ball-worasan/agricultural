<?php
// home.php

// ตรวจสอบ User ID
$userId = $user['is_logged_in'] ? ($user['id'] ?? 0) : null;

// ตั้งค่า Pagination
$currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
$limit = 5;
$offset = ($currentPage - 1) * $limit;

// เตรียม Query สำหรับ Filter
$whereClauses = ["a.area_status = 'available'"];
$params = [];

// -- Filter: จังหวัด 
if (!empty($_GET['province'])) {
  $whereClauses[] = "d.province_id = :province_id";
  $params[':province_id'] = $_GET['province'];
}

// -- Filter: อำเภอ
if (!empty($_GET['district'])) {
  $whereClauses[] = "a.district_id = :district_id";
  $params[':district_id'] = $_GET['district'];
}

// -- Filter: ราคา
if (!empty($_GET['price'])) {
  $ranges = explode('-', $_GET['price']);
  if (count($ranges) === 2) {
    $min = (int)$ranges[0];
    $max = (int)$ranges[1];
    $whereClauses[] = "a.price_per_year >= :min_price AND a.price_per_year <= :max_price";
    $params[':min_price'] = $min;
    $params[':max_price'] = $max;
  }
}

// -- Filter: Search Keyword
if (!empty($_GET['q'])) {
  $whereClauses[] = "(a.area_name LIKE :q OR p.province_name LIKE :q OR d.district_name LIKE :q)";
  $params[':q'] = "%" . $_GET['q'] . "%";
}

// สร้าง WHERE String
$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(' AND ', $whereClauses) : "";

// การเรียงลำดับ
$sortOption = $_GET['sort'] ?? '';
switch ($sortOption) {
  case 'price-low':
    $orderBy = "ORDER BY a.price_per_year ASC";
    break;
  case 'price-high':
    $orderBy = "ORDER BY a.price_per_year DESC";
    break;
  default:
    $orderBy = "ORDER BY a.created_at DESC";
    break;
}

// ดึงข้อมูล Items
$sqlItems = "
    SELECT 
        a.area_id,
        a.user_id,
        a.area_name,
        a.price_per_year,
        a.deposit_percent,
        a.area_status,
        a.created_at,
        p.province_name,
        d.district_name,
        d.district_id,
        (SELECT image_url FROM area_image WHERE area_id = a.area_id LIMIT 1) AS main_image
    FROM rental_area a
    LEFT JOIN district d ON a.district_id = d.district_id
    LEFT JOIN province p ON d.province_id = p.province_id
    $whereSql
    $orderBy
    LIMIT $limit OFFSET $offset
";

try {
  $items = Database::fetchAll($sqlItems, $params);
} catch (Exception $e) {
  $items = [];
  app_log("Error fetching items: " . $e->getMessage());
}

// นับจำนวนรายการทั้งหมด
$sqlCount = "
    SELECT COUNT(*) as total 
    FROM rental_area a
    LEFT JOIN district d ON a.district_id = d.district_id
    LEFT JOIN province p ON d.province_id = p.province_id
    $whereSql
";
$totalRow = Database::fetchOne($sqlCount, $params);
$totalItems = $totalRow['total'] ?? 0;
$totalPages = ceil($totalItems / $limit);

// ดึงข้อมูล Provinces และ Districts สำหรับ Dropdown
$sqlProvinces = "SELECT province_id, province_name FROM province ORDER BY province_name ASC";
$sqlDistricts = "SELECT district_id, province_id, district_name FROM district ORDER BY district_name ASC";

$provinces = Database::fetchAll($sqlProvinces);
$districts = Database::fetchAll($sqlDistricts);
?>

<div class="home-container" data-page="home">

  <!-- Filter Section -->
  <div class="filter-section">

    <!-- ค้นหาตามจังหวัดที่เลือก -->
    <div class="filter-left">
      <div class="filter-group">
        <label for="province">จังหวัด</label>
        <select id="province" name="province">
          <option value="">ทั้งหมด</option>
          <?php foreach ($provinces as $prov): ?>
            <option
              value="<?= e((string)$prov['province_id']); ?>"
              data-name="<?= e((string)$prov['province_name']); ?>"
              <?= (isset($_GET['province']) && $_GET['province'] == $prov['province_id']) ? 'selected' : ''; ?>>
              <?= e((string)$prov['province_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ค้นหาตามอำเภอที่เลือก -->
      <div class="filter-group">
        <label for="district">อำเภอ</label>
        <select id="district" name="district" <?= empty($_GET['province']) ? 'disabled' : ''; ?>>
          <option value="">ทั้งหมด</option>
          <?php foreach ($districts as $dist):
            $isHidden = !empty($_GET['province']) && $_GET['province'] != $dist['province_id'];
            if ($isHidden) continue;
          ?>
            <option
              value="<?= e((string)$dist['district_id']); ?>"
              data-province-id="<?= e((string)$dist['province_id']); ?>"
              <?= (isset($_GET['district']) && $_GET['district'] == $dist['district_id']) ? 'selected' : ''; ?>>
              <?= e((string)$dist['district_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ค้นหาตามราคา -->
      <div class="filter-group">
        <label for="price">ราคาเช่า (บาท/ปี)</label>
        <select id="price" name="price">
          <option value="">ทั้งหมด</option>
          <?php
          $priceOpts = [
            '0-10000' => '0 - 10,000',
            '10000-20000' => '10,000 - 20,000',
            '20000-30000' => '20,000 - 30,000',
            '30000-50000' => '30,000 - 50,000',
            '50000-100000' => '50,000 - 100,000',
            '100000-200000' => '100,000 - 200,000',
            '200000-500000' => '200,000 - 500,000',
            '500000-1000000' => '500,000 - 1,000,000'
          ];
          foreach ($priceOpts as $val => $label):
          ?>
            <option value="<?= $val ?>" <?= (isset($_GET['price']) && $_GET['price'] == $val) ? 'selected' : ''; ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- ตัวเลือกเรียงลำดับ -->
    <div class="filter-right">
      <div class="filter-group">
        <label for="sort">เรียงตาม</label>
        <select id="sort" name="sort">
          <option value="created-desc" <?= ($sortOption == 'created-desc' || $sortOption == '') ? 'selected' : ''; ?>>มาใหม่ล่าสุด</option>
          <option value="price-low" <?= ($sortOption == 'price-low') ? 'selected' : ''; ?>>ราคาต่ำ-สูง</option>
          <option value="price-high" <?= ($sortOption == 'price-high') ? 'selected' : ''; ?>>ราคาสูง-ต่ำ</option>
        </select>
      </div>
    </div>
  </div>

  <div class="items-section" id="itemsContainer">
    <?php if (empty($items)): ?>
      <!-- ไม่พบรายการที่ตรงกับเงื่อนไข -->
      <div id="homeEmptyState" class="empty-state">
        <div class="empty-state-icon" style="font-size: 4rem; opacity: 0.5;">🔎</div>
        <div class="empty-state-title" style="margin-top: 1rem; font-weight: bold;">ไม่พบรายการที่ตรงกับเงื่อนไข</div>
        <div class="empty-state-desc" style="color: #666;">ลองเปลี่ยนตัวกรอง หรือพิมพ์คำค้นใหม่อีกครั้ง</div>
      </div>

    <?php else: ?>
      <!-- รายการพื้นที่เกษตร -->
      <?php foreach ($items as $item):

        // ไอดีพื้นที่เกษตร
        $areaId = isset($item['area_id']) ? (int)$item['area_id'] : 0;
        if ($areaId <= 0) continue;

        // ราคาและสถานะ
        $priceRaw   = isset($item['price_per_year']) ? (float)$item['price_per_year'] : 0.0;
        $depositPct = isset($item['deposit_percent']) ? (float)$item['deposit_percent'] : 0.0;

        // สถานะการจอง
        $areaStatus = (string)($item['area_status'] ?? 'available');
        $isBooked = ($areaStatus === 'booked' || $areaStatus === 'unavailable');

        // ตรวจสอบเจ้าของพื้นที่
        $ownerId = isset($item['user_id']) ? (int)$item['user_id'] : null;
        $isOwner = ($userId !== null && $ownerId !== null && $ownerId === $userId);

        // กำหนดคลาสการ์ด
        $cardClass = $isBooked ? 'item-card booked' : 'item-card';

        // รูปภาพหลัก
        $mainImage = (string)($item['main_image'] ?? '');
        $svgPlaceholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23f0f0f0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-size="24"%3ENo Image%3C/text%3E%3C/svg%3E';

        // นำรูปภาพมาใช้
        if ($mainImage && !str_starts_with($mainImage, 'http') && !str_starts_with($mainImage, 'data:')) {
          $mainImage = '/uploads/' . $mainImage;
        }

        // ตรวจสอบรูปภาพ ถ้าไม่มีหรือเป็น placeholder ให้ใช้รูปภาพสำรอง
        if ($mainImage === '' || stripos($mainImage, 'placeholder') !== false) {
          $mainImage = $svgPlaceholder;
        }

        // วันที่สร้างประกาศ
        $createdAt = (string)($item['created_at'] ?? '');
        $displayDate = '-';
        if ($createdAt !== '') {
          $displayDate = date('d/m/Y', strtotime($createdAt));
        }

        // ชื่อพื้นที่และที่ตั้ง
        $province = (string)($item['province_name'] ?? '');
        $district = (string)($item['district_name'] ?? '');

        // ชื่อพื้นที่
        $titleText = (string)($item['area_name'] ?? '');
        $locationText = ($district !== '' || $province !== '')
          ? trim(($district !== '' ? $district : '') . ($province !== '' ? ', ' . $province : ''))
          : '';
      ?>
        <a
          href="<?= e('?page=detail&id=' . $areaId); ?>"
          class="<?= e($cardClass); ?>"
          style="text-decoration:none;color:inherit;">

          <div class="item-image-container">
            <div class="card-badges">
              <!-- ป้ายสถานะ -->
              <?php if ($isOwner): ?>
                <span class="badge" style="background:var(--primary-color);color:white;">ของคุณ</span>
              <?php endif; ?>
              <?php if ($isBooked): ?>
                <span class="badge" style="background:var(--warning-color);color:var(--text-black);">ไม่ว่าง</span>
              <?php else: ?>
                <span class="badge" style="background:var(--success-color);color:white;">ว่าง</span>
              <?php endif; ?>
            </div>

            <!-- รูปภาพ -->
            <img src="<?= e($mainImage); ?>" alt="<?= e($titleText); ?>" loading="lazy">
          </div>

          <div class="item-details">
            <!-- ตำแหน่ง -->
            <div class="details-top">
              <h3 class="item-title"><?= e($titleText); ?></h3>
              <p class="item-location">📍 <?= e($locationText); ?></p>
            </div>

            <div class="details-bottom">
              <!-- วันที่ลงประกาศ -->
              <div class="item-meta">
                <span class="meta-date">ลงประกาศ: <?= e($displayDate); ?></span>
              </div>

              <div class="item-separator">
                <span class="deposit-label">ค่ามัดจำ:</span>
                <span class="deposit-val"><?= number_format($priceRaw / $depositPct); ?> บาท</span>
              </div>

              <!-- ราคา -->
              <div class="item-price-tag">
                <span class="price-label">ราคาเช่าต่อปี</span>
                <span class="price-val"><?= number_format($priceRaw); ?> บาท</span>
              </div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- เส้นทางจำนวนหน้าเว็บ -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php $queryParams = $_GET; ?>

      <!-- ปุ่มก่อนหน้า -->
      <?php if ($currentPage > 1): ?>
        <?php $queryParams['pg'] = $currentPage - 1; ?>
        <a class="btn" href="?<?= http_build_query($queryParams); ?>">ก่อนหน้า</a>
      <?php endif; ?>

      <!-- จำนวนหน้าตอนนี้ -->
      <span class="page-info">
        หน้า <?= (int)$currentPage; ?> / <?= (int)$totalPages; ?>
      </span>

      <!-- ปุ่มถัดไป -->
      <?php if ($currentPage < $totalPages): ?>
        <?php $queryParams['pg'] = $currentPage + 1; ?>
        <a class="btn" href="?<?= http_build_query($queryParams); ?>">ถัดไป</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>