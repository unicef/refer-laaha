(function ($, Drupal, drupalSettings, once) {
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
        document.cookie = "pwacookie=true";
        localStorage.setItem("pwacookie", "true");
        $('.block-pwa-add-to-home-screen').addClass('hidden');
      });
      var cookiestatus = localStorage.getItem("pwacookie");
      if(cookiestatus == "true") {
        $('.block-pwa-add-to-home-screen').addClass('hidden');
      }
      else {
        $('.block-pwa-add-to-home-screen').removeClass('hidden');
      }

      function isRunningStandalone() {
        return (window.matchMedia('(display-mode: standalone)').matches);
      }
      if (isRunningStandalone()) {
        $('.block-pwa-add-to-home-screen').addClass('hidden');
      }

      if(navigator.userAgent.indexOf('Firefox') != -1) {
        let button = document.createElement("button");
        var button_text = settings.pwaA2hs.pwaA2hsPrompt.button_text;
        button.innerHTML = button_text;
        // Use jQuery once() so the button doesn't get added multiple times.
        $(once("showButton", ".pwa-a2hs", context)).each(function () {
          $(this)
            .removeClass("pwa-a2hs hidden")
            .addClass("pwa-a2hs-active show")
            .append(button);
        });
      }
    }
  };
}(jQuery, Drupal, drupalSettings, once));
