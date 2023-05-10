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
          $customButton.click(function() {
            // Trigger corresponding radio button event on click of newly created buttons
            $(this).addClass('active').siblings().removeClass('active');
            $transitions.eq(index).prop('checked', true).trigger('click');
            // Trigger submit button's click event on click of either of the buttons
            $('#edit-submit').click();
          });
          $transtionsContainer.append($customButton);
        }
      });
      // Hide webform workflow fieldset and submit button
      $('.workflow-transition, #edit-submit').hide();
    }
  };
})(jQuery, Drupal, drupalSettings);
