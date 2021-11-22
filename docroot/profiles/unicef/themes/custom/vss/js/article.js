(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
      /** readmore */
      var readmore = Drupal.t('READ MORE');
      $('#readmore_article').readmore({
        moreLink: '<button id="readmore" class="button-defult read-more">' + readmore + '<i class="fa fa-angle-down" aria-hidden="true"></i></button>',
        collapsedHeight: 1500,
        afterToggle: function (trigger, element, expanded) {
          if (!expanded) { // The "Close" link was clicked
            $('html, body').animate({
              scrollTop: element.offset().top
            }, {
              duration: 100
            });
          }
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
