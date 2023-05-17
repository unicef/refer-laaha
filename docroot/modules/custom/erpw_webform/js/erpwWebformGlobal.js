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
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
