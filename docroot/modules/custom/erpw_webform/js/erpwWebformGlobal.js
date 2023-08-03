(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformGlobal = {
    attach: function (context, settings) {
      $(
        ".services-webform-submissions-list .views-exposed-form .form-select"
      ).select2();
      $(document).ready(function () {
        $(".webform-submission-form .default-open-summary").attr("open", "");
        $(".webform-submission-form .default-open-summary summary").attr(
          "aria-expanded",
          "true"
        );
        $(".webform-submission-form .default-open-summary summary").attr(
          "aria-pressed",
          "true"
        );
        $('.erpw-workflow-cta-button:contains("Save as Draft")').addClass('draft-transition');

        // Change the style of Delete Buttons of the Service Workflow
        const deleteTransitions = ['Submit for Deletion to Focal Point', 'Submit for Deletion to GBV Coordination', 'Delete Service'];
        deleteTransitions.forEach(transition => {
          $('.erpw-workflow-cta-button:contains(' + transition + ')').addClass('delete-transition');
        });

      });
    },
  };
})(jQuery, Drupal, drupalSettings);
