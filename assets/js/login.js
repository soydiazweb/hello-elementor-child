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
  });
})();
