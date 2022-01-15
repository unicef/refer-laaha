(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {
            var manage_locatioon_url = drupalSettings.erpw_location.manage_location_page;
            $(document).ready(function() {
                $(".path-delete-location .ui-icon-closethick").on("click", function(event){
                    event.preventDefault();
                    window.location.href = manage_locatioon_url;
                });
            });
        }
    };
}(jQuery, Drupal, drupalSettings));