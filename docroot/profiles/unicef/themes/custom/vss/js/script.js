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
      let countryLocationCookie = getCookie('country-selector');
      if (countryLocationCookie !== "TRUE" && window.location.pathname !== "/country-selector" && window.location.pathname !== "/user/login") {
        window.location.href = "/country-selector";
      }
  });

})(jQuery, Drupal, drupalSettings);
