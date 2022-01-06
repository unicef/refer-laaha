(function ($, Drupal, drupalSettings) {
  'use strict';

  $(document).ready(function(){
    $('.faq-item .faq-answer-wrapper').addClass('hide-long-text');
    $('.faq_btn-more').click(function(){
      $(this).parent().parent().addClass('hide-short-text');
      $(this).parent().parent().removeClass('hide-long-text');
    });
    $('.faq_btn-less').click(function(){
      $(this).parent().parent().addClass('hide-long-text');
      $(this).parent().parent().removeClass('hide-short-text');
    });
  });
})(jQuery, Drupal, drupalSettings);
