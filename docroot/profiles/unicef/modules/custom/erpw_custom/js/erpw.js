(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {
            var dashboard_url = drupalSettings.erpw_custom.dashboard;
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
                    window.location.href = dashboard_url;
                });
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