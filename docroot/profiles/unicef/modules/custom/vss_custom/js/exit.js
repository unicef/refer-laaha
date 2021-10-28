/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($) {
  Drupal.behaviors.exitJs = {
    attach: function (context, settings) {
      $(document).ready(function () {  
        
            $('.exit-website-btn').click(function(){
            // Clear local storage.
            window.localStorage.clear();
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
                window.location.replace("/exit-website");
              },100000)
            }
      });      
    }
  };

})(jQuery);