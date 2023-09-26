(function ($) {
  Drupal.behaviors.myModule = {
    attach: function (context, settings) {
      // Function to update the form based on the selected question type.
      function updateForm(questionType) {
        if (questionType === 'rating') {
          // Modify the title for Rating Question type.
          $('.options-fieldset-wrapper .form-item').each(function (index) {
            $(this).find('label').text(index + 1);
          });
        } else if (questionType === 'multiple_choice') {
          // Modify the title for Multiple Choice Question type.
          $('.options-fieldset-wrapper .form-item').each(function () {
            $(this).find('label').text('Option');
          });
        }
      }

      // Initialize the form based on the default question type.
      var defaultQuestionType = $('input[name="question_type"]:checked').val();
      updateForm(defaultQuestionType);

      // Listen for changes in the "Question Type" radios element.
      $('input[name="question_type"]').change(function () {
        // Determine the selected question type.
        var questionType = $('input[name="question_type"]:checked').val();

        // Update the form dynamically based on the selected question type.
        updateForm(questionType);
      });
    }
  };
})(jQuery);
