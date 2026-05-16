(function () {
  'use strict';

  document.documentElement.classList.add('hello-child-js-ready');

  /**
   * For administrators only, hide blocking cookie/privacy overlays that can prevent
   * site navigation while working in the front-end theme preview.
   */
  function hideAdminBlockingOverlays() {
    if (!document.body || !document.body.classList.contains('user-can-manage-options')) {
      return;
    }

    var keywords = ['cookie', 'cookies', 'consent', 'gdpr', 'privacidad', 'privacy', 'cookiespro'];
    var nodes = Array.prototype.slice.call(document.body.querySelectorAll('body *'));

    nodes.forEach(function (node) {
      var marker = ((node.id || '') + ' ' + (node.className || '')).toString().toLowerCase();
      var looksLikeCookieElement = keywords.some(function (keyword) {
        return marker.indexOf(keyword) !== -1;
      });

      if (!looksLikeCookieElement) {
        return;
      }

      var computed = window.getComputedStyle(node);
      var rect = node.getBoundingClientRect();
      var zIndex = parseInt(computed.zIndex, 10) || 0;
      var isBlockingFixedElement =
        (computed.position === 'fixed' || computed.position === 'sticky') &&
        zIndex >= 999 &&
        (rect.width >= window.innerWidth * 0.65 || rect.height >= window.innerHeight * 0.25);

      if (isBlockingFixedElement) {
        node.setAttribute('data-hidden-for-admin', 'true');
        node.style.setProperty('display', 'none', 'important');
      }
    });

    document.documentElement.style.removeProperty('overflow');
    document.body.style.removeProperty('overflow');
  }

  document.addEventListener('DOMContentLoaded', hideAdminBlockingOverlays);
  window.addEventListener('load', hideAdminBlockingOverlays);
  window.setTimeout(hideAdminBlockingOverlays, 800);
  window.setTimeout(hideAdminBlockingOverlays, 2000);

  /**
   * Back button shortcode handler.
   */
  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-sw-back-button]');

    if (!button) {
      return;
    }

    event.preventDefault();

    if (window.history.length > 1) {
      window.history.back();
      return;
    }

    var fallbackUrl = button.getAttribute('data-fallback-url') || '/';
    window.location.href = fallbackUrl;
  });

})();
