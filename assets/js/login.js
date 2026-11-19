(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var login = document.getElementById('login');
    var logoLink = document.querySelector('.login h1 a');
    var form = document.querySelector('.login form');
    var loginData = window.HelloChildLogin || {};
    var siteName = loginData.siteName || document.title;
    var i18n = loginData.i18n || {};
    var headingTitle = i18n.headingTitle || 'Admin panel';
    var headingSubtitle = i18n.headingSubtitle || ('Secure access to ' + siteName);

    if (login && !document.querySelector('.custom-login-heading')) {
      var heading = document.createElement('div');
      heading.className = 'custom-login-heading';
      heading.innerHTML = '<strong>' + headingTitle + '</strong><span>' + headingSubtitle + '</span>';
      login.insertBefore(heading, form || login.firstChild);
    }

    if (logoLink) {
      logoLink.setAttribute('aria-label', siteName);
      logoLink.setAttribute('title', siteName);
    }

    var toggles = document.querySelectorAll('.password-toggle');

    toggles.forEach(function (toggle) {
      toggle.setAttribute('title', i18n.showPassword || 'Show password');
      toggle.addEventListener('click', function () {
        var targetId = toggle.getAttribute('data-target');
        var input = targetId ? document.getElementById(targetId) : toggle.parentNode.querySelector('input');

        if (!input) {
          return;
        }

        var eyeIcon = toggle.querySelector('.icon-eye');
        var eyeOffIcon = toggle.querySelector('.icon-eye-off');
        var isHidden = input.getAttribute('type') === 'password';

        input.setAttribute('type', isHidden ? 'text' : 'password');
        toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        toggle.setAttribute('aria-label', isHidden ? (i18n.hidePassword || 'Hide password') : (i18n.showPassword || 'Show password'));
        toggle.setAttribute('title', isHidden ? (i18n.hidePassword || 'Hide password') : (i18n.showPassword || 'Show password'));

        if (eyeIcon && eyeOffIcon) {
          eyeIcon.toggleAttribute('hidden', isHidden);
          eyeOffIcon.toggleAttribute('hidden', !isHidden);
        }

        input.focus();
      });
    });
  });
})();
