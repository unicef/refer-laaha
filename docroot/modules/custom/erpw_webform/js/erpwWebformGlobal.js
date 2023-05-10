(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformGlobal = {
    attach: function (context, settings) {
      $('.services-webform-submissions-list .views-exposed-form .form-select').select2();
    },
  };
})(jQuery, Drupal, drupalSettings);
