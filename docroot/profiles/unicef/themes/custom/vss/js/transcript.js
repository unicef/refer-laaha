/**
 * @file
 * JavaScript for Transcript.
 */

(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.transcriptJs = {
        attach: function (context, settings) {
            $(document).ready(function () {
                $(document).on('click', '.btnSeek', function () {
                    var seekToTime = $(this).data('seek');
                    var player = $('#video').get(0);
                    player.currentTime = seekToTime;
                    player.play();
                });
                // Highlight the transcript based on the video time.
                var lines = document.getElementById("transcript").getElementsByTagName("div");
                var player = $('#video').get(0);

                // Update the progress bar as the video plays
                player.addEventListener('timeupdate', function () {
                    var now = player.currentTime;

                    // highlight text as video plays
                    for (var i = 0, l = lines.length; i < l; i++) {
                        if (now >= lines[i].getAttribute("data-start") &&
                            now <= lines[i].getAttribute("data-end")) {
                            lineId = lines[i].getAttribute('id');
                            $("#" + lineId + " > .transcript-text").addClass("current");
                        }
                        else {
                            lineId = lines[i].getAttribute('id');
                            $("#" + lineId + " > .transcript-text").removeClass("current");
                        }
                    }
                });
                // Toggle Transcript.
                $(".show-transcript").click(function(){
                    $(".hide-transcript").show();
                    $(".show-transcript").hide();
                    $("#transcript").show();      
                });
                $(".hide-transcript").click(function(){
                    $(".show-transcript").show();
                    $(".hide-transcript").hide();
                    $("#transcript").hide();  
                });

            });
        }
    };

})(jQuery, Drupal, drupalSettings);
