(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.catapult_img_preview = {
        attach: function (context, settings) {

            // Redirect user to Language selector screen.
            let langCookieSelector = getCookie('userLanguageSelection');
            if (langCookieSelector !== "TRUE" && window.location.pathname !== "/select-language") {
                window.location.href = "/select-language";
            }

            /**
             * Get cookie value.
             */
            function getCookie(name) {
                function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
                var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
                return match ? match[1] : null;
            }

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
                    window.location.href = drupalSettings.erpw_custom.manage_service_type_page;
                });
            });
            $(".ui-icon-closethick").on("click", function(event){
                event.preventDefault();
                window.location.href = "/";
            });
            $(".signin-ok").on("click", function(event){
                event.preventDefault();
                $(".ui-icon-closethick").click();
            });
            $(".ok-btn").click(function(){
                $("span.ui-icon-closethick").click();
            });
            // set Localstorage and remove Localstorage when browser close.
            if(sessionStorage.getItem('signinPopup')){
                $('.sign-in-popup, .overlay').hide();
            }
            $(".path-frontpage, .skip, .sign-in").on('click', function(){
                window.sessionStorage.signinPopup = "true";
            });
            $(window).on("unload", function(){
                // Clear the session storage
                window.signinPopup.clear()
            });
            
            //Add select 2 for multiselect
            $('.form-select[multiple=multiple], .form-select.add_multiple').select2();
            
            //Home page country field
            if ($('.user-location').text().length > 0) {
                $('.user-location').addClass('location-theme');
            }
        }
    };
}(jQuery, Drupal, drupalSettings));
