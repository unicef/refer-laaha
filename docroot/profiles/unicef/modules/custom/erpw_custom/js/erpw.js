(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {
            jQuery( ".help-text" ).hover(
                function() {
                  jQuery( this ).append( jQuery( '<span class="password-help-text">Password should contain one Number,  one letter, one special symbol (min Length 8 Character)</span>' ) );
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