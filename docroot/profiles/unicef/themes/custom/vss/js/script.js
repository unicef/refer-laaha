(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {

      /** clone logo change */
      if ($(window).width() < 768) {
        $(".logo-wrapper", context).clone().insertBefore("#block-navigationheadermenu");
        $(".logo-wrapper", context).clone().insertBefore("#block-iraqnavigationheadermenu");
        $("#block-exitwebsiteblock .telephone-header", context).insertBefore(".telephone_in_mobile .holder");
      }
       $('.paragraph--type--wysiwyg-editor table').addClass('table table-bordered') ;

      //search popup global
      $( ".searchbox-icon" ).on( "click", function() {
        $('.global-sticky-region').css('z-index', 102);
      });
      $(".form-type-search-api-autocomplete input").attr("placeholder", "Type here to search");

      // Audio support info modal
      const audioInfoModal = $(".audio-info-pop-up");
      if (audioInfoModal.length) {
        $("#audioInfo i").click(function () {
          audioInfoModal.removeClass('hidden');
        });
        $(".audio-info-pop-up .close-icon").on("click", function (e) {
          audioInfoModal.addClass('hidden');
        });
        audioInfoModal.on("click", function (e) {
          e.stopPropagation();
        });
        // close modal on ouside click
        $("body").click(function() {
          if (audioInfoModal.is(":visible")) {
            audioInfoModal.addClass('hidden');
          }
        });
      }

      let language = $('html')[0].lang;
      let key = "show_subtitle_" + language;
      if (localStorage.getItem(key) === 'true') {
        $(".settings-wrapper .last input").prop('checked', true);
          processSubtitle(true);
      } else {
        processSubtitle(false);
      }

    // enable disable subtitle settings
      $(".settings-wrapper .last input").change(function() {
        localStorage.setItem(key,
                  $(this).is(":checked"),
                  {expires: 30, path:'/'},
        );
        processSubtitle($(this).is(":checked"));
      });

      function processSubtitle(b) {
        const video = document.querySelectorAll("video");
        let language_matched = 0;
        let indexOfEnglish = 0;
        if( video.length ) {
          for (let item of video) {
            if (b === true) {
              for (let i = 0; i<item.textTracks.length; i++) {
                item.textTracks[i].mode = "disabled";
                if (item.textTracks[i].language === language ) {
                  language_matched = 1;
                  item.textTracks[i].mode = "showing"
                }
                if (item.textTracks[i].language === 'en') {
                  indexOfEnglish = i;
                }
              }

              if (language_matched !== 1) {
                item.textTracks[indexOfEnglish].mode = "showing";
              }
            } else {
              for (let vtt of item.textTracks) {
                vtt.mode = "disabled"
              }
            }
          }
        }
      }
    }
  };

  $(document).ready(function () {
    /*to stay menu open after click */
    jQuery('.settings-wrapper .dropdown-menu').on({
      "click": function (e) {
        e.stopPropagation();
      }
    });
    $('.carousel .left').hide()
    $('.carousel').each(function (index) {
      $(this).on('slid.bs.carousel', '', function () {
        var $this = $(this);
        if ($this.find(".carousel-inner .item:first").hasClass("active")) {
          $this.children(".left").hide();
          $this.children(".right").show();
        } else if ($this.find(" .carousel-inner .item:last").hasClass("active")) {
          $this.children(".right").hide();
          $this.children(".left").show();
        } else {
          $this.children(".carousel-control").show();
        }
      });
    });

    // location selector page redirection
      /**
       * Get cookie value.
       */
      function getCookie(name) {
        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
        return match ? match[1] : null;
      }

      // Redirect user to Language selector screen.
      let countryLocationCookie = getCookie('country-location-selector');
      if (countryLocationCookie !== "TRUE" && window.location.pathname !== "/country-selector" && window.location.pathname !== "/user/login") {
        window.location.href = "/country-selector";
      }
  });

  $(document).ready(function() {
    if (jQuery('form').get(0)) {
      jQuery('form').get(0).reset(); //clear form data on page load
    }
  });

})(jQuery, Drupal, drupalSettings);
