/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($) {
    Drupal.behaviors.exitJs = {
      attach: function () {
          $(document).ready(function(){
              $('.exit-website').click(function(){
              // Clear local storage.
              localStorage.clear();
              });
          })
      }
    };
  
  })(jQuery);
  