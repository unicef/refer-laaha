/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($, Drupal, drupalSettings) {
    Drupal.behaviors.tour_display = {
      attach: function (context, settings) {
        $('body', context).once('tour_display').each(function () {
          window.speechSynthesis.cancel();
          var skip = Drupal.t('SKIP');
          var finish  = Drupal.t('FINISH');
          $('.tour-tip-body').before('<img class="supportimg" src="/profiles/unicef/themes/custom/vss/images/support-icon.png" width="64" height="64" alt="support-icon">');
          $('.tour-tip-body').after('<div class="pause-wrapper" style=display:none;cursor:pointer><img class="pause" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon" ></div >');
          $('.tour-tip-body').after('<div class="resume-wrapper" style=display:none;cursor:pointer;><img class="resume" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon"></div>');
          $('.tour-tip-body').after('<div class="play-wrapper"><img class="play" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon"></div>');
          $('.shepherd-footer button').before('<span class="skip">'+skip+'</span>');
          $('.shepherd-footer button').addClass('button-defult');
          $('.shepherd-footer button').append('<i class="fa fa-angle-right"></i>');
          $('.shepherd-title').hide();
          $('.tour-progress').hide();
          $('.button-defult:contains("End tour")').prepend('<span class=finish>'+finish+'</span>');  
          if($('.button-defult:contains("End tour")').length > 0){        
            $('.finish').get(0).nextSibling.remove();
            $(".shepherd-cancel-icon").trigger( "click" );
          }
          $("body",context).on("click", ".shepherd-button", function(){
             window.speechSynthesis.cancel();
             $('.tour-tip-body').before('<img class="supportimg" src="/profiles/unicef/themes/custom/vss/images/support-icon.png" width="64" height="64" alt="support-icon">');
             $('.tour-tip-body').after('<div class="pause-wrapper" style=display:none;cursor:pointer;><img class="pause" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon" ></div>');
             $('.tour-tip-body').after('<div class="resume-wrapper" style=display:none;cursor:pointer;><img class="resume" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon"></div>');
             $('.tour-tip-body').after('<div class="play-wrapper"><img class="play" src="/profiles/unicef/themes/custom/vss/images/sound-icon.png" height="18" width="18" alt="sound-icon"></div>');
             $('.shepherd-footer button').before('<span class="skip">'+skip+'</span>');
             $('.shepherd-footer button').addClass('button-defult');
             $('.shepherd-footer button').append('<i class="fa fa-angle-right"></i>');
             $('.shepherd-title').hide();
             $('.tour-progress').hide();
            if($('.button-defult:contains("End tour")').length > 0){   
             $('.button-defult:contains("End tour")').prepend('<span class=finish>'+finish+'</span>');
             $('.finish').get(0).nextSibling.remove();
             $('.finish').click( function () {
              $(".shepherd-cancel-icon").trigger( "click" );
             });
            }
          });

          $('.pause-wrapper').hide();
          $('.resume-wrapper').hide();
          // add UI event handlers
          $("body").on("click", ".play-wrapper", function(){
            var text = $(this).prev().text();
            var utterance = new SpeechSynthesisUtterance(text);
            if (drupalSettings.voiceId) {
              var voices = speechSynthesis.getVoices();
              utterance.voice = voices[drupalSettings.voiceId];
            }
            else {
              utterance.lang = drupalSettings.langId;
            }

            $('.play-wrapper').hide();
            $('.pause-wrapper').show();
            $('.resume-wrapper').hide();
            speechSynthesis.speak(utterance);
            utterance.addEventListener('end', function (event) {
              speechSynthesis.cancel();
              $('.play-wrapper').show();
              $('.pause-wrapper').hide();
              $('.resume-wrapper').hide();
            });
          });

          $("body").on("click", ".pause-wrapper", function(){
            $('.play-wrapper').hide();
            $('.pause-wrapper').hide();
            $('.resume-wrapper').show();
            speechSynthesis.pause();
          });

          $("body").on("click", ".resume-wrapper", function(){
            $('.play-wrapper').hide();
            $('.resume-wrapper').hide();
            $('.pause-wrapper').show();
            speechSynthesis.resume();
          });

          $(".shepherd-cancel-icon").click(function () {
            removeQuery();
            window.speechSynthesis.cancel();
          });
          $("body").on("click", ".skip", function(){
            $(".shepherd-cancel-icon").trigger( "click" );
            window.speechSynthesis.cancel();
          });
       });
       function removeQuery() {
        var url = window.location.href;
        url = url.split('?')[0];
        window.history.pushState({}, document.title, url);
       }
      } 
    };
  
  })(jQuery, Drupal, drupalSettings);
