(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.location = {
    attach: function (context, settings) {
      $(".loc-dropdown").on('change', function() {
        let level = $(this).attr("data-level");
        let levelcount = drupalSettings.leveldetails.levelcount;
        for (let i = level; i <= levelcount; i++) {
          $("#location-level-" + i).addClass('hidden');
          $("#location-level-" + i).find('option:not(:first)').remove();
        }
        // Make last dropdown as multi select element.
        $(".add_multiple").prop("multiple", 'true');
      });
      // Adding icon class for Referral Pathway for add another button.
      $('.node-referral-path-way-form .field-add-more-submit, .node-referral-path-way-edit-form  .field-add-more-submit').parents('.clearfix').addClass('plus-icon button-with-icon');
    }
  };
}(jQuery, Drupal, drupalSettings));
