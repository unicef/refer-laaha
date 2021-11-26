(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
     
      /** clone logo change */
      if ($(window).width() < 768) {
        $(".logo-wrapper").clone().insertBefore("#block-navigationheadermenu");
        $(".logo-wrapper").clone().insertBefore("#block-iraqnavigationheadermenu");
        $("#block-exitwebsiteblock .telephone-header").insertBefore(".telephone_in_mobile .holder");
      }
      if($(window).width() >= 768){
       if($(".main-container").height()< 500){
        $('#block-globalstickyblock').addClass("absoluteclass");
       }
      function checkOffset() {
        if($('#block-globalstickyblock').offset().top + $('#block-globalstickyblock').height() >= $('#footer').offset().top ){
          $('#block-globalstickyblock').addClass("absoluteclass");
        }
        if($(document).scrollTop() + window.innerHeight < $('#footer').offset().top-10)
          $('#block-globalstickyblock').removeClass("absoluteclass"); 
      }
      $(document).scroll(function() {
        checkOffset();
      });
      }

    }
  };

})(jQuery, Drupal, drupalSettings);
/*to stay menu open after click */
jQuery('.settings-wrapper .dropdown-menu').on({
  "click":function(e){
      e.stopPropagation();
    }
});
