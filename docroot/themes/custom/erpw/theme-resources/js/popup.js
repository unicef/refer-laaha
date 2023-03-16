(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.popup = {
    attach: function (context, settings) {
      if ($(".sign-in-popup").length) {
        $('.sign-in-popup').show();
        $('.overlay').show();
        $('.close-popup, .skip').click(function(e){
          e.preventDefault();
            $('.sign-in-popup, .overlay').hide();
        });
      }
      $('.overlay').on("click", function() {
        $('.sign-in-popup').hide();
        $('[id^="block-erpw-main-menu"]').hide();
        $(this).hide();
        $('.close-popup').remove();
      }).appendTo($(document.body));

      $('#block-pwaaddtohomescreen img').click(function() {
        $('.pwa-a2hs-active').removeClass('pwa-a2hs-active show').addClass('pwa-a2hs hidden');
      });
    }
  };
}(jQuery, Drupal, drupalSettings));
