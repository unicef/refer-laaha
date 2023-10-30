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

      var currentUserId = drupalSettings.user.uid;
      localforageUserServiceChanges = localforage.createInstance({
        driver: localforage.INDEXEDDB,
        name: "userServiceChanges".concat(currentUserId),
        version: 1.0,
        storeName: "userServiceChanges".concat(currentUserId),
      });
      $(document).ready(function () {
        if (currentUserId != 0) {
          // Check if localforageUserServiceChanges has any key-value pairs
          localforageUserServiceChanges
            .length()
            .then(function (numberOfKeys) {
              if (numberOfKeys > 0) {
                localforageUserServiceChanges
                  .iterate(function (valueData, key, iterationNumber) {
                    var url = window.location.href;
                    if (key == url.match(/\/(\d+)\/edit$/)[1]) {
                      // Select all elements with the class erpw-workflow-cta-button.
                      var buttons = document.querySelectorAll(
                        ".erpw-workflow-cta-button"
                      );

                      // Loop through the selected buttons and add the click event listener to each.
                      buttons.forEach(function (button) {
                        button.addEventListener("click", buttonClickHandler);
                      });
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
                              if (
                                Array.isArray(valueData["changes"][fieldLabel])
                              ) {
                                var inputElement =
                                  $(jsFormItemDiv).find("select");
                                if (inputElement) {
                                  var selectedOptions =
                                    valueData["changes"][fieldLabel]; // The array of labels

                                  // Check if 'In Person' exists in the array
                                  var index =
                                    selectedOptions.indexOf("In Person");

                                  if (index !== -1) {
                                    // 'In Person' was found, replace it with 'In person'
                                    selectedOptions[index] = "In person";
                                  }
                                  for (var optionkey in inputElement.options) {
                                    if (
                                      typeof inputElement.options[optionkey] ===
                                      "object"
                                    ) {
                                      if (
                                        selectedOptions.includes(
                                          inputElement.options[optionkey].text
                                        )
                                      ) {
                                        inputElement.options[
                                          optionkey
                                        ].selected = true;
                                        inputElement.options[
                                          optionkey
                                        ].setAttribute("selected", "selected");
                                      }
                                    }
                                  }
                                  inputElement
                                    .val(selectedOptions)
                                    .trigger("change");
                                }
                              } else {
                                var inputElement =
                                  jsFormItemDiv.querySelector("input");
                                var inputElementTextarea =
                                  jsFormItemDiv.querySelector("textarea");
                                if (inputElement) {
                                  // Set the new value for the input element.
                                  inputElement.value =
                                    valueData["changes"][fieldLabel];
                                }
                                if (inputElementTextarea) {
                                  // Set the new value for the input element.
                                  inputElementTextarea.value =
                                    valueData["changes"][fieldLabel];
                                }
                              }
                            }
                          }
                        }

                        // Find all fieldset field types.
                        var fieldsetElements =
                          document.querySelectorAll("fieldset");
                        // Loop through the label elements to find the one that matches label text.
                        for (var i = 0; i < fieldsetElements.length; i++) {
                          if (
                            fieldsetElements[i].getAttribute(
                              "data-drupal-selector"
                            ) != "edit-erpw-workflow" &&
                            fieldsetElements[i].getAttribute(
                              "data-drupal-selector"
                            ) != "edit-erpw-workflow-workflow-fieldset" &&
                            fieldsetElements[i].getAttribute(
                              "data-drupal-selector"
                            ) != "edit-location" &&
                            fieldsetElements[i].getAttribute(
                              "data-drupal-selector"
                            ) != "edit-erpw-workflow-transition"
                          ) {
                            // Find the legend element label within the fieldset
                            var legendElementLabel = fieldsetElements[i]
                              .querySelector("legend")
                              .querySelector("span").textContent;
                            if (fieldLabel == legendElementLabel) {
                              var radios =
                                fieldsetElements[i].querySelector(
                                  ".js-webform-radios"
                                );
                              var checkboxes = fieldsetElements[
                                i
                              ].querySelector(".js-webform-checkboxes");
                              if (radios !== null) {
                                // Get all radio inputs within the container
                                var radioInputs = radios.querySelectorAll(
                                  'input[type="radio"]'
                                );

                                // Define the value to match.
                                var valueToMatch =
                                  valueData["changes"][fieldLabel];

                                // Loop through the radio inputs
                                radioInputs.forEach(function (radioInput) {
                                  // Check if the input's value matches the valueToMatch
                                  if (radioInput.value === valueToMatch) {
                                    // If it matches, select the input
                                    radioInput.checked = true;
                                  } else {
                                    // If it doesn't match, unselect it (optional)
                                    radioInput.checked = false;
                                  }
                                });
                              }
                              if (checkboxes !== null) {
                                // Get all radio inputs within the container
                                var checkbox = checkboxes.querySelectorAll(
                                  'input[type="checkbox"]'
                                );
                                checkbox.forEach(function (eachcheckbox) {
                                  // Get the label text by finding the corresponding label element
                                  var label =
                                    eachcheckbox.parentNode.querySelector(
                                      "label"
                                    );
                                  if (label) {
                                    var labelText = label.textContent.trim();
                                    if (
                                      valueData["changes"][fieldLabel].includes(
                                        labelText
                                      )
                                    ) {
                                      // Uncheck the checkbox
                                      eachcheckbox.checked = true;
                                    } else {
                                      // Uncheck the checkbox
                                      eachcheckbox.checked = false;
                                    }
                                  }
                                });
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

      // Define the event handler function.
      function buttonClickHandler(event) {
        // Check if localforageUserServiceChanges has any key-value pairs
        localforageUserServiceChanges.length().then(function (numberOfKeys) {
          if (numberOfKeys > 0) {
            localforageUserServiceChanges.iterate(function (
              valueData,
              key,
              iterationNumber
            ) {
              var url = window.location.href;
              if (key == url.match(/\/(\d+)\/edit$/)[1]) {
                localforageUserServiceChanges
                  .removeItem(key)
                  .then(function () {
                    // The key-value pair has been removed.
                    console.log("Removed data changes");
                  })
                  .catch(function (err) {
                    // Handle any errors that occur during removal.
                    console.error("Error removing data changes");
                  });
              }
            });
          }
        });
      }
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
