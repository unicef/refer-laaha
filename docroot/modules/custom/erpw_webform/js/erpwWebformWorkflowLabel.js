(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformWorkflowLabel = {
    attach: function (context, settings) {
      // Get all elements with the class name "workflow-label-class"
      var elements = document.getElementsByClassName("workflow-label-class");

      // Convert the HTMLCollection to an array
      var elementsArray = Array.from(elements);

      // Loop through each element using forEach
      elementsArray.forEach(function (tdElement) {
        // Check if the value is "approved"
        if (tdElement.textContent.trim().toLowerCase() === "approved") {
          tdElement.classList.add("approved-workflow"); // Add the class "approved-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "rejected") {
          tdElement.classList.add("rejected-workflow"); // Add the class "rejected-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "in review with country admin") {
          tdElement.classList.add("in-review-cadmin-workflow"); 
          // Add the class "in-review-cadmin-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "in review with gbvi coordinator") {
          tdElement.classList.add("in-review-coordinator-workflow");
          // Add the class "in-review-coordinator-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "draft") {
          tdElement.classList.add("draft-workflow"); // Add the class "draft-workflow"
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
