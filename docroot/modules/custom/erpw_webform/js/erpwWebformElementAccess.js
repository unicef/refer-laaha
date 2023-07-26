(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformElementAccess = {
    attach: function (context, settings) {
      $(document).ready(function () {
        var attachClass =
          drupalSettings.erpw_webform.erpw_webform_element_access;
        if (!attachClass) {
          $('.webform-edit-form .webform-ui-elements-table tbody').addClass(
            'hide-submit-access-for-nonadmin-user'
          );
          $('.webform-edit-form .webform-ui-elements-table tbody tr').each(
            function (index, element) {
              var webformKey = element.getAttribute('data-webform-key');
              if (
                webformKey == 'erpw_workflow' ||
                webformKey == 'submission_domain' ||
                webformKey == 'service_type'
              ) {
                $(element).addClass('hide-element-access-for-nonadmin-user');
              }
              if (webformKey == 'location') {
                $(element).addClass('hide-element-menu-for-nonadmin-user');
              }
            }
          );
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
