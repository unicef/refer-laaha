(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.carousal = {
    attach: function (context, settings) {
      var isRtl = jQuery("html[lang=ar]").length ? true : false;
      if ($(window).width() < 768 && $(".category-item").length > 2) {

        $('#carousel').lightSlider({
          item:4,
          loop:true,
          slideMove:1,
          easing: 'cubic-bezier(0.25, 0, 0.25, 1)',
          speed:600,
          pager: false,
          rtl: isRtl,
          useCSS: true,
          responsive : [
            {
              breakpoint:480,
              settings: {
                item:3,
                slideMove:1
              }
            }
          ]
      });
      }
      if ($(window).width() > 1024) {
        $(".category-item").on({
          mouseenter: function () {
            $(this).find(".category-thumbnail").css({"opacity": 0, "visibility": "hidden"});
            $(this).find(".hover-show").removeClass('hidden');
          },
          mouseleave: function () {
              $(this).find(".category-thumbnail").css({"opacity": 1, "visibility": "visible"});
              $(this).find(".hover-show").addClass('hidden');
          }
        });
      }
      $('#aboutUsCarousel').lightSlider({
        item: 3,
        autoWidth: false,
        slideMove: 1, // slidemove will be 1 if loop is true
        slideMargin: 10,

        addClass: '',
        mode: "slide",
        useCSS: true,
        cssEasing: 'ease', //'cubic-bezier(0.25, 0, 0.25, 1)',//
        easing: 'linear', //'for jquery animation',////

        speed: 400, //ms'
        auto: false,
        loop: false,
        slideEndAnimation: true,
        pause: 2000,

        keyPress: false,
        controls: false,
        prevHtml: '',
        nextHtml: '',

        rtl:isRtl,
        adaptiveHeight:false,

        vertical:false,
        verticalHeight:500,
        vThumbWidth:100,

        thumbItem:10,
        pager: true,
        gallery: false,
        galleryMargin: 5,
        thumbMargin: 5,
        currentPagerPosition: 'middle',

        enableTouch:true,
        enableDrag:true,
        freeMove:true,
        swipeThreshold: 40,

        responsive : [
          {
            breakpoint:768,
            settings: {
              item:1,
              slideMove:1
            }
          }
        ],

        onBeforeStart: function (el) {},
        onSliderLoad: function (el) {},
        onBeforeSlide: function (el) {},
        onAfterSlide: function (el) {},
        onBeforeNextSlide: function (el) {},
        onBeforePrevSlide: function (el) {}
    });
    }
  };
})(jQuery, Drupal, drupalSettings);
