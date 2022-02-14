(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.location = {
        attach: function (context, settings) {
            $(document).ready(function() {
                $(".path-delete-location .ui-icon-closethick").on("click", function(event){
                    event.preventDefault();
                    window.location.href = drupalSettings.erpw_location.redirect_to_manage;
                });
            });

            // Adding class to hide the elements for location dropdown on forms.
            $(".loc-dropdown").on('change', function() {
              var level = $(this).attr("data-level");
              var levelcount = drupalSettings.leveldetails.levelcount;
              for (i = level; i <= levelcount; i++) {
                $("#location-level-" + i).addClass('hidden');
                $("#location-level-" + i).find('option:not(:first)').remove();
              }
              // Make last dropdown as multi select element.
              $(".add_multiple").prop("multiple", 'true');
          });
        	//Referral Pathway for add another section
        	$('.node-referral-path-way-form .field-add-more-submit').parents('.clearfix').addClass('plus-icon button-with-icon');
        }
    };
}(jQuery, Drupal, drupalSettings));
