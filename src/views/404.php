<div class="error-page-container">
  <div class="error-content">

    <div class="error-illustration">
      <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="90" fill="#f0f4ff" opacity="0.5" />

        <circle cx="70" cy="85" r="8" fill="#2563eb" opacity="0.6" />
        <circle cx="130" cy="85" r="8" fill="#2563eb" opacity="0.6" />
        <path d="M 70 130 Q 100 115 130 130" stroke="#2563eb" stroke-width="6" fill="none" stroke-linecap="round" opacity="0.6" />

        <text x="100" y="60" font-size="40" font-weight="bold" text-anchor="middle" fill="#2563eb" opacity="0.3">?</text>
      </svg>
    </div>

    <h1 class="error-code">404</h1>

    <h2 class="error-title">ไม่พบหน้าที่คุณต้องการ</h2>
    <p class="error-description">
      ขออภัย หน้าที่คุณกำลังมองหาอาจถูกย้าย ลบออก หรือไม่เคยมีอยู่จริง
    </p>

    <div class="error-actions">
      <a href="?page=home" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        กลับหน้าหลัก
      </a>

      <button onclick="history.back()" class="btn btn-outline">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="19" y1="12" x2="5" y2="12" />
          <polyline points="12 19 5 12 12 5" />
        </svg>
        ย้อนกลับ
      </button>
    </div>

    <div class="error-links">
      <span class="links-label">หน้าที่คุณอาจสนใจ:</span>
      <div class="links-list">
        <a href="?page=home" class="link-item">หน้าหลัก</a>
        <span class="link-separator">•</span>
        <a href="?page=my-rental" class="link-item">พื้นที่ของฉัน</a>
        <span class="link-separator">•</span>
        <a href="?page=login" class="link-item">เข้าสู่ระบบ</a>
      </div>
    </div>

  </div>
</div>