/**
 * @file
 * JavaScript for Exit Website.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exitJs = {
    attach: function (context, settings) {
      $(document).ready(function () {
        // Add session storage for window
        $('.pop-up').click(function(){
          if (!sessionStorage.getItem('pop-up')) {
            sessionStorage.setItem('pop-up', '1');
          }
        });
        // Audio naration.
        var msg = new SpeechSynthesisUtterance();
        speechSynthesis.cancel();
        $('.btn-narrate').click(function () {
          msg.lang = drupalSettings.langId;
          msg.text = drupalSettings.narrate;//"مرحبا صباح الخير"; //jQuery(".pop-up-speech").text();
          window.speechSynthesis.speak(msg);
          // event after text has been spoken.
          msg.onend = function () {
            speechSynthesis.cancel();
          }
        });
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
              },300000)
            }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
