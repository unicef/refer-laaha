/**
 * @file
 * JavaScript for Exit Website.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exitJs = {
    attach: function (context, settings) {
      var start = new Date(); 
      $(document).ready(function () {

        $('.exit-website-btn').click(function(){
          // Clear local storage.
          window.localStorage.clear();
          window.sessionStorage.clear();
          window.location.replace("/exit-website");
        });

        var interval;
        $(document).on('mousemove keyup keypress',function(){
          clearTimeout(interval);//clear it as soon as any event occurs
          //do any process and then call the function again
          settimeout();//call it again
        })

        function settimeout(){
          interval=setTimeout(function(){
            window.localStorage.clear();
            window.sessionStorage.clear();
            window.location.replace("/exit-website");
          },600000)
        }

        $('#edit-submit-search').hide();
        $('.ui-dialog-buttonpane').hide();

          var end = new Date(); 
          var difference = (end - start) / 1000; 
          if (difference >= 10) {
            $("html").hide();
            var url = window.location.href+"?amp";
            $('body').html("<div style='text-align:center;font-size: 25px;padding-top: 50px;'><a href = "+url+">Switch to lighter version</a></div>");
            $("html").show();
          }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
