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
          if (!sessionStorage.getItem('pop-up')) {
            sessionStorage.setItem('pop-up', '1');
          }
        });
        window.speechSynthesis.cancel();
        // grab the UI elements to work with
        const playEl = document.getElementById('play');
        const pauseEl = document.getElementById('pause');
        // add UI event handlers
        playEl.addEventListener('click', play);
        pauseEl.addEventListener('click', pause);

        // set text
        text = drupalSettings.disclaimer_narrate;


        function play(event) {
          if (sessionStorage.getItem('id') != 'play') {
            sessionStorage.setItem('id', 'play');
            window.speechSynthesis.cancel();
          }
          if (window.speechSynthesis.speaking) {
            // there's an unfinished utterance
            window.speechSynthesis.resume();
          }
          else {
            // start new utterance
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.addEventListener('start', handleStart);
            utterance.addEventListener('pause', handlePause);
            utterance.addEventListener('resume', handleResume);
            utterance.lang = drupalSettings.disclaimer_landId;
            window.speechSynthesis.speak(utterance);
          }
        }

        function pause() {
          window.speechSynthesis.pause();
        }
        function handleStart() {
          playEl.disabled = true;
          pauseEl.disabled = false;
        }

        function handlePause() {
          playEl.disabled = false;
          pauseEl.disabled = true;
        }

        function handleResume() {
          playEl.disabled = true;
          pauseEl.disabled = false;
        }

      });
    }
  };

})(jQuery, Drupal, drupalSettings);
