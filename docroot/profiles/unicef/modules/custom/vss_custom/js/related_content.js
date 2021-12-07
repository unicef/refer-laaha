(function ($, Drupal, drupalSettings) {

  'use strict';
  $(document).ready(function () {
    let r = (Math.random() + 1).toString(36).substring(7);
    Drupal.ajax({ url: '/related-content/' + drupalSettings.path.currentPath+'?q=' + r }).execute();
  });

})(jQuery, Drupal, drupalSettings);
