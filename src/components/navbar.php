<nav class="navbar" role="navigation" aria-label="แถบนำทางหลัก">
  <div class="nav-inner">

    <!-- เมนูทางซ้าย -->
    <div class="nav-left">
      <a href="?page=home" class="brand" aria-label="ไปหน้าหลัก">
        <?= e(APP_NAME) ?> · พื้นที่การเกษตรให้เช่า
      </a>
    </div>

    <!-- ค้นหาตรงกลาง -->
    <div class="nav-center">
      <?php
      // แสดงช่องค้นหาเฉพาะตอนอยู่ที่หน้า Home เท่านั้น
      if ($onHome):
      ?>
        <input
          type="search"
          id="globalSearch"
          class="nav-search"
          placeholder="ค้นหาแปลงเกษตร..."
          aria-label="ค้นหารายการพื้นที่เกษตร"
          autocomplete="off" />
      <?php endif; ?>
    </div>

    <!-- เมนูทางขวา -->
    <div class="nav-right">
      <div class="nav-account">

        <?php if ($user['is_logged_in']): ?>
          <!-- ถ้า login แล้ว -->
          <div class="dropdown">

            <!-- แสดงข้อมูลผู้ใช้ และ ปุ่ม dropdown -->
            <button class="dropdown-toggle" id="userDropdownTrigger" aria-expanded="false">
              <div class="user-text">
                <small><?= e(ucfirst($user['role'])) ?></small>
                <span><?= e($user['name']) ?></span>
              </div>
              <span class="arrow">▼</span>
            </button>

            <!-- แสดงคำทักทาย -->
            <div class="dropdown-menu" id="userDropdownMenu">
              <div class="dropdown-header">
                สวัสดี, <strong><?= e($user['name']) ?></strong>
              </div>

              <!-- ขีด -->
              <div class="dropdown-divider"></div>

              <!-- ลิงก์เมนูต่างๆ อ้างอิงจากไฟล์ (bootstrap.php) -->
              <?php foreach ($page_config as $key => $conf): ?>
                <?php
                $showInNav = $conf['nav'] ?? false;
                $hasRole   = in_array($user['role'], $conf['roles'] ?? []);

                if ($showInNav && $hasRole):
                ?>
                  <a href="?page=<?= e($key) ?>" class="dropdown-item <?= $page === $key ? 'active' : '' ?>">
                    <?= e($conf['title']) ?>
                  </a>
                <?php endif; ?>
              <?php endforeach; ?>

              <!-- ขีด -->
              <div class="dropdown-divider"></div>

              <!-- ปุ่มออกจากระบบ -->
              <a href="?action=logout" class="dropdown-item text-danger">
                ออกจากระบบ
              </a>
            </div>
          </div>

        <?php else: ?>
          <!-- ถ้าไม่ login -->
          <div class="auth-buttons">
            <a href="?page=login" class="btn-login">เข้าสู่ระบบ</a>
            <a href="?page=register" class="btn-register">สมัครสมาชิก</a>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</nav>