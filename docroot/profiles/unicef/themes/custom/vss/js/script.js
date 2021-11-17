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
            
           
           
/** clone logo change */
if($(window).width() < 768){
    $(".logo-wrapper").clone().insertBefore("#block-navigationheadermenu");
    $(".logo-wrapper").clone().insertBefore("#block-iraqnavigationheadermenu");
    $("#block-exitwebsiteblock .telephone-header").insertBefore(".telephone_in_mobile .holder");
}

  /** readmore */
  $('#readmore_article').readmore({
    moreLink: '<button id="readmore" class="button-defult read-more">READ MORE <i class="fa fa-angle-down" aria-hidden="true"></i></button>',
    collapsedHeight: 1500,
    afterToggle: function(trigger, element, expanded) {
      if(! expanded) { // The "Close" link was clicked
        $('html, body').animate({scrollTop: element.offset().top}, {duration: 100});
      }
    }
  });
  
 
  
  

    }
    };
  
  })(jQuery, Drupal, drupalSettings);