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
    var preloadTimer = null;

    if (!slides.length) return;

    function loadSlide(slide) {
      if (!slide || slide.getAttribute('data-lfk-loaded') === '1') {
        return Promise.resolve();
      }

      var sources = Array.prototype.slice.call(slide.querySelectorAll('source[data-srcset]'));
      var image = slide.querySelector('img[data-src]');

      sources.forEach(function (source) {
        source.setAttribute('srcset', source.getAttribute('data-srcset'));
        source.removeAttribute('data-srcset');
      });

      if (!image) {
        slide.setAttribute('data-lfk-loaded', '1');
        return Promise.resolve();
      }

      return new Promise(function (resolve) {
        image.addEventListener('load', resolve, { once: true });
        image.addEventListener('error', resolve, { once: true });

        if (image.getAttribute('data-srcset')) {
          image.setAttribute('srcset', image.getAttribute('data-srcset'));
          image.removeAttribute('data-srcset');
        }

        image.src = image.getAttribute('data-src');
        image.removeAttribute('data-src');
        slide.setAttribute('data-lfk-loaded', '1');

        if (image.complete) resolve();
      });
    }

    function preloadNext() {
      if (preloadTimer) window.clearTimeout(preloadTimer);
      preloadTimer = window.setTimeout(function () {
        loadSlide(slides[(index + 1) % slides.length]);
      }, 2600);
    }

    function activate(nextIndex) {
      index = (nextIndex + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        slide.classList.toggle('is-active', slideIndex === index);
      });
      dots.forEach(function (dot, dotIndex) {
        dot.classList.toggle('is-active', dotIndex === index);
        dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
      });
      preloadNext();
    }

    function show(nextIndex) {
      var normalizedIndex = (nextIndex + slides.length) % slides.length;
      loadSlide(slides[normalizedIndex]).then(function () {
        activate(normalizedIndex);
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

    slides[0].setAttribute('data-lfk-loaded', '1');
    activate(0);
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
        if (window.matchMedia('(min-width: 1024px)').matches) return parseInt(root.getAttribute('data-lfk-visible-desktop') || '4', 10);
        if (window.matchMedia('(min-width: 640px)').matches) return parseInt(root.getAttribute('data-lfk-visible-tablet') || '3', 10);
        return parseInt(root.getAttribute('data-lfk-visible-mobile') || '2', 10);
      }

      function maxIndex() {
        return Math.max(0, track.children.length - visibleCount());
      }

      function show(nextIndex) {
        index = Math.max(0, Math.min(nextIndex, maxIndex()));
        var firstItem = track.children[0];
        var trackStyle = window.getComputedStyle(track);
        var gap = parseFloat(trackStyle.columnGap || trackStyle.gap || '0') || 0;
        var itemWidth = firstItem.getBoundingClientRect().width + gap;
        track.style.transform = 'translateX(' + (-index * itemWidth) + 'px)';
      }

      function restart() {
        if (timer) window.clearInterval(timer);
        timer = window.setInterval(function () {
          show(index >= maxIndex() ? 0 : index + 1);
        }, 5000);
      }

      function pause() {
        if (timer) window.clearInterval(timer);
        timer = null;
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

      root.addEventListener('mouseenter', pause);
      root.addEventListener('mouseleave', restart);
      root.addEventListener('focusin', pause);
      root.addEventListener('focusout', restart);

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

  function initCommerceFeedback() {
    var toastTimer = null;
    var pendingButtons = [];
    var pendingSingleButtons = [];
    var pendingStorageKey = 'lfkPendingCartFeedbackAt';

    function showToast(message) {
      var toast = document.querySelector('[data-lfk-cart-toast]');

      if (!toast) {
        toast = document.createElement('div');
        toast.className = 'lfk-cart-toast';
        toast.setAttribute('data-lfk-cart-toast', '');
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML = '<span></span><a href="/cart/">ดูตะกร้า</a>';
        document.body.appendChild(toast);
      }

      toast.querySelector('span').textContent = message;
      toast.classList.add('is-visible');

      if (toastTimer) window.clearTimeout(toastTimer);
      toastTimer = window.setTimeout(function () {
        toast.classList.remove('is-visible');
      }, 2600);
    }

    function pulseCart() {
      var cartLink = document.querySelector('.lfk-cart-link');
      if (!cartLink) return;
      cartLink.classList.remove('is-updated');
      window.requestAnimationFrame(function () {
        cartLink.classList.add('is-updated');
      });
    }

    function completeFeedback(button) {
      if (button) {
        button.classList.remove('loading');
        button.classList.add('added');
      }
      pulseCart();
      showToast('เพิ่มสินค้าในตะกร้าแล้ว');
    }

    function queueFallback(button) {
      if (!button || pendingButtons.indexOf(button) !== -1) return;
      pendingButtons.push(button);
      window.setTimeout(function () {
        var index = pendingButtons.indexOf(button);
        if (index !== -1) pendingButtons.splice(index, 1);
        if (button.classList.contains('loading') || button.classList.contains('added')) {
          completeFeedback(button);
        }
      }, 900);
    }

    function cartSignature() {
      var count = document.querySelector('.lfk-cart-count');
      var total = document.querySelector('.lfk-cart-total');
      return [count ? count.textContent.trim() : '', total ? total.textContent.trim() : ''].join('|');
    }

    function armSingleButton(button) {
      if (!button || pendingSingleButtons.indexOf(button) !== -1) return;
      button.classList.add('loading');
      pendingSingleButtons.push(button);

      window.setTimeout(function () {
        var index = pendingSingleButtons.indexOf(button);
        if (index === -1) return;
        pendingSingleButtons.splice(index, 1);
        button.classList.remove('loading');
      }, 5000);
    }

    function completeSingleButtons() {
      if (!pendingSingleButtons.length) return;
      pendingSingleButtons.splice(0).forEach(function (button) {
        completeFeedback(button);
      });
    }

    function completeObservedCartChange() {
      if (pendingSingleButtons.length) {
        completeSingleButtons();
        return;
      }

      var button = document.querySelector('.lfk-single-cart .single_add_to_cart_button, form.cart .single_add_to_cart_button');
      if (button) {
        completeFeedback(button);
        return;
      }

      pulseCart();
      showToast('เพิ่มสินค้าในตะกร้าแล้ว');
    }

    function markPendingNavigationFeedback() {
      try {
        window.sessionStorage.setItem(pendingStorageKey, String(Date.now()));
      } catch (error) {}
    }

    function consumePendingNavigationFeedback() {
      var timestamp = 0;

      try {
        timestamp = parseInt(window.sessionStorage.getItem(pendingStorageKey) || '0', 10);
        window.sessionStorage.removeItem(pendingStorageKey);
      } catch (error) {
        return;
      }

      if (!timestamp || Date.now() - timestamp > 10000) return;

      window.setTimeout(function () {
        completeObservedCartChange();
      }, 150);
    }

    function updateFragments(fragments) {
      if (!fragments) return;

      Object.keys(fragments).forEach(function (selector) {
        var nodes = Array.prototype.slice.call(document.querySelectorAll(selector));
        nodes.forEach(function (node) {
          node.outerHTML = fragments[selector];
        });
      });
    }

    function fallbackAjaxAddToCart(button) {
      var productId = button.getAttribute('data-product_id');
      var quantity = button.getAttribute('data-quantity') || '1';

      if (!productId || !window.fetch || !window.URLSearchParams) {
        window.location.href = button.href;
        return;
      }

      var body = new URLSearchParams();
      body.set('product_id', productId);
      body.set('quantity', quantity);

      window.fetch('/?wc-ajax=add_to_cart', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: body.toString()
      }).then(function (response) {
        return response.json();
      }).then(function (payload) {
        if (payload && payload.error && payload.product_url) {
          window.location.href = payload.product_url;
          return;
        }

        updateFragments(payload && payload.fragments);
        completeFeedback(button);
      }).catch(function () {
        window.location.href = button.href;
      });
    }

    document.body.addEventListener('click', function (event) {
      var button = event.target.closest ? event.target.closest('.lfk-add-to-cart.ajax_add_to_cart') : null;
      if (button) {
        button.classList.add('loading');
        queueFallback(button);
        if (!window.jQuery || !window.wc_add_to_cart_params) {
          event.preventDefault();
          fallbackAjaxAddToCart(button);
        }
      }
    });

    document.addEventListener('click', function (event) {
      var button = event.target.closest ? event.target.closest('.lfk-single-cart .single_add_to_cart_button, form.cart .single_add_to_cart_button') : null;
      if (button) {
        armSingleButton(button);
      }
    }, true);

    document.addEventListener('pointerdown', function (event) {
      var button = event.target.closest ? event.target.closest('.lfk-single-cart .single_add_to_cart_button, form.cart .single_add_to_cart_button') : null;
      if (button) {
        markPendingNavigationFeedback();
      }
    }, true);

    var currentCartSignature = cartSignature();
    var cartObserverTarget = document.body;
    if (cartObserverTarget && window.MutationObserver) {
      new MutationObserver(function () {
        var nextSignature = cartSignature();
        if (nextSignature !== currentCartSignature) {
          currentCartSignature = nextSignature;
          completeObservedCartChange();
        }
      }).observe(cartObserverTarget, { childList: true, subtree: true, characterData: true });
    }

    function bindWooEvents(attempt) {
      if (!window.jQuery) {
        if (attempt < 20) {
          window.setTimeout(function () {
            bindWooEvents(attempt + 1);
          }, 250);
        }
        return;
      }

      if (document.body.getAttribute('data-lfk-commerce-bound') === '1') return;
      document.body.setAttribute('data-lfk-commerce-bound', '1');

      window.jQuery(document.body).on('adding_to_cart', function (event, button) {
        if (button && button.addClass) button.addClass('loading');
      });

      window.jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, button) {
        var rawButton = button && button.get ? button.get(0) : null;
        if (rawButton) {
          var index = pendingButtons.indexOf(rawButton);
          if (index !== -1) pendingButtons.splice(index, 1);
        }
        if (button && button.removeClass) button.removeClass('loading').addClass('added');
        pulseCart();
        showToast('เพิ่มสินค้าในตะกร้าแล้ว');
      });
    }

    bindWooEvents(0);
    consumePendingNavigationFeedback();

    Array.prototype.slice.call(document.querySelectorAll('.lfk-single-cart form.cart')).forEach(function (form) {
      var button = form.querySelector('.single_add_to_cart_button');

      if (button) {
        button.addEventListener('click', function () {
          armSingleButton(button);
        });
      }

      form.addEventListener('submit', function () {
        armSingleButton(button);
      });
    });

    var checkout = document.querySelector('form.checkout');
    if (checkout) {
      checkout.addEventListener('submit', function () {
        var placeOrder = checkout.querySelector('#place_order');
        if (placeOrder) placeOrder.classList.add('loading');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initHeroSlider();
    initCarousels();
    initBackToTop();
    initCatalogOrdering();
    initProductGallery();
    initCommerceFeedback();
  });
})();
