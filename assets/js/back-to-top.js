(function () {
  'use strict';

  var selector = '.hello-child-back-to-top';
  var visibleClass = 'is-visible';
  var threshold = 220;

  function getSetting(name, fallback) {
    if (!window.HelloChildBackToTop || typeof window.HelloChildBackToTop[name] === 'undefined') {
      return fallback;
    }

    return window.HelloChildBackToTop[name] || fallback;
  }

  function getOrCreateButton() {
    var button = document.querySelector(selector);

    if (button || !document.body) {
      return button;
    }

    button = document.createElement('button');
    button.type = 'button';
    button.className = 'hello-child-back-to-top';
    button.setAttribute('aria-label', getSetting('ariaLabel', 'Back to top'));

    var icon = document.createElement('span');
    icon.className = 'hello-child-back-to-top__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = getSetting('icon', '↑');

    button.appendChild(icon);
    document.body.appendChild(button);

    return button;
  }

  function initBackToTop() {
    var button = getOrCreateButton();

    if (!button) {
      return;
    }

    var ticking = false;

    function updateVisibility() {
      var currentScroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;

      if (currentScroll > threshold) {
        button.classList.add(visibleClass);
      } else {
        button.classList.remove(visibleClass);
      }

      ticking = false;
    }

    function requestVisibilityUpdate() {
      if (!ticking) {
        window.requestAnimationFrame(updateVisibility);
        ticking = true;
      }
    }

    button.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    window.addEventListener('scroll', requestVisibilityUpdate, { passive: true });
    window.addEventListener('resize', requestVisibilityUpdate, { passive: true });
    updateVisibility();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBackToTop);
  } else {
    initBackToTop();
  }
})();
