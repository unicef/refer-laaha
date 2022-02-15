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
                    var player = $('#audio_' + transcriptId).get(0);
                    player.currentTime = seekToTime;
                    player.play();
                });
                let podcastTranscriptsCount = $('.show-podcast-transcript').length;
                var podcastPlayers = [];
                for (var i = 0; i < podcastTranscriptsCount; i++) {
                    var transcriptId = $('.show-podcast-transcript')[i].getAttribute("data-id").split("_")[1];
                    podcastPlayers.push($('#audio_' + transcriptId).get(0));

                }
                if (podcastPlayers.length > 0) {
                    podcastPlayers.forEach(player => {
                        // Update the progress bar as the video plays
                        player.addEventListener('timeupdate', function () {
                            var now = player.currentTime;
                            var transcriptId = player.getAttribute("id").split("_")[1];
                            var lines = document.getElementById("podcast-transcript_" + transcriptId).getElementsByTagName("div");
                            // Highlight text as video plays.
                            for (var i = 0, l = lines.length; i < l; i++) {
                                if (now >= lines[i].getAttribute("data-start") &&
                                    now <= lines[i].getAttribute("data-end")) {
                                    let lineId = lines[i].getAttribute('id');
                                    if (!$("#" + lineId + " > .podcast-transcript-text").hasClass("current")) {
                                        $("#" + lineId + " > .podcast-transcript-text").addClass("current");
                                        var scrollOffset = $(".current").offset().top - $("#podcast-transcript_" + transcriptId).offset().top;
                                        $("#podcast-transcript_" + transcriptId).animate({ scrollTop: '+=' + scrollOffset });
                                    }
                                }
                                else {
                                    lineId = lines[i].getAttribute('id');
                                    $("#" + lineId + " > .podcast-transcript-text").removeClass("current");
                                }
                            }
                        });
                    });
                }
                // Toggle Transcript.
                $(".podcast-transcript-container").hide();
                $(".show-podcast-transcript").click(function () {
                    transcriptId = $(this).attr('data-id').split("_")[1];
                    $("#hide_podcast_" + transcriptId).show();
                    $("#show_podcast_" + transcriptId).hide();
                    $("#" + transcriptId).show();
                    $("#transcript-container_" + transcriptId).show();
                });
                $(".hide-podcast-transcript").click(function () {
                    transcriptId = $(this).attr('data-id').split("_")[1];
                    $("#hide_podcast_" + transcriptId).hide();
                    $("#show_podcast_" + transcriptId).show();
                    $("#" + transcriptId).hide();
                    $("#transcript-container_" + transcriptId).hide();
                });

            });
        }
    };

})(jQuery, Drupal, drupalSettings);
