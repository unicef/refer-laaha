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
                    var player = $('#audio').get(0);
                    player.currentTime = seekToTime;
                    player.play();
                });
                // Highlight the transcript based on the video time.
                var lines = document.getElementById("podcast-transcript").getElementsByTagName("div");
                var player = $('#audio').get(0);

                // Update the progress bar as the video plays
                player.addEventListener('timeupdate', function () {
                    var now = player.currentTime;

                    // highlight text as video plays
                    for (var i = 0, l = lines.length; i < l; i++) {
                        if (now >= lines[i].getAttribute("data-start") &&
                            now <= lines[i].getAttribute("data-end")) {
                            lineId = lines[i].getAttribute('id');
                            if(!$("#" + lineId + " > .podcast-transcript-text").hasClass( "current" )) {
                                $("#" + lineId + " > .podcast-transcript-text").addClass("current");
                                var scrollOffset = $(".current").offset().top - $("#podcast-transcript").offset().top;
                                $("#podcast-transcript").animate({scrollTop: '+='+scrollOffset});
                            }
                        }
                        else {
                            lineId = lines[i].getAttribute('id');
                            $("#" + lineId + " > .podcast-transcript-text").removeClass("current");
                        }
                    }
                });
                // Toggle Transcript.
                $(".transcript-container").hide();
                $(".podcast-transcript-container").hide();
                $(".show-podcast-transcript").click(function(){
                    $(".hide-podcast-transcript").show();
                    $(".show-podcast-transcript").hide();
                    $("#podcast-transcript").show();   
                    $(".podcast-transcript-container").show();   
                    $(".transcript-container").show();
                });
                $(".hide-podcast-transcript").click(function(){
                    $(".show-podcast-transcript").show();
                    $(".hide-podcast-transcript").hide();
                    $("#podcast-transcript").hide();  
                    $(".podcast-transcript-container").hide();
                    $(".transcript-container").hide(); 
                });

            });
        }
    };

})(jQuery, Drupal, drupalSettings);
