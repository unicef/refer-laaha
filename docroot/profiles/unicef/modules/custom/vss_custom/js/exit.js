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

        // grab the UI elements to work with
        const textEl = document.getElementById('text');
        const playEl = document.getElementById('play');
        const pauseEl = document.getElementById('pause');
        const stopEl = document.getElementById('stop');


        // add UI event handlers
        playEl.addEventListener('click', play);
        pauseEl.addEventListener('click', pause);

        // set text
        text = drupalSettings.narrate;

        function play() {
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
            utterance.addEventListener('end', handleEnd);
            window.speechSynthesis.speak(utterance);
          }
        }

        function pause() {
          window.speechSynthesis.pause();
        }

        function stop() {
          window.speechSynthesis.cancel();

          // Safari doesn't fire the 'end' event when cancelling, so call handler manually
          handleEnd();
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

        function handleEnd() {
          playEl.disabled = false;
          pauseEl.disabled = true;

          // reset text to remove mark
          text = drupalSettings.narrate;
        }

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
          },600000)
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
