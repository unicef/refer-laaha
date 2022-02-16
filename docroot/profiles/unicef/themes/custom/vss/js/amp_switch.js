/**
 * @file
 * JavaScript for Exit Website.
 */

 (function ($, Drupal) {
    setTimeout(function(){
        if(document.readyState !== 'complete') {  
         $("html").hide();
         var url = window.location.href+"?amp";
         var sw_ver = Drupal.t('Switch to lighter version');
         $('body').html("<div style='text-align:center;font-size: 25px;padding-top: 50px;'><a href = "+url+">"+sw_ver+"</a></div>");
         $("html").show();
        }
    },10000)
  })(jQuery, Drupal);
  