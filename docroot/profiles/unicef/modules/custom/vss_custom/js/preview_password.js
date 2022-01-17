(function ($, Drupal, drupalSettings) {
  'use strict';
  $(document).ready(function(){
    var pass = drupalSettings.password;
      if (pass) {
        $("html").hide();
        setTimeout(function () {
          while(!passname){
            var passname =prompt('Enter password');
            if(passname == pass) {
              $("html").show();
            }
            if((passname != '' && passname != null) && passname != pass) {
              location.reload();
            }
          }  
        }, 5);
      }

      //search pooup global sticky z-index increased
      $( ".searchbox-icon" ).on( "click", function() {
          $('.global-sticky-region').css('z-index', 102);
      });
      $(".form-type-search-api-autocomplete input").attr("placeholder", "Type here to search");

  });
})(jQuery, Drupal, drupalSettings);
