(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.location = {
    attach: function (context, settings) {
      $(".loc-dropdown").on('change', function() {
        var level = $(this).attr("data-level");
        var levelcount = drupalSettings.leveldetails.levelcount;
        for (var i = level; i <= levelcount; i++) {
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
