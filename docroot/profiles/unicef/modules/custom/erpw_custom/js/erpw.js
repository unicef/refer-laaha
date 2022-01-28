(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {
            var manage_service_type_url = drupalSettings.erpw_custom.manage_service_type_page;
            
            jQuery( ".help-text" ).hover(
                function() {
                  jQuery( this ).append( jQuery( '<span class="password-help-text">Password should contain one Number,  one letter, one special symbol (min Length 8 Character)</span>' ) );
                }, function() {
                  jQuery( this ).find( "span" ).last().remove();
                }
            );
            $(document).ready(function() {
                $(".page-node-type-service-type .ui-icon-closethick").on("click", function(event){
                    event.preventDefault();
                    window.location.href = manage_service_type_url;
                });
            });
            $(".ui-icon-closethick").on("click", function(event){
                event.preventDefault();
                window.location.href = "/";
            });
            $(".path-add-location .ui-icon-closethick").on("click", function(event){
                var manage_location = drupalSettings.erpw_location.manage_location;
                event.preventDefault();
                window.location.href = manage_location;
            });
            $(".path-update-location .ui-icon-closethick").on("click", function(event){
                var manage_location = drupalSettings.erpw_location.manage_location;
                event.preventDefault();
                window.location.href = manage_location;
            });
            $(".signin-ok").on("click", function(event){
                event.preventDefault();
                $(".ui-icon-closethick").click();
            });
            $(".ok-btn").click(function(){
                $("span.ui-icon-closethick").click();
            });
        }
    };
}(jQuery, Drupal, drupalSettings));