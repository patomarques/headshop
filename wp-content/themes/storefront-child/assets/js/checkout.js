/* global jQuery */
(function ($) {
  'use strict';

  $(function () {
    var $postcode = $('#billing_postcode');
    if (!$postcode.length) return;

    var debounce;

    $postcode.on('input keyup', function () {
      var cep = $(this).val().replace(/\D/g, '');
      clearTimeout(debounce);
      if (cep.length !== 8) return;

      debounce = setTimeout(function () {
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (data.erro) return;

            var fill = {
              billing_address_1: data.logradouro,
              billing_city:      data.localidade,
              billing_state:     data.uf,
            };

            $.each(fill, function (id, val) {
              if (!val) return;
              $('#' + id).val(val).trigger('change');
            });

            $('body').trigger('update_checkout');

            var $number = $('#billing_number');
            if ($number.length) {
              $number.focus();
            }
          })
          .catch(function () {});
      }, 400);
    });
  });
}(jQuery));
