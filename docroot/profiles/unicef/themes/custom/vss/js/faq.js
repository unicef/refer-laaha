(function ($, Drupal, drupalSettings) {
  'use strict';

  $(document).ready(function(){
    $('.faq-item .faq-answer-wrapper').addClass('hide-long-text');
    $('.long-text').hide();
    $('.faq_btn-more').click(function(){
      $(this).parent('.short-text').hide();
      $(this).parent().next('.long-text').slideDown();
    });
    $('.faq_btn-less').click(function(){
      $(this).parent().prev('.short-text').show();
      $(this).parent('.long-text').slideUp();
    });
  });
})(jQuery, Drupal, drupalSettings);
