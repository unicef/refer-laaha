(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.catapult_img_preview = {
    attach: function (context, settings) {
      //Home page guideline link add attr
      $('.field--name-field-upload-gbv-referrals-guide .file--application-pdf a').attr('target', '_blank');

      // Ignore select language page for Sierra leone domain.
      if (window.location.hostname.includes('sl') && window.location.pathname == "/") {
        // Redirect to en page for Sierra leone domain.
        window.location.href = '/en';
      }
      else if (window.location.hostname.includes('sl') && window.location.pathname == "/select-language") {
        // Redirect to en page for Sierra leone domain.
        window.location.href = '/en';
      }
      else if (!window.location.hostname.includes('sl')) {
        // Redirect user to Language selector screen for all other domains.
        let langCookieSelector = getCookie('userLanguageSelection');
        if (langCookieSelector !== "TRUE" && window.location.pathname !== "/select-language") {
          window.location.href = "/select-language";
        }
      }

      // Sierra Leone domain handler code.
      if (window.location.hostname.includes('sl') && window.location.pathname == "/select-language") {
      window.location.href = '/en';
      } else if (window.location.pathname == "/select-language") {
        const countryList = document.getElementById('country-dropdown').children;
        let activeCountry = null;
        // Get the active country.
        for (let i = 0; i < countryList.length; i++) {
          if (countryList[i].getAttribute('selected') != null && countryList[i].getAttribute('selected').length > 0) {
            activeCountry = countryList[i].attributes.value.nodeValue;
          }
        }
        // If the active country is Sierra Leone then remove the language selector.
        if ((activeCountry != null && activeCountry.includes('sl')) || (window.location.hostname.includes('sl') && activeCountry.includes('sl'))) {
          document.getElementsByClassName('choose-language-text')[0].style.display = 'none';
          document.getElementsByClassName('form-radios')[0].style.display = 'none';
        }
      }

      const subDomain = window.location.hostname.split(".")[0];
      if (subDomain != null && subDomain.length < 3) {
        if (document.referrer != null && !document.referrer.includes(subDomain) && window.location.hostname.includes(subDomain) && subDomain == "sl") {
          window.location.reload();
        }
      }
      
      /**
       * Get cookie value.
       */
      function getCookie(name) {
        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
        return match ? match[1] : null;
      }

      /**
       * Set cookie value.
       */
      function setCookie(name, value, expirationDays) {
        var date = new Date();
        date.setTime(date.getTime() + (expirationDays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + "; " + expires + "; path=/";
      }

      // Password Suggestions and check
        var $passwordField = $('#edit-password-pass1');
        var $suggestionsElement = $('#password-suggestions-check');
        $passwordField.on('keyup', function() {
          var password = $passwordField.val();
          var suggestions = [];
          if (!password.match(/^(?=.*[a-z])/)) {
            suggestions.push('Please add at least 1 lowercase character');
          }
          if (!password.match(/^(?=.*[A-Z])/)) {
            suggestions.push('Please add at least 1 uppercase character');
          }
          if (!password.match(/^(?=.*\d)/)) {
            suggestions.push('Please add at least 1 numeric character');
          }
          if (!password.match(/^(?=.*[@$!%*#?&])/)) {
            suggestions.push('Please add at least 1 special character, eg * & # %');
          }
          if (password.length < 8) {
            suggestions.push('Minimum of 8 characters are required');
          }

          var $suggestionsContainer = $suggestionsElement;
          $suggestionsContainer.empty();
          if (suggestions.length > 0) {
            $suggestionsContainer.addClass('show');
            var $ul = $('<ul>');
            suggestions.forEach(function(suggestion) {
              var $li = $('<li>').text(suggestion);
              $ul.append($li);
            });
            var $span = $('<span>');
            $span.text('Recommendations to make your password stronger:');
            $suggestionsContainer.append($span);
            $suggestionsContainer.append($ul);
            $suggestionsContainer.css('display', 'block');
          } else {
            $suggestionsContainer.css('display', 'none');
          }

          // Remove individual suggestions as criteria are met
          var $suggestionItems = $suggestionsContainer.find('li');
          $suggestionItems.each(function() {
            var suggestion = $(this).text();
            if (password.match(suggestion.replace(/^Please add /, ''))) {
              $(this).remove();
            }
          });
        });

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


      let signInPopUpCookies = getCookie('sign_in_popup');
      if (signInPopUpCookies != null) {
        $('.sign-in-popup, .overlay').hide();
      }

      let signInPopUpButton = document.getElementById("edit-skip");
      if (signInPopUpButton != null) {
        signInPopUpButton.addEventListener("click", function() {
          setCookie('sign_in_popup', 'agreed', 30);
        });
      }

      // set Localstorage and remove Localstorage when browser close.
      // if(sessionStorage.getItem('signinPopup')){
      //   $('.sign-in-popup, .overlay').hide();
      // }
      // $(".path-frontpage, .skip, .sign-in").on('click', function(){
      //   window.sessionStorage.signinPopup = "true";
      // });
      // $(window).on("unload", function(){
      //   // Clear the session storage
      //   window.signinPopup.clear()
      // });

      //Add select 2 for multiselect
      $('.form-select.add_multiple').attr('multiple','multiple');
      $('.form-select').select2();

      //Home page country field
      if ($('.user-location').text().length > 0) {
        $('.user-location').addClass('location-theme');
      }
      /** Sign UP form country check Start **/
      $("#sign-up .form-item-level-0 > label").text(Drupal.t("Country"));
      if ($("#sign-up #edit-level-0").val() == '') {
        $("#sign-up .signup-next").hide();
      }
      else {
        $('#sign-up #intro-text').hide();
      }
      $("#sign-up #edit-level-0").on("change", function() {
        if ($(this).val() != '') {
          $('#sign-up #intro-text').hide();
          $("#sign-up .signup-next").show();
        }
        else {
          $('#sign-up #intro-text').show();
          $("#sign-up .signup-next").hide();
        }
      });
       /** Sign UP form country check End **/

       //Add condition for border under view header
       var icon_button = $('.button-with-icon').length;
       if ($(icon_button).length > 0) {
          $('.view-header').css('border-bottom', '2px solid #f3c1bf87');
       } else {
          $('.view-header').css('border-bottom', 0);
       }
    }
  };

  Drupal.behaviors.disableCountrySelector = {
    attach: function (context, settings) {
      if ($('select.disable-country-select-default', context).length > 0) {
        var userRoles = drupalSettings.erpw_custom.userRoles;
        if (userRoles) {
          $('select.disable-country-select-default')
          .parent()
          .addClass('admin-user');
        }
        else {
          $('select.disable-country-select-default')
          .parent()
          .addClass('disable-country-select-default');
        }
      }

      // Broadcast predefined messages vocabulary changes.
      var currentPath = window.location.pathname;
      if (currentPath == '/en/admin/structure/taxonomy/manage/predefined_broadcast_messages/overview') {
        $('.tabledrag-toggle-weight-wrapper').hide();
        $('.action-links li a').text('Add Predefined Message');
      }
    },
  };

})(jQuery, Drupal, drupalSettings);
