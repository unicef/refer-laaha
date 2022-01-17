(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.location = {
        attach: function (context, settings) {
            $(".path-delete-location .ui-icon-closethick").on("click", function(event){
                event.preventDefault();
                window.location.href = document.referrer;
            });
        }
    };
}(jQuery, Drupal, drupalSettings));