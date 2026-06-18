(function () {
  function initMobileMenu() {
    var button = document.querySelector('[data-lfk-menu-toggle]');
    var menu = document.querySelector('[data-lfk-mobile-nav]');
    if (!button || !menu) return;

    button.addEventListener('click', function () {
      var isOpen = menu.classList.toggle('is-open');
      button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  function initHeroSlider() {
    var root = document.querySelector('[data-lfk-hero]');
    if (!root) return;

    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-lfk-slide]'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-lfk-dot]'));
    var prev = root.querySelector('[data-lfk-prev]');
    var next = root.querySelector('[data-lfk-next]');
    var index = 0;
    var timer = null;

    if (!slides.length) return;

    function show(nextIndex) {
      index = (nextIndex + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        slide.classList.toggle('is-active', slideIndex === index);
      });
      dots.forEach(function (dot, dotIndex) {
        dot.classList.toggle('is-active', dotIndex === index);
        dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
      });
    }

    function restart() {
      if (timer) window.clearInterval(timer);
      timer = window.setInterval(function () {
        show(index + 1);
      }, 5000);
    }

    if (prev) {
      prev.addEventListener('click', function () {
        show(index - 1);
        restart();
      });
    }

    if (next) {
      next.addEventListener('click', function () {
        show(index + 1);
        restart();
      });
    }

    dots.forEach(function (dot, dotIndex) {
      dot.addEventListener('click', function () {
        show(dotIndex);
        restart();
      });
    });

    show(0);
    restart();
  }

  function initCarousels() {
    var roots = Array.prototype.slice.call(document.querySelectorAll('[data-lfk-carousel]'));

    roots.forEach(function (root) {
      var track = root.querySelector('[data-lfk-carousel-track]');
      var prev = root.querySelector('[data-lfk-carousel-prev]');
      var next = root.querySelector('[data-lfk-carousel-next]');
      var index = 0;
      var timer = null;

      if (!track || !track.children.length) return;

      function visibleCount() {
        if (window.matchMedia('(min-width: 1024px)').matches) return 4;
        if (window.matchMedia('(min-width: 640px)').matches) return 3;
        return 2;
      }

      function maxIndex() {
        return Math.max(0, track.children.length - visibleCount());
      }

      function show(nextIndex) {
        index = Math.max(0, Math.min(nextIndex, maxIndex()));
        var firstItem = track.children[0];
        var gap = 12;
        var itemWidth = firstItem.getBoundingClientRect().width + gap;
        track.style.transform = 'translateX(' + (-index * itemWidth) + 'px)';
      }

      function restart() {
        if (timer) window.clearInterval(timer);
        timer = window.setInterval(function () {
          show(index >= maxIndex() ? 0 : index + 1);
        }, 5000);
      }

      if (prev) {
        prev.addEventListener('click', function () {
          show(index - 1);
          restart();
        });
      }

      if (next) {
        next.addEventListener('click', function () {
          show(index + 1);
          restart();
        });
      }

      window.addEventListener('resize', function () {
        show(index);
      });

      show(0);
      restart();
    });
  }

  function initBackToTop() {
    var button = document.querySelector('[data-lfk-back-top]');
    if (!button) return;

    button.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  function initCatalogOrdering() {
    var forms = Array.prototype.slice.call(document.querySelectorAll('.woocommerce-ordering'));

    forms.forEach(function (form) {
      var select = form.querySelector('select.orderby');
      if (!select) return;

      select.addEventListener('change', function () {
        form.submit();
      });
    });
  }

  function initProductGallery() {
    var galleries = Array.prototype.slice.call(document.querySelectorAll('[data-lfk-product-gallery]'));

    galleries.forEach(function (gallery) {
      var main = gallery.querySelector('[data-lfk-product-main]');
      var mainArea = gallery.querySelector('.lfk-product-gallery-main');
      var track = gallery.querySelector('.lfk-product-gallery-track');
      var slides = track ? Array.prototype.slice.call(track.querySelectorAll('img:not(.lfk-product-gallery-zoom-clone):not(.lfk-product-zoom-marker)')) : [];
      var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-lfk-product-thumb]'));
      var index = 0;

      if (!thumbs.length || (!main && !track)) return;

      function slideWidth() {
        var firstSlide = slides[0];
        var slideBox = firstSlide ? firstSlide.getBoundingClientRect() : null;
        var mainBox = mainArea ? mainArea.getBoundingClientRect() : null;
        return (slideBox && slideBox.width) || (mainBox && mainBox.width) || 0;
      }

      function show(nextIndex) {
        index = Math.max(0, Math.min(nextIndex, thumbs.length - 1));

        if (main) {
          main.src = thumbs[index].getAttribute('data-image');
          main.srcset = thumbs[index].getAttribute('data-srcset') || '';
        }

        if (mainArea) {
          mainArea.style.setProperty('--lfk-gallery-current-mobile-height', (thumbs[index].getAttribute('data-mobile-height') || '370') + 'px');
          mainArea.style.setProperty('--lfk-gallery-current-desktop-height', (thumbs[index].getAttribute('data-desktop-height') || '575') + 'px');
        }

        if (track && slides.length) {
          track.style.transform = 'translateX(' + (-index * slideWidth()) + 'px)';
        }

        thumbs.forEach(function (item, thumbIndex) {
          item.classList.toggle('is-active', thumbIndex === index);
          item.setAttribute('aria-current', thumbIndex === index ? 'true' : 'false');
        });
      }

      thumbs.forEach(function (thumb, thumbIndex) {
        thumb.addEventListener('click', function () {
          show(thumbIndex);
        });
      });

      window.addEventListener('resize', function () {
        show(index);
      });

      show(0);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initHeroSlider();
    initCarousels();
    initBackToTop();
    initCatalogOrdering();
    initProductGallery();
  });
})();
