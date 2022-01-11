/**
 * @file
 * JavaScript for Transcript.
 */

(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
        var tag = document.createElement('script');
        var player;
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        //Holds a reference to the YouTube player
        var player;
        window.onYouTubeIframeAPIReady = function () {

            videoTranscriptsCount = $('.show-transcript-external').length;
            console.log(videoTranscriptsCount);
            for (var i = 0; i < videoTranscriptsCount; i++) {
                var transcriptId = $('.show-transcript-external')[i].getAttribute("data-id").split("_")[1];
                var frameId = 'youtube_' + transcriptId;
                $('#youtube_' + transcriptId).find('iframe').attr('id', frameId);
                $('#youtube_' + transcriptId).css({"border-radius": "20px"});
                youtube_url = $('#youtube_' + transcriptId).find('iframe').attr('src');
                videoId = youtube_parser(youtube_url);
                player = new YT.Player(frameId, {
                    width: '100%',
                    height: '390',
                    videoId: videoId,
                    modestbranding: 1, // Hide the Youtube Logo
                    host: 'https://www.youtube.com',
                    playerVars: {
                        'origin': 'https://' + window.location.host
                    },
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
            }

            function youtube_parser(url) {
                var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
                var match = url.match(regExp);
                return (match && match[7].length == 11) ? match[7] : false;
            }
            function onPlayerReady(event) {

            }
            var timeupdater = null;
            function onPlayerStateChange(event) {
                // check if player is playing.
                if (event.data == YT.PlayerState.PLAYING) {
                    timeupdater = setInterval(function () {
                        {
                            now = player.getCurrentTime();
                            var transcriptId = event.target.getIframe().id.split('_')[1];
                            console.log("transcriptID " + transcriptId);
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
                        }
                    }, 100);
                }
                else {
                    clearInterval(timeupdater);
                }

            }

            $(".transcript-container").hide();
            $(".hide-transcript-external").hide();
            $(".show-transcript-external").click(function () {
                transcriptId = $(this).attr('id').split("_")[1];
                $("#hide_" + transcriptId).show();
                $("#show_" + transcriptId).hide();
                $("#" + transcriptId).show();
                $("#transcript-container_" + transcriptId).show();
            });
            $(".hide-transcript-external").click(function () {
                transcriptId = $(this).attr('id').split("_")[1];
                $("#hide_" + transcriptId).hide();
                $("#show_" + transcriptId).show();
                $("#" + transcriptId).hide();
                $("#transcript-container_" + transcriptId).hide();
            });
        }
    });

})(jQuery, Drupal, drupalSettings);
