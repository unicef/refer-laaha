/**
 * @file
 * JavaScript for Exit Website.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.narrateJs = {
    attach: function (context, settings) {
      $(document).ready(function () {
        // Add session storage for window.
        $('.pop-up').click(function () {
          if (!sessionStorage.getItem('pop-up')) {
            sessionStorage.setItem('pop-up', '1');
          }
        });
        if (!drupalSettings.narrate) {
          return;
        }
        window.speechSynthesis.cancel();
        // Grab the UI elements to work with.
        const play = document.getElementById('play-article');
        const pause = document.getElementById('pause-article');
        const resume = document.getElementById('resume-article');
        resume.hidden = true;
        pause.hidden = true;
        // Add UI event handlers.
        play.addEventListener("click", () => {
          var utterance = new SpeechSynthesisUtterance(drupalSettings.narrate);
          if (drupalSettings.voiceId) {
            var voices = speechSynthesis.getVoices();
            utterance.voice = voices[drupalSettings.voiceId];
          }
          else {
            utterance.lang = drupalSettings.langId;
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
          speechSynthesis.resume();
          play.hidden = true;
          resume.hidden = true;
          pause.hidden = false;
        });

      });
    }
  };

})(jQuery, Drupal, drupalSettings);
