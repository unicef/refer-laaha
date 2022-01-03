(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.carousal = {
    attach: function (context, settings) {
      if ($(window).width() < 768 && $(".category-item").length > 2) {
        $('#carousel').lightSlider({
          item:4,
          loop:true,
          slideMove:1,
          easing: 'cubic-bezier(0.25, 0, 0.25, 1)',
          speed:600,
          pager: false,
          responsive : [
            {
              breakpoint:800,
              settings: {
                item:6,
                slideMove:1,
                slideMargin:6,
              }
            },
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
    }
  };
})(jQuery, Drupal, drupalSettings);
