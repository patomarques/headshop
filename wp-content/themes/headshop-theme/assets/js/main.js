document.addEventListener('DOMContentLoaded', function () {
  if (typeof Swiper === 'undefined') return;

  var settings = (window.headshopSettings || {});
  var slides = (settings.slidesPerView || { sm: 2, md: 3, lg: 4, xl: 5 });
  var space = (typeof settings.spaceBetween === 'number') ? settings.spaceBetween : 16;

  var config = {
    slidesPerView: slides.sm || 2,
    spaceBetween: space,
    breakpoints: {
      640: { slidesPerView: slides.md || 3, spaceBetween: space },
      1024: { slidesPerView: slides.lg || 4, spaceBetween: space },
      1280: { slidesPerView: slides.xl || 5, spaceBetween: space },
    },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    pagination: { el: '.swiper-pagination', clickable: true },
    loop: false,
  };

  document.querySelectorAll('.product-swiper').forEach(function (el) {
    new Swiper(el, config);
  });
});


