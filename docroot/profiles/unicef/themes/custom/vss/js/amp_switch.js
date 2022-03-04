/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($, Drupal) {
    setTimeout(function(){
        if(document.readyState !== 'complete') {
         var url = window.location.href+"?amp";
         var sw_ver = Drupal.t('Switch to lighter version');
         var amp = "<div class='amp' style='text-align:center;font-size: 25px;background-color:lightyellow;'><a href = "+url+">"+sw_ver+"</a></div>";
         $('body').prepend(amp);
        }
    },10000)

    $(window).on('load', function () {
      $('body').find('.amp').hide();
   });
  })(jQuery, Drupal);
  