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
           var count= parseInt($("#zoom-percent").text());
           var  zoom_cont = $.cookie('zoom');
           console.log(zoom_cont);
           var click_count=100;
            $('#text_resize_increase').once().click(function (e) {
               

            count=count+10;
            $.cookie('zoom',count, {path: '/'});
          
               $("#zoom-percent").text(Math.round(count));
            })
            $('#text_resize_decrease').once().click(function () {
              
                count=count-count-10;
                $("#zoom-percent").text(Math.round(count)) ;
            })
/** clone logo change */
if($(window).width() < 768){
    $(".logo-wrapper").clone().insertBefore("#block-navigationheadermenu");
    $(".logo-wrapper").clone().insertBefore("#block-iraqnavigationheadermenu");
    $("#block-exitwebsiteblock .telephone-header").insertBefore(".telephone_in_mobile .holder");
}

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