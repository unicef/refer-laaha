(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformWorkflowLabel = {
    attach(context, settings) {
      // Get all elements with the class name "workflow-label-class"
      const elements = document.getElementsByClassName("workflow-label-class");

      // Convert the HTMLCollection to an array
      const elementsArray = Array.from(elements);

      // Loop through each element using forEach
      elementsArray.forEach(function (tdElement) {
        // Check if the value is "approved"
        if (tdElement.textContent.trim().toLowerCase() === "approved") {
          tdElement.classList.add("approved-workflow"); // Add the class "approved-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "rejected") {
          tdElement.classList.add("rejected-workflow"); // Add the class "rejected-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "in review with gbv coordination"
        ) {
          tdElement.classList.add("in-review-coordination-workflow");
          // Add the class "in-review-coordination-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "in review with focal point"
        ) {
          tdElement.classList.add("in-review-focal-point-workflow");
          // Add the class "in-review-focal-point-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "draft") {
          tdElement.classList.add("draft-workflow"); // Add the class "draft-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "edits in review with focal point"
        ) {
          tdElement.classList.add("edits-in-review-focal-point-workflow"); // Add the class "edits-in-review-focal-point-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "edits in review with gbv coordination"
        ) {
          tdElement.classList.add("edits-in-review-coordination-workflow"); // Add the class "edits-in-review-coordination-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "deletion in review with focal point"
        ) {
          tdElement.classList.add("deletion-in-review-focal-point-workflow"); // Add the class "deletion-in-review-focal-point-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "deletion in review with gbv coordination"
        ) {
          tdElement.classList.add("deletion-in-review-coordination-workflow"); // Add the class "edits-in-review-coordination-workflow"
        } else if (tdElement.textContent.trim().toLowerCase() === "deleted") {
          tdElement.classList.add("deleted-workflow"); // Add the class "deleted-workflow"
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "deactivation in review with focal point"
        ) {
          // Add the class "archive-in-review-focal-point-workflow"
          tdElement.classList.add("archive-in-review-focal-point-workflow");
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "deactivation in review with gbv coordination"
        ) {
          // Add the class "archive-in-review-coordination-workflow"
          tdElement.classList.add("archive-in-review-coordination-workflow");
        } else if (
          tdElement.textContent.trim().toLowerCase() === "deactivated"
        ) {
          // Add the class "archived-workflow"
          tdElement.classList.add("archived-workflow");
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "service activation in review with focal point"
        ) {
          // Add the class "restore-service-in-review-with-focal-point"
          tdElement.classList.add("restore-service-in-review-with-focal-point");
        } else if (
          tdElement.textContent.trim().toLowerCase() ===
          "service activation in review with gbv coordination"
        ) {
          // Add the class "restore-service-in-review-with-gbv-coordination"
          tdElement.classList.add(
            "restore-service-in-review-with-gbv-coordination",
          );
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
