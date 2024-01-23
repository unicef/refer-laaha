(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.signup_next = {
    attach: function (context, settings) {

      // Sign Up form: Prevent going to next page before AJAX load is completed.
      const locationLevel4 = document.getElementById('location-level-4');
      if ((locationLevel4 == null) || (locationLevel4.classList.contains('hidden'))) {
        $( document ).on( "ajaxStart", function() {
          $('#sign-up #intro-text').hide();
          $("#sign-up .signup-next").hide();
        });
        $( document ).on( "ajaxComplete", function() {
          $("#sign-up .signup-next").show();
        });
      }

      // Sign Up form: Trigger next button when enter is pressed on step 2.
      $(context).find('#edit-autocomplete-location').keypress(function (e) {
        if (e.which == 13) {
          // Intercept Enter key press on autcomplete location field.
          e.preventDefault();
          $('#next_button_2').click();
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
