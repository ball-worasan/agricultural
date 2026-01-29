// login.js
(function () {
  "use strict";

  const pageRoot = document.querySelector('[data-page="login"]');
  if (!pageRoot) return;

  const form = pageRoot.querySelector(".auth-form");
  const submitBtn = form.querySelector("button[type='submit']");

  // 1. Password Toggle Logic (รองรับไอคอน SVG)
  const toggleBtns = pageRoot.querySelectorAll(".toggle-password");

  toggleBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // หา Input ที่อยู่คู่กับปุ่มนี้
      const wrapper = btn.closest('.password-input-wrapper');
      const input = wrapper.querySelector('input');
      const eyeIcon = btn.querySelector('.icon-eye');
      const eyeOffIcon = btn.querySelector('.icon-eye-off');

      if (input) {
        // สลับ type password/text
        const isPass = input.type === "password";
        input.type = isPass ? "text" : "password";

        // สลับการแสดงไอคอน
        if (eyeIcon && eyeOffIcon) {
          // ถ้าตอนนี้เป็น text (แสดงรหัส) ให้ซ่อนรูปตาปกติ แสดงรูปตาปิด
          const isNowText = input.type === 'text';
          eyeIcon.classList.toggle('d-none', isNowText);
          eyeOffIcon.classList.toggle('d-none', !isNowText);

          // อัปเดต ARIA label เพื่อการเข้าถึง
          btn.setAttribute('aria-label', isNowText ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
        }
      }
    });
  });

  // 2. Form Submit State (เหมือนเดิม)
  if (form) {
    form.addEventListener("submit", (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
      }

      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        const originalText = submitBtn.innerText;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังเข้าสู่ระบบ...`;
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerText = originalText;
        }, 15000);
      }
    });
  }
})();