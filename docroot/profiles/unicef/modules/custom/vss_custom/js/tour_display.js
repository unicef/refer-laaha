/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($, Drupal, drupalSettings) {
    Drupal.behaviors.tour_display = {
      attach: function (context, settings) {
        
        window.speechSynthesis.cancel();
        $('.tour-tip-body').before('<img alt = icon>');
        $('.tour-tip-body').after('<a class = play>Play</a><br><a class = skip>Skip</a>');
        $('.shepherd-title').hide();
        $('.tour-progress').hide();
        $("body").on("click", ".shepherd-button", function(){
           $('.tour-tip-body').before('<img alt = icon>');
           $('.tour-tip-body').after('<a class = play>Play</a><br><a class = skip>Skip</a');
           $('.shepherd-title').hide();
           $('.tour-progress').hide();
        });
        
        // add UI event handlers
        $("body").on("click", ".play", function(){
          var text = $(this).prev().text();
          var utterance = new SpeechSynthesisUtterance(text);
          var voices = speechSynthesis.getVoices();
          utterance.voice = voices[41];
          speechSynthesis.speak(utterance);
        });

        $("body").on("click", ".skip", function(){
          $(".shepherd-cancel-icon").trigger( "click" );
        });
      } 
    };
  
  })(jQuery, Drupal, drupalSettings);
  