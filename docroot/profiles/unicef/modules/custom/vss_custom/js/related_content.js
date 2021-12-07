(function ($, Drupal, drupalSettings) {

  'use strict';
  $(document).ready(function () {
    let r = (Math.random() + 1).toString(36).substring(7);
    Drupal.ajax({ url: '/related-content/9?q=' + r }).execute();
  });

})(jQuery, Drupal, drupalSettings);
