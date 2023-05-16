(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformTemplate = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $('.webform-add-form .form-item-category-select').each(function () {
          $(this).closest('fieldset').css('visibility', 'hidden');
          $(this).closest('fieldset').css('height', '0px');
        });
        $('.webform-duplicate-form .form-item-category-select').each(
          function () {
            $(this).closest('fieldset').css('visibility', 'hidden');
            $(this).closest('fieldset').css('height', '0px');
          }
        );
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
