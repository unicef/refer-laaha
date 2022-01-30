/**
 * @file
 * JavaScript for Exit Website.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.disclaimer = {
    attach: function (context, settings) {
      $(document).ready(function () {
        // Add session storage for window
        $('.pop-up').click(function () {
          $('.overlay-popup').hide();
          if (!sessionStorage.getItem('pop-up')) {
            sessionStorage.setItem('pop-up', '1');
          }
          $.cookie('disclaimer', '1');
        });
        if (!drupalSettings.disclaimer_narrate) {
        return;
        }
        window.speechSynthesis.cancel();

        // grab the UI elements to work with
        const play = document.getElementById('play');
        const pause = document.getElementById('pause');
        const resume = document.getElementById('resume');
        resume.hidden = true;
        pause.hidden = true;
        // add UI event handlers
        play.addEventListener("click", () => {
          var utterance = new SpeechSynthesisUtterance(drupalSettings.disclaimer_narrate);
          if (drupalSettings.voiceId) {
            var voices = speechSynthesis.getVoices();
            utterance.voice = voices[drupalSettings.voiceId];
          }
          else {
            utterance.lang = drupalSettings.disclaimer_landId;
          }
          play.hidden = true;
          resume.hidden = true;
          pause.hidden = false;
          speechSynthesis.speak(utterance);
          utterance.addEventListener('end', function (event) {
            speechSynthesis.cancel();
            play.hidden = false;
            resume.hidden = true;
            pause.hidden = true;
          });
        });

        pause.addEventListener("click", () => {
          speechSynthesis.pause();
          play.hidden = true;
          pause.hidden = true;
          resume.hidden = false;
        });

        resume.addEventListener("click", () => {
          // data.innerText = "resume";
          speechSynthesis.resume();
          play.hidden = true;
          resume.hidden = true;
          pause.hidden = false;
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
