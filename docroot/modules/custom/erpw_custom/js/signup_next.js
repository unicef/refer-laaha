(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.catapult_img_preview = {
    attach: function (context, settings) {

      // Sign Up form: Prevent going to next page before AJAX load is completed.
      $( document ).on( "ajaxStart", function() {
        $('#sign-up #intro-text').hide();
        $("#sign-up .signup-next").hide();
      });
      $( document ).on( "ajaxComplete", function() {
        $("#sign-up .signup-next").show();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
