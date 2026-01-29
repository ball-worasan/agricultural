(function () {
  "use strict";
  const App = (window.App = window.App || {});

  App.initNavbars = function initNavbars() {
    // 1. จัดการ Search (เหมือนเดิม)
    const searchInput = document.getElementById("globalSearch");
    if (searchInput) {
      let searchTimeout = null;
      const emitSearchEvent = (value) => {
        window.dispatchEvent(
          new CustomEvent("global:search-change", { detail: { value } })
        );
      };
      searchInput.addEventListener("input", (e) => {
        const value = (e.target.value || "").trim();
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => emitSearchEvent(value), 300);
      });
      searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          emitSearchEvent(e.target.value.trim());
        }
      });
    }

    // 2. จัดการ Dropdown Menu (เพิ่มใหม่)
    const dropdownTrigger = document.getElementById("userDropdownTrigger");
    const dropdownMenu = document.getElementById("userDropdownMenu");

    if (dropdownTrigger && dropdownMenu) {
      // Toggle function
      const toggleMenu = (forceState = null) => {
        const isExpanded = dropdownTrigger.getAttribute("aria-expanded") === "true";
        const newState = forceState !== null ? forceState : !isExpanded;

        dropdownTrigger.setAttribute("aria-expanded", newState);

        if (newState) {
          dropdownMenu.classList.add("show");
        } else {
          dropdownMenu.classList.remove("show");
        }
      };

      // Click trigger
      dropdownTrigger.addEventListener("click", (e) => {
        e.stopPropagation(); // ป้องกัน event bubbling ไปถึง document
        toggleMenu();
      });

      // Close when clicking outside
      document.addEventListener("click", (e) => {
        if (!dropdownMenu.contains(e.target) && !dropdownTrigger.contains(e.target)) {
          toggleMenu(false);
        }
      });

      // Close on Escape key
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          toggleMenu(false);
        }
      });
    }
  };
})();