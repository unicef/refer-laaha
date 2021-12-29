(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.icon_color_picker = {
      attach: function (context, settings) {
        $(document).ready(function() { 
          $('.fip-box').click(function(){
            $(this).parents('form').find('.selector').show();
              $('.field--name-field-service-type-icon .js-form-type-textfield').css('margin-top', 90);
          });
    
          $('.color_field_widget_box__square').each(function(){
            var bgcolor = $(this).css( "background-color" );
            $(this).css('border-color', bgcolor);
          })

          $('.color_field_widget_box__square').click(function(){
            var color = $(this).css( "background-color" );
            var add_bg= $(this).parents('body').find('.icons-selector').children('.selector').find('span.selected-icon');
            $(add_bg).css('background-color', color);
          })
          if($('.messages--error').length && $('.fip-box').hasClass('current-icon')) {
            $('body').find('.selector').show();
            $('.field--name-field-service-type-icon .js-form-type-textfield').css('margin-top', 90);
          }
        });
      }
    };
  }(jQuery, Drupal, drupalSettings));
