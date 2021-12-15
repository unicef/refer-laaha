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

    if (jQuery(window).width() >= 768) {
      if (jQuery(".main-container").height() < 500) {
        jQuery('#block-globalstickyblock').addClass("absoluteclass");
      }

      function checkOffset() {
        if (jQuery('#block-globalstickyblock').offset().top + jQuery('#block-globalstickyblock').height() >= jQuery('#footer').offset().top) {
          jQuery('#block-globalstickyblock').addClass("absoluteclass");
        }
        if (jQuery(document).scrollTop() + window.innerHeight < jQuery('#footer').offset().top - 10)
          jQuery('#block-globalstickyblock').removeClass("absoluteclass");
      }
      jQuery(document).scroll(function () {
        checkOffset();
      });
    }
  });

})(jQuery, Drupal, drupalSettings);
