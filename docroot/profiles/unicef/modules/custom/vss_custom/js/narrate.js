/**
 * @file
 * JavaScript for Exit Website.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.narrateJs = {
    attach: function (context, settings) {
      $(document).ready(function () {
        // Add session storage for window
        $('.pop-up').click(function () {
          if (!sessionStorage.getItem('pop-up')) {
            sessionStorage.setItem('pop-up', '1');
          }
        });
        if (!drupalSettings.narrate) {
          return;
        }
        window.speechSynthesis.cancel();
        sessionStorage.setItem('id', '');

        // grab the UI elements to work with
        const playElArticle = document.getElementById('play-article');
        const pauseElArticle = document.getElementById('pause-article');

        // add UI event handlers
        playElArticle.addEventListener('click', playArticle);
        pauseElArticle.addEventListener('click', pauseArticle);
        // set text
        textArticle = drupalSettings.narrate;

        function playArticle(event) {
          if (sessionStorage.getItem('id') != 'play-article') {
            sessionStorage.setItem('id', 'play-article');
            window.speechSynthesis.cancel();
          }
          if (window.speechSynthesis.speaking) {
            // there's an unfinished utterance
            window.speechSynthesis.resume();
          }
          else {
            // start new utterance
            const utteranceArticle = new SpeechSynthesisUtterance(textArticle);
            utteranceArticle.addEventListener('start', handleStartArticle);
            utteranceArticle.addEventListener('pause', handlePauseArticle);
            utteranceArticle.addEventListener('resume', handleResumeArticle);
            utteranceArticle.lang = drupalSettings.langId;
            window.speechSynthesis.speak(utteranceArticle);
          }
        }

        function pauseArticle() {
          window.speechSynthesis.pause();
        }
        function handleStartArticle() {
          pauseElArticle.disabled = true;
          pauseElArticle.disabled = false;
        }

        function handlePauseArticle() {
          pauseElArticle.disabled = false;
          pauseElArticle.disabled = true;
        }

        function handleResumeArticle() {
          pauseElArticle.disabled = true;
          pauseElArticle.disabled = false;
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
