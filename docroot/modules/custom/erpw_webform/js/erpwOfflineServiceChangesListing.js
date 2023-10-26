(function ($, Drupal, drupalSettings, localforage) {
  var alreadyExecuted = false; // Flag to track whether the code has already run
  var numberChanges = false;
  var numberCreated = false;
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
      localforageUserServiceChanges = localforage.createInstance({
        driver: localforage.INDEXEDDB,
        name: "userServiceChanges".concat(currentUserId),
        version: 1.0,
        storeName: "userServiceChanges".concat(currentUserId),
      });

      localforageUserServiceCreated = localforage.createInstance({
        driver: localforage.INDEXEDDB,
        name: "userServiceCreated".concat(currentUserId),
        version: 1.0,
        storeName: "userServiceCreated".concat(currentUserId),
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

                var fieldsHTML = ``;
                for (var field in valueData) {
                  if (field == "changes") {
                    for (var fieldKey in valueData["changes"]) {
                      var orgValue = "";
                      if (valueData["original"][fieldKey] === undefined) {
                        orgValue = "Not available.";
                      } else {
                        orgValue = valueData["original"][fieldKey];
                      }
                      var fieldHTML = `
                            <div class="views-field">
                              <span class="views-label">${fieldKey}</span>
                              <span class="field-content">
                                <span class="before">${orgValue}</span>
                                <span class="after">${valueData["changes"][fieldKey]}</span>
                              </span>
                            </div>`;
                      fieldsHTML += fieldHTML;
                    }
                  }
                }
                for (var field in valueData) {
                  if (field == "original") {
                    var counter = 0;
                    var parsedOrganisation = "";
                    var parsedServicetype = "";
                    var parsedStColor = "";
                    var parsedStIcon = "";
                    if (valueData["original"]["Organisation"]) {
                      parsedOrganisation =
                        valueData["original"]["Organisation"];
                    }
                    if (valueData["original"]["Service Type"]) {
                      parsedServicetype = valueData["original"]["Service Type"];
                    }
                    if (valueData["original"]["Service Type Color"]) {
                      parsedStColor =
                        valueData["original"]["Service Type Color"];
                    }
                    if (valueData["original"]["Service Type Icon"]) {
                      parsedStIcon = valueData["original"]["Service Type Icon"];
                    }
                    // Create the div structure
                    const div = document.createElement("div");
                    div.classList.add("views-row");
                    div.dataset.key = key;
                    var linkHTML = "";

                    linkHTML = `
                          <div class="service-provider-details">
                            <div class="service-detail-heading">
                              <div class="view-delete-links">
                                <span class="view-link">
                                  <a href="/en/admin/structure/webform/manage/${valueData["original"]["webformID"]}/submission/${key}/edit">Review and submit</a>
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
                            ${fieldsHTML}
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
            numberChanges = true;
          }
        })
        .catch(function (error) {
          console.error("No offline changes: error", error);
        });

      // Check if localforageUserServiceCreated has any key-value pairs
      localforageUserServiceCreated
        .length()
        .then(function (numberOfKeys) {
          if (numberOfKeys > 0) {
            const rowContainer = document.createElement("div");
            rowContainer.classList.add("row-container");
            rowContainer.classList.add("row-container-created");
            var fieldsHTML = ``;
            localforageUserServiceCreated
              .iterate(function (valueData, key, iterationNumber) {
                // Create the div structure
                const div = document.createElement("div");
                div.classList.add("views-row");
                var linkHTML = "";
                var fieldsHTML = "";
                for (var field in valueData) {
                  if (
                    field !== "service_type_title" &&
                    field !== "service_type" &&
                    field !== "locationID" &&
                    field !== "orgID"
                  ) {
                    var fieldHTML = `
                    <div class="views-field">
                      <span class="views-label">${field}</span>
                      <span class="field-content">
                        <span class="after">${valueData[field]}</span>
                      </span>
                    </div>`;
                    fieldsHTML += fieldHTML;
                  }
                }
                // Define the image source URL
                // Get the base path from Drupal settings
                var basePath = drupalSettings.path.baseUrl;
                var imagePath =
                  basePath +
                  "modules/custom/erpw_webform/images/icons8-brand-new-100.png";
                linkHTML = `
                        <div class="service-provider-details">
                          <div class="service-detail-heading">
                            <div class="view-delete-links">
                              <span class="view-link">
                                <a href="/en/form/${valueData["service_type"]}?key=${key}">Review and submit</a>
                              </span>
                            </div>
                          </div>
                        </div>`;
                div.innerHTML = `
                        <div class="service-providers-submission-row">
                          <div class="row-header">
                            <div class=" service-type-color-logo-container">
                              <div class="service-type-color" style="background-color:#F4CBCA"></div>
                              <div class="service-type-logo new-add-image-div">
                              <img class='new-add-image' src=${imagePath}></img>
                            </div>
                          </div>
                            <div class="service-type-org">
                                <a style="margin-left:10px;pointer-events:none;">${valueData["service_type_title"]}</a>
                            </div>
                        </div>
                        ${fieldsHTML}
                        ${linkHTML}
                        </div>`;
                rowContainer.appendChild(div);
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
            numberCreated = true;
          }
        })
        .catch(function (error) {
          console.error("No offline changes: error", error);
        });

      // Handle empty listing.
      if (numberCreated == true && numberChanges == true) {
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
      }
    }
  });
  Drupal.behaviors.erpwOfflineServicesChangesListing = {
    attach: function (context, settings) {},
  };
})(jQuery, Drupal, drupalSettings, localforage);
