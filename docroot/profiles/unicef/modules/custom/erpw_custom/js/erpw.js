/**
 * erpw.js
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.catapult_img_preview = {
    attach: function (context, settings) {
        // Redirect user to Manage service type listing page.
        var manage_service_type_url = drupalSettings.erpw_custom.manage_service_type_page;
        $(document).ready(function() {
            $(".page-node-type-service-type .ui-icon-closethick").on("click", function(event){
                event.preventDefault();
                window.location.href = manage_service_type_url;
            });
        });

      // Redirect user to Language selector screen.
      let langCookieSelector = getCookie('userLanguageSelection');
      if (langCookieSelector !== "TRUE" && window.location.pathname !== "/select-language") {
        window.location.href = "/select-language";
      }

      // Set current language.
      let userLanguage = getCookie('userLanguage');
      if (userLanguage !== null && drupalSettings.path.currentLanguage !== userLanguage) {
        drupalSettings.path.currentLanguage = userLanguage;
        drupalSettings.path.pathPrefix = userLanguage;
        window.location.href = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + "/" + drupalSettings.path.currentPath;
      }

      var msg = 'Password should contain one Number,  one letter, one special symbol (min Length 8 Character)';
      $(".help-text").hover(
        function () {
          $(this).append($('<span class="password-help-text">' + msg + '</span>'));
        }, function () {
          $(this).find("span").last().remove();
        }
      );

      redirectClickEvent(".page-node-type-service-type .ui-icon-closethick", drupalSettings.erpw_custom.manage_service_type_page);
      redirectClickEvent(".ui-icon-closethick", "/");
      redirectClickEvent(".path-add-location .ui-icon-closethick", drupalSettings.erpw_location.manage_location);
      redirectClickEvent(".path-update-location .ui-icon-closethick", drupalSettings.erpw_location.manage_location);

      triggerClickEvent(".signin-ok", ".ui-icon-closethick", true);
      triggerClickEvent(".ok-btn", "span.ui-icon-closethick");

      /**
       * Get cookie value.
       */
      function getCookie(name) {
        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
        return match ? match[1] : null;
      }

      /**
       * Redirect on click event.
       */
      function redirectClickEvent(sourceClass, destination = "/") {
        $(sourceClass).on("click", function (event) {
        event.preventDefault();
        window.location.href = destination;
        });
      }

      /**
       * Trigger click event.
       */
      function triggerClickEvent(sourceClass, eventClass, defaultTrigger = false) {
        $(sourceClass).click(function (event) {
        if (defaultTrigger == true) {
            event.preventDefault();
        }
        $(eventClass).click();
        });
      }
    }

    };
}(jQuery, Drupal, drupalSettings));
