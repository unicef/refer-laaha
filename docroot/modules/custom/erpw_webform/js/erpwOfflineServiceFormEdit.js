(function ($, Drupal, drupalSettings, localforage) {
  var alreadyExecuted = false; // Flag to track whether the code has already run

  Drupal.behaviors.erpwOfflineServiceFormEdit = {
    attach: function (context, settings) {
      // Check if the code has already been executed
      if (alreadyExecuted) {
        return;
      }
      // Set the flag to indicate that the code has been executed
      alreadyExecuted = true;

      $(document).ready(function () {
        var currentUserId = drupalSettings.user.uid;
        if (currentUserId != 0) {
          // Inside your fetchDataAndStore function:
          localforageUserServiceChanges = localforage.createInstance({
            driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
            name: "userServiceChanges".concat(currentUserId),
            version: 1.0,
            storeName: "userServiceChanges".concat(currentUserId),
          });
          // Check if localforageUserServiceChanges has any key-value pairs
          localforageUserServiceChanges
            .length()
            .then(function (numberOfKeys) {
              if (numberOfKeys > 0) {
                localforageUserServiceChanges
                  .iterate(function (valueData, key, iterationNumber) {
                    var url = window.location.href;
                    if (key == url.match(/\/(\d+)\/edit$/)[1]) {
                      for (var fieldLabel in valueData["changes"]) {
                        // Find all the label elements on the page.
                        var labelElements = document.querySelectorAll("label");

                        // Loop through the label elements to find the one that matches label text.
                        for (var i = 0; i < labelElements.length; i++) {
                          if (
                            labelElements[i].textContent.trim() === fieldLabel
                          ) {
                            // Once the label with matching text is found, get its parent div with class "js-form-item".
                            var jsFormItemDiv =
                              labelElements[i].closest(".js-form-item");
                            if (jsFormItemDiv) {
                              jsFormItemDiv.classList.add(
                                "highlighted-changed-data"
                              );
                              var inputElement =
                                jsFormItemDiv.querySelector("input");
                              if (inputElement) {
                                // Set the new value for the input element.
                                inputElement.value =
                                  valueData["changes"][fieldLabel];
                              }
                            }
                          }
                        }
                      }
                    }
                  })
                  .then(function () {
                    // This block of code will run after the iteration is complete.
                    console.log("Iteration complete");
                  })
                  .catch(function (error) {
                    // Handle any errors that occur during the iteration.
                    console.error("Error during iteration: ", error);
                  });
              }
            })
            .catch(function (error) {
              console.error("No offline changes: error", error);
            });
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
