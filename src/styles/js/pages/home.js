(function () {
  "use strict";

  const App = (window.App = window.App || {});

  App.initHomeFilters = function initHomeFilters() {
    const pageRoot = document.querySelector('[data-page="home"]');
    if (!pageRoot) return;

    const filterIds = ['province', 'district', 'price', 'sort'];

    // ฟังก์ชันจัดการ URL Parameter และ Reload หน้า
    const updateParams = (key, value) => {
      const url = new URL(window.location.href);
      const params = url.searchParams;

      // เคลียร์อำเภอถ้าเปลี่ยนจังหวัด
      if (key === 'province') {
        params.delete('district');
      }

      if (value) {
        params.set(key, value);
      } else {
        params.delete(key);
      }

      // รีเซ็ตหน้าเป็น 1
      params.set('pg', 1);

      // Reload
      window.location.href = url.toString();
    };

    // Events สำหรับ Dropdown
    filterIds.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('change', (e) => {
          updateParams(id, e.target.value);
        });
      }
    });

    // Global Search Event
    window.addEventListener("global:search-change", (event) => {
      const val = event.detail?.value || "";
      updateParams('q', val.trim());
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", App.initHomeFilters);
  } else {
    App.initHomeFilters();
  }
})();