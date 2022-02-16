(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.location = {
        attach: function (context, settings) {
            $(document).ready(function() {
                $(".path-delete-location .ui-icon-closethick").on("click", function(event){
                    event.preventDefault();
                    window.location.href = drupalSettings.erpw_location.redirect_to_manage;
                });
            });
        }
    };
}(jQuery, Drupal, drupalSettings));
