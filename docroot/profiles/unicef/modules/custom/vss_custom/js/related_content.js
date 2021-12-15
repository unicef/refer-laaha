(function ($, Drupal, drupalSettings) {

  'use strict';
  $(document).ready(function () {
    let r = (Math.random() + 1).toString(36).substring(7);
    var langcode = drupalSettings.langId.split('-') [0];
    if(langcode == 'en'){
      Drupal.ajax({ url: '/related-content/' + drupalSettings.path.currentPath+'?q=' + r }).execute();
    }else{
      Drupal.ajax({ url: '/'+langcode+'/related-content/' + drupalSettings.path.currentPath+'?q=' + r }).execute();
    }
  });

})(jQuery, Drupal, drupalSettings);
