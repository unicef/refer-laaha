(function ($, Drupal, drupalSettings) {
    'use strict';
      $('.close-popup, .skip').click(function(e){
        e.preventDefault();
          $('.sign-in-popup, .overlay').hide();
      })
      if ($(".sign-in-popup").length) {
          console.log('abc');
        $('.sign-in-popup').show();
        $('<div class="overlay"></div>').on("click", function() {
          $('.sign-in-popup').hide();
          $(this).remove();
        }).appendTo($(document.body));
      }

  })(jQuery, Drupal, drupalSettings);
  