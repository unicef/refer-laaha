(function ($, Drupal, drupalSettings, localforage) {
  var alreadyExecuted = false; // Flag to track whether the code has already run

  Drupal.behaviors.erpwOfflineServicesChangesListing = {
    attach: function (context, settings) {
      // Check if the code has already been executed
      if (alreadyExecuted) {
        return;
      }
      // Set the flag to indicate that the code has been executed
      alreadyExecuted = true;

      $(document).ready(function () {
        var currentUserId = drupalSettings.user.uid;
        // Get a reference to the existing <div class="region region-content">
        var regionContent = document.querySelector(".region-content");
        const containerOuter = document.createElement("div");
        containerOuter.className = "reminder-details-rows";
        regionContent.appendChild(containerOuter);
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
                    const rowContainer = document.createElement("div");
                    rowContainer.className = "row-container";

                    const rowContainerHeader = document.createElement("div");
                    rowContainerHeader.className = "row-container-header";
                    rowContainerHeader.textContent = Drupal.t("Changes:");
                    rowContainer.appendChild(rowContainerHeader);
                    for (var field in valueData) {
                      if (field == "original") {
                        var counter = 0;
                        var parsedOrganisation = "";
                        var parsedFpNumber = "";
                        var parsedFpName = "";
                        var parsedLocation = "";
                        var parsedServiceMode = "";
                        var parsedServicetype = "";
                        var parsedStColor = "";
                        var parsedStIcon = "";

                        if (valueData["original"]["Organisation"]) {
                          parsedOrganisation =
                            valueData["original"]["Organisation"];
                        } else {
                          parsedOrganisation = Drupal.t("Not available.");
                        }
                        if (
                          valueData["original"][
                            "Name of focal point for referrals (first/last name)"
                          ]
                        ) {
                          parsedFpName =
                            valueData["original"][
                              "Name of focal point for referrals (first/last name)"
                            ];
                        } else {
                          parsedFpName = Drupal.t("Not available.");
                        }
                        if (
                          valueData["original"]["Phone number of focal point"]
                        ) {
                          parsedFpNumber =
                            valueData["original"][
                              "Phone number of focal point"
                            ];
                        } else {
                          parsedFpNumber = Drupal.t("Not available.");
                        }
                        if (valueData["original"]["Location"]) {
                          parsedLocation = valueData["original"]["Location"];
                        } else {
                          parsedLocation = Drupal.t("Not available.");
                        }
                        if (valueData["original"]["Service Mode"]) {
                          parsedServiceMode =
                            valueData["original"]["Service Mode"];
                        } else {
                          parsedServiceMode = Drupal.t("Not available.");
                        }
                        if (valueData["original"]["Service Type"]) {
                          parsedServicetype =
                            valueData["original"]["Service Type"];
                        }
                        if (valueData["original"]["Service Type Color"]) {
                          parsedStColor =
                            valueData["original"]["Service Type Color"];
                        }
                        console.log(valueData["original"]);
                        if (valueData["original"]["Service Type Icon"]) {
                          parsedStIcon =
                            valueData["original"]["Service Type Icon"];
                        }
                        // Create the div structure
                        const div = document.createElement("div");
                        div.classList.add("views-row");
                        div.dataset.key = key; // You might need to adjust this based on your structure
                        var linkHTML = "";

                        linkHTML = `
                          <div class="service-provider-details">
                            <div class="service-detail-heading">
                              <div class="view-delete-links">
                                <span class="view-link">
                                  <a href="/en/admin/structure/webform/manage/${valueData["webformID"]}/submission/${key}/edit">Review and submit</a>
                                </span>
                              </div>
                            </div>
                          </div>`;

                        div.innerHTML = `
                          <div class="service-providers-submission-row">
                            <div class="row-header">
                              <div class=" service-type-color-logo-container">
                                <div class="service-type-color" style="background-color:${parsedStColor}"></div>
                                <div class="service-type-logo">
                                  <i class="${parsedStIcon}"></i>
                                </div>
                              </div>
                              <div class="service-type-org">
                                <a href="/service/${key}">${parsedOrganisation} - ${parsedServicetype}</a>
                              </div>
                            </div>
                            <div class="views-field views-field-webform-submission-organisation-field service-provider-org org-icon service-list-icons">
                              <span class="views-label views-label-webform-submission-organisation-field">Organisation:
                              </span>
                              <span class="field-content">${parsedOrganisation}</span>
                            </div>
                            <div class="views-field views-field-webform-submission-focal-point-field service-provider-org org-icon service-list-icons">
                              <span class="views-label views-label-webform-submission-focal-point-field">Focal Point:
                              </span>
                              <span class="field-content">${parsedFpName}</span>
                            </div>
                            <div class="views-field views-field-webform-submission-hotline-contact-field service-provider-number contact-icon service-list-icons">
                              <span class="views-label views-label-webform-submission-hotline-contact-field">Contact:
                              </span>
                              <span class="field-content">${parsedFpNumber}</span>
                            </div>
                            <div class="views-field views-field-webform-submission-location-field location-icon service-list-icons">
                              <span class="views-label views-label-webform-submission-location-field">Location:
                              </span>
                              <span class="field-content">${parsedLocation}</span>
                            </div>
                            <div class="views-field views-field-webform-submission-mode-field service-mode mode-icon service-list-icons">
                              <span class="views-label views-label-webform-submission-mode-field">Service Mode:
                              </span>
                              <span class="field-content">${parsedServiceMode}</span>
                            </div>
                            ${linkHTML}
                          </div>`;

                        // Append the div to the view-content div
                        if (counter == 0) {
                          rowContainer.appendChild(div);
                          counter++;
                        }
                      }
                    }
                    containerOuter.appendChild(rowContainer);
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
              if (numberOfKeys == 0) {
                const container = document.createElement("div");
                container.id = "reminder-details";
                container.className = "reminder-details-offline";
                container.style.backgroundColor = "rgba(243, 193, 191, 0.53)";

                const reminderHeading = document.createElement("div");
                reminderHeading.className = "reminder-detail-heading";
                reminderHeading.textContent = Drupal.t(
                  "No changes available for review."
                );

                container.appendChild(reminderHeading);

                // Insert the new <div> as the first child inside the existing <div class="region region-content">
                regionContent.insertBefore(container, regionContent.firstChild);
              } else {
                console.log("localforageUserServiceChanges is empty.");
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
