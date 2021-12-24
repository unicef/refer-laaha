(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {
            jQuery( ".help-text" ).hover(
                function() {
                  jQuery( this ).append( jQuery( '<span class="password-help-text">Password should contain at least one Number, one Symbol and one alphabet</span>' ) );
                }, function() {
                  jQuery( this ).find( "span" ).last().remove();
                }
            );
            $(".ui-icon-closethick").on("click", function(event){
                event.preventDefault();
                window.location.href = "/";
            });
            $(".signin-ok").on("click", function(event){
                event.preventDefault();
                $(".ui-icon-closethick").click();
            });
        }
    };
}(jQuery, Drupal, drupalSettings));