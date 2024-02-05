(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.signup_browser_control = {
        attach: function (context, settings) {
            $(window).on('load', function() {
                window.location.hash = drupalSettings.formSettings.step;
                window.onbeforeunload = function(event) {
                    return event.returnValue = "Your work will be lost."; 
                };
                $("input:text, select").first().focus();
            });
            $('input[type=submit]').click(function (e) {
                window.onbeforeunload = function () {};
            });
        }
    };
})(jQuery, Drupal, drupalSettings);