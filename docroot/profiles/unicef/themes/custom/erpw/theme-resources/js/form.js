(function ($, Drupal, drupalSettings) {
  'use strict';

  $(document).ready(function(){
    $('select').on('change', function() {
      if ($(this).val()) {
      return $(this).css('color', '#444');
        } else {
      return $(this).css('color', '#444');
      }
    });
  });
})(jQuery, Drupal, drupalSettings);
