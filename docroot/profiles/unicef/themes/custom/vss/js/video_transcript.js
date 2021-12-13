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
                    var transcriptId = $(this).parent().attr("id").split("_")[1];
                    var player = $('#video_' + transcriptId).get(0);
                    player.currentTime = seekToTime;
                    player.play();
                });
                videoTranscriptsCount = $('.show-transcript').length;
                var players = [];
                for (var i = 0; i < videoTranscriptsCount; i++) {
                    var transcriptId = $('.show-transcript')[i].getAttribute("data-id").split("_")[1];
                    players.push($('#video_' + transcriptId).get(0));

                }
                if (players.length > 0) {
                    players.forEach(player => {
                        // Update the progress bar as the video plays.
                        player.addEventListener('timeupdate', function () {
                            var now = player.currentTime;
                            var transcriptId = player.getAttribute("id").split("_")[1];
                            var lines = document.getElementById("transcript_" + transcriptId).getElementsByTagName("div");
                            // Highlight text as video plays.
                            for (var i = 0, l = lines.length; i < l; i++) {
                                if (now >= lines[i].getAttribute("data-start") &&
                                    now <= lines[i].getAttribute("data-end")) {
                                    lineId = lines[i].getAttribute('id');
                                    if (!$("#" + lineId + " > .transcript-text").hasClass("current")) {
                                        $("#" + lineId + " > .transcript-text").addClass("current");
                                        var scrollOffset = $(".current").offset().top - $("#transcript_" + transcriptId).offset().top;
                                        $("#transcript_" + transcriptId).animate({ scrollTop: '+=' + scrollOffset });
                                    }
                                }
                                else {
                                    lineId = lines[i].getAttribute('id');
                                    $("#" + lineId + " > .transcript-text").removeClass("current");
                                }
                            }
                        });
                    });
                }
                // Toggle Transcript.
                $(".transcript-container").hide();
                $(".show-transcript").click(function () {
                    transcriptId = $(this).attr('id').split("_")[1];
                    $("#hide_" + transcriptId).show();
                    $("#show_" + transcriptId).hide();
                    $("#" + transcriptId).show();
                    $("#transcript-container_" + transcriptId).show();
                });
                $(".hide-transcript").click(function () {
                    transcriptId = $(this).attr('id').split("_")[1];
                    $("#hide_" + transcriptId).hide();
                    $("#show_" + transcriptId).show();
                    $("#" + transcriptId).hide();
                    $("#transcript-container_" + transcriptId).hide();
                });

            });
        }
    };

})(jQuery, Drupal, drupalSettings);
