(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformWorkflowTransitions = {
    attach: function (context, settings) {
      // Find all radio buttons in the workflow transition element and replace with new cta
      var $transitions = $('#edit-erpw-workflow-transition--2 input[type=radio]', context);
      var $transtionsContainer = $('#edit-actions', context);
      $transitions.each(function(index) {
        var label = $(this).next('label').text();
        if (!$transtionsContainer.find('.erpw-workflow-cta-button:contains(' + label + ')').length) {
          var $customButton = $('<button class="erpw-workflow-cta-button">' + label + '</button>');
          $customButton.click(function(event) {
            // Prevent the default form submission
            event.preventDefault();
            if (isDeleteTransition(label)) {
              // Show the custom Drupal modal
              var dialogOptions = {
                title: Drupal.t('Are you sure you want to delete this service?'),
                dialogClass:"delete-service-popup",
                buttons: {
                  "Delete": function() {
                    $(this).dialog("close");
                    // Trigger corresponding radio button event on click of delete buttons
                    $(this).addClass('active').siblings().removeClass('active');
                    $transitions.eq(index).prop('checked', true).trigger('click');
                    // Trigger submit button's click event on click of either of the buttons
                    $('#edit-submit').click();
                  },
                  "Cancel": function() {
                    $(this).dialog("close");
                    // Do nothing, the user cancelled the delete operation
                  }
                }
              };
              var $modalContent = $("<div class='message-delete-white'>" + Drupal.t("Click on Delete to") + " <strong>" + label +  "</strong> " + Drupal.t("or Cancel to go back.") + "</div>");
              $modalContent.dialog(dialogOptions);
            } else {
              // For non-delete transitions, proceed as before
              $(this).addClass('active').siblings().removeClass('active');
              $transitions.eq(index).prop('checked', true).trigger('click');
              $('#edit-submit').click();
            }
          });
          $transtionsContainer.append($customButton);
        }
      });
      // Hide webform workflow fieldset and submit button
      $('.workflow-transition, #edit-submit').hide();
    }
  };

  function isDeleteTransition(label) {
    // List of delete transitions
    var deleteTransitions = ['Submit for Deletion to Focal Point', 'Submit for Deletion to GBV Coordination', 'Delete Service'];
    return deleteTransitions.includes(label.trim());
  }
})(jQuery, Drupal, drupalSettings);
