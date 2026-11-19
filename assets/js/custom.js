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
   * SW form helpers.
   * Add class "formsw" to a container. Every form inside it will get
   * floating placeholders and validations. If the class is placed directly
   * on a form, it is supported for backwards compatibility.
   */
  var formswFieldId = 0;
  var floatingTypes = ['text', 'email', 'url', 'tel', 'number', 'password', 'search', 'date', 'time', 'datetime-local'];

  function isValidFloatingField(field) {
    if (!field || !field.tagName) {
      return false;
    }

    var tagName = field.tagName.toLowerCase();

    if (tagName === 'textarea') {
      return true;
    }

    if (tagName !== 'input') {
      return false;
    }

    var type = (field.getAttribute('type') || 'text').toLowerCase();

    return floatingTypes.indexOf(type) !== -1;
  }

  function getFieldMarker(field) {
    return [
      field.getAttribute('type') || '',
      field.getAttribute('name') || '',
      field.getAttribute('id') || '',
      field.getAttribute('autocomplete') || '',
      field.className || '',
      field.getAttribute('data-sw-field') || ''
    ].join(' ').toLowerCase();
  }

  function hasFieldMode(field, mode) {
    var dataMode = (field.getAttribute('data-sw-field') || '').toLowerCase();

    if (dataMode === mode) {
      return true;
    }

    if (mode === 'text') {
      return field.classList.contains('sw-only-text');
    }

    if (mode === 'number') {
      return field.classList.contains('sw-only-number');
    }

    if (mode === 'phone') {
      return field.classList.contains('sw-phone');
    }

    return false;
  }

  function shouldValidateAsPhone(field) {
    if (!field || field.classList.contains('sw-no-validate')) {
      return false;
    }

    var type = (field.getAttribute('type') || '').toLowerCase();
    var marker = getFieldMarker(field);

    return (
      type === 'tel' ||
      hasFieldMode(field, 'phone') ||
      marker.indexOf('telefono') !== -1 ||
      marker.indexOf('teléfono') !== -1 ||
      marker.indexOf('celular') !== -1 ||
      marker.indexOf('whatsapp') !== -1 ||
      marker.indexOf('phone') !== -1 ||
      marker.indexOf('mobile') !== -1 ||
      marker.indexOf(' tel') !== -1 ||
      marker.indexOf('tel ') !== -1
    );
  }

  function shouldValidateAsNumber(field) {
    if (!field || field.classList.contains('sw-no-validate') || shouldValidateAsPhone(field)) {
      return false;
    }

    var type = (field.getAttribute('type') || '').toLowerCase();

    return type === 'number' || hasFieldMode(field, 'number');
  }

  function shouldValidateAsText(field) {
    if (!field || field.classList.contains('sw-no-validate') || field.classList.contains('sw-allow-numbers')) {
      return false;
    }

    if (field.getAttribute('data-sw-allow-numbers') === 'true') {
      return false;
    }

    if (hasFieldMode(field, 'text')) {
      return true;
    }

    var tagName = field.tagName.toLowerCase();
    var type = (field.getAttribute('type') || 'text').toLowerCase();

    return tagName === 'input' && type === 'text';
  }

  function updateFloatingState(field) {
    var wrapper = field.closest('.sw-floating-field');

    if (!wrapper) {
      return;
    }

    wrapper.classList.toggle('has-value', !!field.value);
    wrapper.classList.toggle('is-focused', document.activeElement === field);
  }

  function setupFloatingLabel(field) {
    if (!isValidFloatingField(field) || field.dataset.swFloatingReady === 'true') {
      return;
    }

    var placeholder = field.getAttribute('placeholder');

    if (!placeholder) {
      return;
    }

    var wrapper = field.closest('.sw-floating-field');

    if (!wrapper) {
      wrapper = document.createElement('span');
      wrapper.className = 'sw-floating-field';
      field.parentNode.insertBefore(wrapper, field);
      wrapper.appendChild(field);
    } else {
      wrapper.classList.add('sw-floating-field');
    }

    if (!field.id) {
      formswFieldId += 1;
      field.id = 'formsw-field-' + formswFieldId;
    }

    var label = wrapper.querySelector('.sw-floating-label');

    if (!label) {
      label = document.createElement('label');
      label.className = 'sw-floating-label';
      wrapper.appendChild(label);
    }

    label.setAttribute('for', field.id);
    label.textContent = placeholder;

    if (!field.getAttribute('aria-label')) {
      field.setAttribute('aria-label', placeholder);
    }

    field.setAttribute('data-sw-placeholder', placeholder);
    field.setAttribute('placeholder', ' ');
    field.dataset.swFloatingReady = 'true';

    ['input', 'change', 'focus', 'blur'].forEach(function (eventName) {
      field.addEventListener(eventName, function () {
        updateFloatingState(field);
      });
    });

    updateFloatingState(field);
    window.setTimeout(function () {
      updateFloatingState(field);
    }, 250);
  }

  function keepOnlyLetters(field) {
    var cleanValue = field.value.replace(/[0-9]/g, '');

    if (field.value !== cleanValue) {
      field.value = cleanValue;
    }
  }

  function keepOnlyDigits(field, maxLength) {
    var cleanValue = field.value.replace(/\D+/g, '');

    if (maxLength) {
      cleanValue = cleanValue.slice(0, maxLength);
    }

    if (field.value !== cleanValue) {
      field.value = cleanValue;
    }
  }

  function validatePhone(field) {
    keepOnlyDigits(field, 11);

    var value = field.value;

    if (value && value.length < 8) {
      field.setCustomValidity('Ingresa mínimo 8 números.');
    } else {
      field.setCustomValidity('');
    }
  }

  function setupInputValidations(form) {
    var fields = Array.prototype.slice.call(form.querySelectorAll('input, textarea'));

    fields.forEach(function (field) {
      if (field.dataset.swValidationReady === 'true') {
        return;
      }

      if (shouldValidateAsPhone(field)) {
        field.setAttribute('inputmode', 'numeric');
        field.setAttribute('pattern', '[0-9]{8,11}');
        field.setAttribute('minlength', '8');
        field.setAttribute('maxlength', '11');

        ['input', 'change', 'paste', 'blur'].forEach(function (eventName) {
          field.addEventListener(eventName, function () {
            window.setTimeout(function () {
              validatePhone(field);
              updateFloatingState(field);
            }, 0);
          });
        });

        validatePhone(field);
      } else if (shouldValidateAsNumber(field)) {
        field.setAttribute('inputmode', 'numeric');
        field.setAttribute('pattern', '[0-9]*');

        ['input', 'change', 'paste'].forEach(function (eventName) {
          field.addEventListener(eventName, function () {
            window.setTimeout(function () {
              keepOnlyDigits(field);
              updateFloatingState(field);
            }, 0);
          });
        });

        keepOnlyDigits(field);
      } else if (shouldValidateAsText(field)) {
        ['input', 'change', 'paste'].forEach(function (eventName) {
          field.addEventListener(eventName, function () {
            window.setTimeout(function () {
              keepOnlyLetters(field);
              updateFloatingState(field);
            }, 0);
          });
        });

        keepOnlyLetters(field);
      }

      field.dataset.swValidationReady = 'true';
    });

    if (form.dataset.swSubmitValidationReady !== 'true') {
      form.addEventListener('submit', function (event) {
        var phoneFields = Array.prototype.slice.call(form.querySelectorAll('input, textarea')).filter(shouldValidateAsPhone);
        var firstInvalidField = null;

        phoneFields.forEach(function (field) {
          validatePhone(field);

          if (!firstInvalidField && !field.checkValidity()) {
            firstInvalidField = field;
          }
        });

        if (firstInvalidField) {
          event.preventDefault();
          firstInvalidField.reportValidity();
        }
      });

      form.dataset.swSubmitValidationReady = 'true';
    }
  }

  function setupFormsw(form) {
    if (!form) {
      return;
    }

    var fields = Array.prototype.slice.call(form.querySelectorAll('input[placeholder], textarea[placeholder]'));

    fields.forEach(setupFloatingLabel);
    setupInputValidations(form);

    form.dataset.swFormReady = 'true';
  }

  function getUniqueForms(forms) {
    return forms.filter(function (form, index, list) {
      return form && list.indexOf(form) === index;
    });
  }

  function findFormswForms(context) {
    var root = context && context.querySelectorAll ? context : document;
    var forms = [];
    var containers = [];

    /* Backwards compatibility: allow class="formsw" directly on the form. */
    if (root.matches && root.matches('form.formsw')) {
      forms.push(root);
    }

    if (root.matches && root.matches('.formsw')) {
      containers.push(root);
    }

    if (root.closest) {
      var closestContainer = root.closest('.formsw');

      if (closestContainer) {
        containers.push(closestContainer);
      }
    }

    containers = containers.concat(Array.prototype.slice.call(root.querySelectorAll('.formsw')));
    containers = containers.filter(function (container, index, list) {
      return container && list.indexOf(container) === index;
    });

    containers.forEach(function (container) {
      if (container.tagName && container.tagName.toLowerCase() === 'form') {
        forms.push(container);
        return;
      }

      forms = forms.concat(Array.prototype.slice.call(container.querySelectorAll('form')));
    });

    return getUniqueForms(forms);
  }

  function initFormsw(context) {
    findFormswForms(context).forEach(setupFormsw);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFormsw(document);

    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        Array.prototype.slice.call(mutation.addedNodes).forEach(function (node) {
          if (node.nodeType === 1) {
            initFormsw(node);
          }
        });
      });
    });

    if (document.body) {
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  });

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
