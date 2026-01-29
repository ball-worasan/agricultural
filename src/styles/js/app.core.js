(function () {
  "use strict";

  const App = (window.App = window.App || {});

  // ============================================
  // Constants
  // ============================================
  App.constants = {
    TOAST_DURATION: 5000,
    ANIMATION_DURATION: 300,
  };

  // ============================================
  // Utility Functions
  // ============================================

  App.escapeHtml = function (text) {
    const div = document.createElement("div");
    div.textContent = String(text ?? "");
    return div.innerHTML;
  };

  App.debounce = function (func, wait = 300) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  };

  App.formatCurrency = function (amount) {
    return new Intl.NumberFormat("th-TH", {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    }).format(amount);
  };

  App.formatDate = function (date, format = "short") {
    const d = new Date(date);
    if (isNaN(d.getTime())) return "";
    return new Intl.DateTimeFormat("th-TH", {
      year: "numeric", month: format === "short" ? "short" : "long", day: "numeric"
    }).format(d);
  };

  // ============================================
  // Toast Notifications (Matches base.css .toast)
  // ============================================

  App.showToast = function (title, message, type = "success", duration = App.constants.TOAST_DURATION) {
    // Remove existing toast
    const existingToast = document.querySelector(".toast");
    if (existingToast) existingToast.remove();

    const toast = document.createElement("div");
    // Map type to CSS classes defined in base.css
    toast.className = `toast toast-${type}`;
    toast.setAttribute("role", "alert");

    const icons = { success: "✓", error: "✕", warning: "⚠", info: "ℹ" };

    toast.innerHTML = `
      <div class="toast-icon">${icons[type] || "ℹ"}</div>
      <div class="toast-content">
        ${title ? `<div class="toast-title">${App.escapeHtml(title)}</div>` : ""}
        ${message ? `<div class="toast-message">${App.escapeHtml(message)}</div>` : ""}
      </div>
    `;

    document.body.appendChild(toast);

    // Animation & Removal logic
    const removeToast = () => {
      toast.style.opacity = "0";
      toast.style.transform = "translateX(100%)";
      setTimeout(() => toast.remove(), App.constants.ANIMATION_DURATION);
    };

    if (duration > 0) setTimeout(removeToast, duration);
    toast.addEventListener("click", removeToast);
  };

  // ============================================
  // Modal Dialog (Matches base.css .modal)
  // ============================================

  App.showModal = function (title, content, options = {}) {
    const {
      confirmText = "ยืนยัน",
      cancelText = "ยกเลิก",
      onConfirm = null,
      onCancel = null,
      showCancel = true,
      size = "", // Default size
    } = options;

    const existingModal = document.querySelector(".modal-overlay");
    if (existingModal) existingModal.remove();

    const overlay = document.createElement("div");
    overlay.className = "modal-overlay";

    const cancelButton = showCancel
      ? `<button class="btn btn-outline" data-action="cancel">${App.escapeHtml(cancelText)}</button>`
      : "";

    overlay.innerHTML = `
      <div class="modal ${size ? `modal-${size}` : ''}">
        <div class="modal-header">
          <h3 class="modal-title">${App.escapeHtml(title)}</h3>
          <button class="modal-close" data-action="close">×</button>
        </div>
        <div class="modal-body">${content}</div>
        <div class="modal-footer">
          ${cancelButton}
          <button class="btn btn-primary" data-action="confirm">${App.escapeHtml(confirmText)}</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    document.body.style.overflow = "hidden"; // Prevent scrolling

    const closeModal = (callback) => {
      document.body.style.overflow = "";
      overlay.style.opacity = "0";
      setTimeout(() => {
        overlay.remove();
        if (callback) callback();
      }, App.constants.ANIMATION_DURATION);
    };

    overlay.addEventListener("click", (e) => {
      const action = e.target.closest("[data-action]")?.dataset.action;
      if (action === "close" || action === "cancel" || e.target === overlay) {
        closeModal(onCancel);
      } else if (action === "confirm") {
        closeModal(onConfirm);
      }
    });
  };

  App.confirmDialog = function (title, message, onConfirm, onCancel) {
    return App.showModal(title, `<p class="text-secondary">${App.escapeHtml(message)}</p>`, {
      onConfirm, onCancel, showCancel: true
    });
  };

  App.alertDialog = function (title, message) {
    return App.showModal(title, `<p class="text-secondary">${App.escapeHtml(message)}</p>`, {
      confirmText: "ตกลง", showCancel: false
    });
  };

  // ============================================
  // Lazy Loading Images
  // ============================================

  App.initLazyLoading = function (selector = "img[data-src]") {
    const images = document.querySelectorAll(selector);

    if (!("IntersectionObserver" in window)) {
      images.forEach(img => {
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute("data-src");
        }
      });
      return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.style.backgroundColor = "var(--surface-muted)"; // Skeleton effect

          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.onload = () => {
              img.style.backgroundColor = "transparent";
              img.removeAttribute("data-src");
              img.classList.add("loaded");
            };
            img.onerror = () => {
              img.style.backgroundColor = "transparent";
            };
          }
          obs.unobserve(img);
        }
      });
    });

    images.forEach(img => observer.observe(img));
  };

  // ============================================
  // Form Validation
  // ============================================

  App.validateForm = function (formElement, rules) {
    let isValid = true;
    const errors = {};

    Object.keys(rules).forEach((fieldName) => {
      const field = formElement.elements[fieldName];
      const fieldRules = rules[fieldName];
      if (!field) return;

      field.classList.remove("border-danger");
      const existingError = field.parentElement.querySelector(".text-danger");
      if (existingError) existingError.remove();

      let errorMessage = null;

      if (fieldRules.required && !field.value.trim()) {
        errorMessage = "กรุณากรอกข้อมูล";
      } else if (fieldRules.minLength && field.value.length < fieldRules.minLength) {
        errorMessage = `ต้องมีอย่างน้อย ${fieldRules.minLength} ตัวอักษร`;
      } else if (fieldRules.pattern && !fieldRules.pattern.test(field.value)) {
        errorMessage = "รูปแบบไม่ถูกต้อง";
      }

      if (errorMessage) {
        isValid = false;
        errors[fieldName] = errorMessage;
        field.style.borderColor = "var(--error-color)";

        const errorDiv = document.createElement("div");
        errorDiv.className = "text-danger text-xs mt-1";
        errorDiv.textContent = errorMessage;
        field.parentElement.appendChild(errorDiv);
      } else {
        field.style.borderColor = "";
      }
    });

    return { isValid, errors };
  };

  // ============================================
  // Loading Spinner
  // ============================================
  App.showLoading = function (message = "กำลังโหลด...") {
    if (document.querySelector(".app-loading")) return;
    const loading = document.createElement("div");
    loading.className = "app-loading";
    loading.innerHTML = `
      <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(2px);">
        <div style="background:white;padding:1.5rem;border-radius:12px;display:flex;flex-direction:column;align-items:center;gap:1rem;">
          <div class="spinner" style="width:30px;height:30px;border:3px solid #eee;border-top-color:var(--primary-color);border-radius:50%;animation:spin 1s linear infinite;"></div>
          <span style="font-size:0.9rem;font-weight:500;">${App.escapeHtml(message)}</span>
        </div>
      </div>
      <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
    `;
    document.body.appendChild(loading);
  };

  App.hideLoading = function () {
    document.querySelector(".app-loading")?.remove();
  };

})();