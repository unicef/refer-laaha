(function ($, Drupal, drupalSettings) {
  'use strict';

  $(document).ready(function(){
    /** readmore */
    var readmore = Drupal.t('READ MORE');
     $('#readmore_article').readmore({
      moreLink: '<button id="readmore" class="button-defult read-more">' + readmore + '<i class="fa fa-angle-down" aria-hidden="true"></i></button>',
      collapsedHeight: 1500,
      afterToggle: function (trigger, element, expanded) {
        jQuery('#block-globalstickyblock').removeClass("absoluteclass"); 
        if (!expanded) { // The "Close" link was clicked
          // scroll the sticky to current viewport.
          $('html, body').animate({
            scrollTop: element.offset().top
          }, {
            duration: 100
          });
        }
      }
    });
  });
})(jQuery, Drupal, drupalSettings);
