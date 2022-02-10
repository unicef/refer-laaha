(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.location = {
        attach: function (context, settings) {
            $(document).ready(function() {
                $(".path-delete-location .ui-icon-closethick").on("click", function(event){
                    event.preventDefault();
                    window.location.href = document.referrer;
                });
            });

            $(".loc-dropdown").on('change', function() {
              var level = drupalSettings.leveldetails.level;
              var levelcount = drupalSettings.leveldetails.levelcount;
              for (i = level; i < levelcount; i++) {
                $("#edit-level-" + i).addClass('hidden');
                $("#edit-level-" + i).find('option:not(:first)').remove();
              }
              jQuery(".add_multiple").prop("multiple", 'true');
          });
        }
    };
}(jQuery, Drupal, drupalSettings));
