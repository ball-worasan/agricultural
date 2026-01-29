// register.js
(function () {
  "use strict";

  const pageRoot = document.querySelector('[data-page="register"]');
  if (!pageRoot) return;

  const form = pageRoot.querySelector(".auth-form");
  const submitBtn = form.querySelector("button[type='submit']");

  // 1. Password Toggle Logic (ใช้ Logic เดียวกับ Login ได้เลย)
  const toggleBtns = pageRoot.querySelectorAll(".toggle-password");

  toggleBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const wrapper = btn.closest('.password-input-wrapper');
      const input = wrapper.querySelector('input');
      const eyeIcon = btn.querySelector('.icon-eye');
      const eyeOffIcon = btn.querySelector('.icon-eye-off');

      if (input) {
        const isPass = input.type === "password";
        input.type = isPass ? "text" : "password";

        if (eyeIcon && eyeOffIcon) {
          const isNowText = input.type === 'text';
          eyeIcon.classList.toggle('d-none', isNowText);
          eyeOffIcon.classList.toggle('d-none', !isNowText);
          btn.setAttribute('aria-label', isNowText ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
        }
      }
    });
  });

  // 2. Form Submit & Validation (เหมือนเดิม)
  if (form) {
    form.addEventListener("submit", (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
      }

      const pass = document.getElementById('password')?.value;
      const confirm = document.getElementById('password_confirm')?.value;

      if (pass && confirm && pass !== confirm) {
        e.preventDefault();
        alert("รหัสผ่านยืนยันไม่ตรงกัน");
        return;
      }

      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        const originalText = submitBtn.innerText;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังลงทะเบียน...`;
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerText = originalText;
        }, 15000);
      }
    });
  }
})();