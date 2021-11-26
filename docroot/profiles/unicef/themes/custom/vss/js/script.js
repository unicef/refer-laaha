(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
     
      /** clone logo change */
      if ($(window).width() < 768) {
        $(".logo-wrapper",context).clone().insertBefore("#block-navigationheadermenu");
        $(".logo-wrapper",context).clone().insertBefore("#block-iraqnavigationheadermenu");
        $("#block-exitwebsiteblock .telephone-header",context).insertBefore(".telephone_in_mobile .holder");
      }
     

    }
  };

  $(document).ready(function() {
    /*to stay menu open after click */
    jQuery('.settings-wrapper .dropdown-menu').on({
      "click":function(e){
          e.stopPropagation();
        }
    });
      
    if(jQuery(window).width() >= 768){
      if(jQuery(".main-container").height()< 500){
        jQuery('#block-globalstickyblock').addClass("absoluteclass");
      }
    function checkOffset() {
      if(jQuery('#block-globalstickyblock').offset().top + jQuery('#block-globalstickyblock').height() >= jQuery('#footer').offset().top ){
        jQuery('#block-globalstickyblock').addClass("absoluteclass");
      }
      if(jQuery(document).scrollTop() + window.innerHeight < jQuery('#footer').offset().top-10)
      jQuery('#block-globalstickyblock').removeClass("absoluteclass"); 
    }
    jQuery(document).scroll(function() {
      checkOffset();
    });
    }
  });

})(jQuery, Drupal, drupalSettings);
