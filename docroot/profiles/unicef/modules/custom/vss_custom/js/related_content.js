(function ($, Drupal, drupalSettings) {

  'use strict';
  $(document).ready(function () {
    const crypto = window.crypto || window.msCrypto;
    var array = new Uint32Array(1);
    let r = crypto.getRandomValues(array); // Compliant for security-sensitive use cases

    var langcode = drupalSettings.langId.split('-') [0];
    Drupal.ajax({ url: '/'+langcode+'/related-content/' + drupalSettings.path.currentPath+'?q=' + r}).execute();
  });

})(jQuery, Drupal, drupalSettings);
