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

        
    }
    };
  
  })(jQuery, Drupal, drupalSettings);