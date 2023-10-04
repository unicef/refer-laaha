(function ($) {
  Drupal.behaviors.myModule = {
    attach: function (context, settings) {
      /* Function to update the form based on the selected question type.
      */
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

      /* Function to add new fields on click of add new question cta.
      */
      $('#edit-add-question', context).once('customForm').on('click', function (e) {
        e.preventDefault();

        // Clone the last question section.
        var lastQuestion = $('.question-section:last').clone();

        // Clear the values in the cloned question section.
        lastQuestion.find('input, textarea, select').val('');

        // Increment the question section name attribute.
        lastQuestion.find('[name]').each(function () {
          var name = $(this).attr('name');
          var matches = name.match(/question_(\d+)/);
          if (matches && matches.length === 2) {
            var newIndex = parseInt(matches[1]) + 1;
            name = name.replace('question_' + (newIndex - 1), 'question_' + newIndex);
            $(this).attr('name', name);
          }
        });

        // Append the cloned question section to the questions wrapper.
        $('#questions-wrapper').append(lastQuestion);
      }); 
    }
  };
})(jQuery);
