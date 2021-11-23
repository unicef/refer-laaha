/**
 * @file
 * JavaScript for Transcript.
 */

 (function ($, Drupal, drupalSettings) {
    Drupal.behaviors.transcriptJs = {
      attach: function (context, settings) {
        $(document).ready(function () {
            $(document).on('click', '.btnSeek', function() {
                var seekToTime = $(this).data('seek');
                var player = $('#video').get(0);
                player.currentTime = seekToTime;
                player.play();
            });
  
        });
      }
    };
  
  })(jQuery, Drupal, drupalSettings);
