(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.form_select = {
    attach: function (context, settings) {
      if($( "select option:selected").val() == 0) {
        $('select').css('color', '#E0D7D7');
      };
      $('select').on('change', function() {
        if ($(this).children("option:selected").val() == 0) {
        return $(this).css('color', '#E0D7D7');
          } else {
        return $(this).css('color', '#444');
        }
      });
      $("form .form-submit").click(function() {
        $("form input").each(function() {
          if ($(this).val() == "") {
            $(this).css("border", "2px solid red");
          }
        });
        $(".form-select").change(function() {
          if ($(this).children("option:selected").val() == 0) {
            $(this).css("border", "2px solid green");
          }
        })
      });
    }
  };
}(jQuery, Drupal, drupalSettings));
