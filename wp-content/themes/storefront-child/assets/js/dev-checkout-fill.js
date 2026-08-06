/**
 * DEV ONLY — Auto-fill checkout with test data (WP_DEBUG = true).
 * Enqueued automatically on checkout. Never runs in production.
 */
/* global jQuery */
(function ($) {
  'use strict';

  var nativeSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;

  var rawCard = {
    'asaas-cc-name':             'CLIENTE TESTE',
    'asaas-cc-number':           '4444444444444444',
    'asaas-cc-expiration-month': '12',
    'asaas-cc-expiration-year':  '2030',
    'asaas-cc-security-code':    '123',
  };

  var postCard = {
    asaas_cc_name:             'CLIENTE TESTE',
    asaas_cc_number:           '4444444444444444',
    asaas_cc_expiration_month: '12',
    asaas_cc_expiration_year:  '2030',
    asaas_cc_security_code:    '123',
  };

  function fillCardFields() {
    Object.keys(rawCard).forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.focus();
      nativeSetter.call(el, rawCard[id]);
      el.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }

  function watchAndFill() {
    var payment = document.getElementById('payment');
    if (!payment) return;

    new MutationObserver(function (mutations) {
      var added = mutations.some(function (m) { return m.addedNodes.length > 0; });
      if (!added) return;
      setTimeout(fillCardFields, 300);
    }).observe(payment, { childList: true, subtree: true });
  }

  $(document).on('ajaxSend', function (_e, _xhr, settings) {
    if (!settings.url || settings.url.indexOf('wc-ajax=checkout') === -1) return;
    var extra = Object.keys(postCard).map(function (k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(postCard[k]);
    }).join('&');
    settings.data = (settings.data || '') + '&' + extra;
  });

  $(document).on('ready updated_checkout', function () {
    setTimeout(function () {
      fillCardFields();
      watchAndFill();
    }, 400);
  });

}(jQuery));
