(function () {
  "use strict";

  const container = document.querySelector('[data-page="detail"]');
  if (!container) return;

  // 1. Image Gallery Logic
  const images = JSON.parse(container.dataset.images || "[]");
  const mainImage = document.getElementById("mainImage");
  const prevBtn = container.querySelector(".nav-btn.prev");
  const nextBtn = container.querySelector(".nav-btn.next");
  const counter = container.querySelector(".image-counter");
  const thumbs = container.querySelectorAll(".thumb-item");

  let currentIndex = 0;

  function updateGallery(index) {
    if (index < 0) index = images.length - 1;
    if (index >= images.length) index = 0;

    currentIndex = index;

    // Update Main Image
    mainImage.style.opacity = 0;
    setTimeout(() => {
      mainImage.src = images[currentIndex];
      mainImage.style.opacity = 1;
    }, 150);

    // Update Counter
    if (counter) counter.innerText = `${currentIndex + 1} / ${images.length}`;

    // Update Thumbs
    thumbs.forEach((t, i) => {
      t.classList.toggle("active", i === currentIndex);
    });
  }

  if (images.length > 1) {
    prevBtn?.addEventListener("click", () => updateGallery(currentIndex - 1));
    nextBtn?.addEventListener("click", () => updateGallery(currentIndex + 1));

    thumbs.forEach(thumb => {
      thumb.addEventListener("click", function () {
        updateGallery(parseInt(this.dataset.index));
      });
    });
  }

  // 2. Booking Form Logic
  const btnShowBooking = document.getElementById("btnShowBooking");
  const btnCancelBooking = document.getElementById("btnCancelBooking");
  const bookingForm = document.getElementById("bookingForm");
  const actionArea = container.querySelector(".action-area");

  if (btnShowBooking && bookingForm) {
    btnShowBooking.addEventListener("click", () => {
      actionArea.style.display = "none";
      bookingForm.style.display = "block";
    });

    btnCancelBooking.addEventListener("click", () => {
      bookingForm.style.display = "none";
      actionArea.style.display = "block";
    });
  }

  // 3. Form Submit with Confirmation
  const formBook = document.getElementById("formBook");
  if (formBook) {
    formBook.addEventListener("submit", (e) => {
      if (!confirm("ยืนยันการจองพื้นที่นี้?")) {
        e.preventDefault();
      }
    });
  }

})();