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

    }
  };

})(jQuery, Drupal, drupalSettings);
/*to stay menu open after click */
jQuery('.settings-wrapper .dropdown-menu').on({
  "click":function(e){
      e.stopPropagation();
    }
});