(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.form_select = {
    attach: function (context, settings) {
      if($( "select option:selected").val() == 0) {
        $('select').css('color', '#E0D7D7');
      }
      $('select').on('change', function() {
        if ($(this).children("option:selected").val() == 0) {
        return $(this).css('color', '#E0D7D7');
          } else {
        return $(this).css('color', '#444');
        }
      });
      $("form [id^='edit-submit'], form [id^='edit-next']").click(function() {
        $("form input[required='required']").each(function() {
          if ($(this).val() == "") {
            $(this).css("border", "2px solid red");
          } else {
            $(this).css("border", "1px solid #F3C1BF");
          }
        });
        $(".form-select").change(function() {
          if ($(this).children("option:selected").val() == 0 &&  $(this).children("option").val() == 0) {
            $(this).css("border", "2px solid red");
          } else {
            $(this).css("border", "1px solid #F3C1BF");
          }
        })
        $('label.error').parent('.select2-container .select2-selection').css("border", "2px solid red");
      });
    }
  };
}(jQuery, Drupal, drupalSettings));
