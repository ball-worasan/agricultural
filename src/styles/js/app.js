(function () {
  "use strict";
  const App = (window.App = window.App || {});

  function safeCall(fn) {
    if (typeof fn === "function") fn();
  }

  function init() {
    // Core Initializations
    safeCall(App.initNavbars);     // From app.navbar.js
    safeCall(App.initLazyLoading); // From app.core.js

    // Feature specific inits (ถ้ามีไฟล์อื่นโหลดมา)
    safeCall(App.initFlashPopups);
    safeCall(App.initHomeFilters);
    safeCall(App.initSigninPage);

    // Note: Removed redundant manual image loading block 
    // because App.initLazyLoading handles it better now.
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();