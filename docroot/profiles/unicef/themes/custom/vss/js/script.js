(function ($, Drupal, drupalSettings) {

    'use strict';
  
    Drupal.behaviors.mybehavior = {
      attach: function (context, settings) {
     
   //@todo
        $("#edit-audio-enable-1").change(function() {
            if ($(this).is(":checked")){
               $.cookie("audio_enabled", true );
               }else{
                $.cookie("audio_enabled", false );
            }
            }) 
           if( $.cookie("audio_enabled" )){
                $("#edit-audio-enable-1").attr('checked', true);
            }
            else{
            
                $("#edit-audio-enable-1").removeAttr('checked');
            }
            $("#edit-audio-enable-2").click(function() {
                if ($(this).is(":checked")){
                 $.cookie("video_enabled", true );
                }
            })  
            
           
            /**Zoom percentage */
           var count= parseInt($("#zoom-percent").text())
           var click_count=0;
            $('#text_resize_increase').once().click(function (e) {
                //console.log();
                click_count++;
            //    console.log(click_count);
            //console.log("+ clicked");
            count=count+count*10/100;
           //  console.log(count);
               $("#zoom-percent").text(Math.round(count));
            })
            $('#text_resize_decrease').once().click(function () {
              //  console.log("- clicked")
                count=count-count*10/100;
                $("#zoom-percent").text(Math.round(count)) ;
            })
/** clone logo change */
if($(window).width() < 768){
    $(".logo-wrapper").clone().insertBefore("#block-navigationheadermenu");
    $(".logo-wrapper").clone().insertBefore("#block-iraqnavigationheadermenu");
    $("#block-exitwebsiteblock .telephone-header").insertBefore(".telephone_in_mobile .holder");
}
/**play pause button */
$("#pause-article").hide();
$('#play-article').on('click', function(event) {
   

    $('#pause-article').show();
    $('#play-article').hide();
  });

  $('#pause-article').on('click', function(event) {
   
    $('#pause-article').hide();
    $('#play-article').show();
  });
  /** readmore */
 
  var maxLength = 300;
	$("article p").each(function(){
		var myStr = $(this).text();
		if($.trim(myStr).length > maxLength){
			var newStr = myStr.substring(0, maxLength);
			var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
			$(this).empty().html(newStr);
			$(this).append('<button id="readmore" class="button-defult read-more">READ MORE <i class="fa fa-angle-down" aria-hidden="true"></i></button>');
			$(this).append('<span class="more-text">' + removedStr + '</span>');
		}
	});
	$(".read-more").click(function(){
		$(this).siblings(".more-text").contents().unwrap();
		$(this).remove();
	});
  

    }
    };
  
  })(jQuery, Drupal, drupalSettings);