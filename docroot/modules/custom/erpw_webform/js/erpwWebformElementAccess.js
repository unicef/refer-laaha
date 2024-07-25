(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformElementAccess = {
    attach(context, settings) {
      $(document).ready(function () {
        // Element access on Webform.
        const attachClass =
          drupalSettings.erpw_webform.erpw_webform_element_access;
        if (!attachClass) {
          $(".webform-edit-form .webform-ui-elements-table tbody").addClass(
            "hide-submit-access-for-nonadmin-user",
          );
          $(".webform-edit-form .webform-ui-elements-table tbody tr").each(
            function (index, element) {
              const webformKey = element.getAttribute("data-webform-key");
              if (
                webformKey === "erpw_workflow" ||
                webformKey === "submission_domain" ||
                webformKey === "service_type" ||
                webformKey === "service_submission_id" ||
                webformKey === "service_location_tid" ||
                webformKey === "service_organisation"
              ) {
                $(element).addClass("hide-element-access-for-nonadmin-user");
              }
              if (webformKey === "location") {
                $(element).addClass("hide-element-menu-for-nonadmin-user");
              }
            },
          );
        }

        // Element access on Webform Submission when in delete workflow.
        const inDeletion =
          drupalSettings.erpw_webform.erpw_webform_deletion_access;
        if (inDeletion) {
          $("#edit-location--wrapper").addClass("disabled-for-delete");
          $("#edit-step-1-add-service-details .details-wrapper").addClass(
            "disabled-for-delete",
          );
          $("#edit-step-2-contact-fields .details-wrapper").addClass(
            "disabled-for-delete",
          );
        }

        // Target all <td> elements of Service Rating Webforms.
        $('.webform-service-rating-edit .hide-submit-access-for-nonadmin-user td').each(function() {
          // Get the text content of the <td>
          var text = $(this).text();
          
          // Check if the text matches the pattern
          var match = text.match(/(rating_question|multiple_choice_question)_\d+/);
          if (match) {
            // Remove the number, 'question' and replace '_' with space. 
            var newText = match[1].replace(/_/g, ' ').replace('question', '').trim();
            
            // Update the <td> text
            $(this).text(newText);
          }
        });

      });
    },
  };
})(jQuery, Drupal, drupalSettings);
