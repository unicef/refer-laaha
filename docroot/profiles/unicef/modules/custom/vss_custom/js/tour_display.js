/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($, Drupal, drupalSettings) {
    Drupal.behaviors.tour_display = {
      attach: function (context, settings) {
        var skip = Drupal.t('SKIP');
        var finish  = Drupal.t('FINISH');
        window.speechSynthesis.cancel();
        $('.tour-tip-body').before('<img class="supportimg" src="/profiles/unicef/themes/custom/vss/images/support-icon.png" width="64" height="64" alt="support-icon">');
        $('.tour-tip-body').after('<img class="play" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon">'); 
        $('.shepherd-footer button').before('<span class="skip">'+skip+'</span>');
        $('.shepherd-footer button').addClass('button-defult');
        $('.shepherd-footer button').append('<i class="fa fa-angle-right"></i>');
        $('.shepherd-title').hide();
        $('.tour-progress').hide();
        $("body",context).on("click", ".shepherd-button", function(){
           $('.tour-tip-body').before('<img class="supportimg" src="/profiles/unicef/themes/custom/vss/images/support-icon.png" width="64" height="64" alt="support-icon">');
           $('.tour-tip-body').after('<img class="play" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18"  alt="sound-icon">');
           $('.shepherd-footer button').before('<span class="skip">'+skip+'</span>');
           $('.shepherd-footer button').addClass('button-defult');
           $('.shepherd-footer button').append('<i class="fa fa-angle-right"></i>');
           $('.shepherd-title').hide();
           $('.tour-progress').hide();
          });
        
       
        // add UI event handlers
        $("body").on("click", ".play", function(){
          var text = $(this).prev().text();
          var utterance = new SpeechSynthesisUtterance(text);
          if (drupalSettings.voiceId) {
            var voices = speechSynthesis.getVoices();
            utterance.voice = voices[drupalSettings.voiceId];
          }
          else {
            utterance.lang = drupalSettings.langId;
          }
          speechSynthesis.speak(utterance);
        });

        $("body").on("click", ".skip", function(){
          $(".shepherd-cancel-icon").trigger( "click" );
        });
      } 
    };
  
  })(jQuery, Drupal, drupalSettings);
  