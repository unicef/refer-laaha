(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.icon_color_picker = {
      attach: function (context, settings) {
        $(document).ready(function() { 
          $('.field--name-field-service-type-icon .js-form-type-textfield').css('margin-top', 120);
          $('.fip-box').click(function(){
          });
    
          $('.color_field_widget_box__square').each(function(){
            var bgcolor = $(this).css( "background-color" );
            $(this).css('border-color', bgcolor);
          })

          //add bg on selected icon
          var add_bg= $('body').find('.icons-selector').children('.selector').find('span.selected-icon');
          $('.color_field_widget_box__square').click(function(){
            var color = $(this).css( "background-color" );
            $(add_bg).css('background-color', color);
            console.log(add_bg);
          })
          var activeDiv = $('.color_field_widget_box__square.active').css( "background-color" );
          $(add_bg).css('background-color', activeDiv);

          //Delete screen dialogtitle custom
          var popup_msg = Drupal.t('<div class="review-msg">Deleted successfully</div><div class="popup-content">The details has been successfully Deleted.</div>')
          $( ".page-node-type-service-type .delete-screen" ).parents('body').find('.ui-dialog .ui-dialog-title').html(popup_msg);
        });
      }
    };
  }(jQuery, Drupal, drupalSettings));
