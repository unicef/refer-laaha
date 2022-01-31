/**
 * @file
 * JavaScript for location selection speech Website.
 */

 (function ($, Drupal, drupalSettings) {
  'use strict';
      $(document).ready(function () {

        if (!drupalSettings.location_selector_narrate) {
        return;
        }
        window.speechSynthesis.cancel();

        // grab the UI elements to work with
        const play = document.getElementById('virtual-play');
        const pause = document.getElementById('virtual-pause');
        const resume = document.getElementById('virtual-resume');
        resume.hidden = true;
        pause.hidden = true;
        // add UI event handlers
        play.addEventListener("click", () => {
          var utterance = new SpeechSynthesisUtterance(drupalSettings.location_selector_narrate);
          if (drupalSettings.voiceId) {
            var voices = speechSynthesis.getVoices();
            utterance.voice = voices[drupalSettings.voiceId];
          }
          else {
            utterance.lang = drupalSettings.location_selector_landId;
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
    })(jQuery, Drupal, drupalSettings);
