(function ($, Drupal, drupalSettings) {
  'use strict';
  $(document).ready(function(){
    var pass = drupalSettings.password;
    var passname;
    if (pass) {
      $("html").hide();
      setTimeout(function () {
        while(!passname){
          passname =prompt('Enter password');
          if(passname == pass) {
            $("html").show();
          }
          if((passname != '' && passname != null) && passname != pass) {
            location.reload();
          }
        }
      }, 5);
    }
  });
})(jQuery, Drupal, drupalSettings);
