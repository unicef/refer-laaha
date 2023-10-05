(function ($, Drupal, drupalSettings, localforage) {
  var elementCreated = false;
  var additionalInfoClick = [];
  var alreadyExecuted = false;

  window.addEventListener("offline", function (e) {
    // Get the current domain dynamically
    var baseUrl = window.location.protocol + "//" + window.location.host;
    var dynamicValue =
      window.location.pathname.match(/\d+/) === null
        ? null
        : window.location.pathname.match(/\d+/)[0];
    // Define a mapping of view classes to their respective REST export paths.
    let viewsData = {};
    viewsData["view-service-based-service-providers-listing"] =
      "/service-based/%node/export";
    viewsData["block-views-blockmanage-webform-services-block-1"] =
      "/manage-services/export";
    viewsData["block-views-blockwebform-submissions-block-1"] =
      "/webform_submission/rest_export";
    viewsData["view-manage-in-review-webform-services-listing"] =
      "/manage-draft-services/export";

    // Loop through the viewsData array and make AJAX requests.
    for (let viewClass in viewsData) {
      const viewElement = document.querySelector(`.${viewClass}`);
      if (viewElement) {
        const pagerItems = document.querySelector(
          `.${viewClass} .pager .pager__items`
        );
        if (pagerItems) {
          pagerItems.style.display = "none";
        }
        const customElement = document.querySelector(
          "#offline-load-more-button"
        );
        if (customElement) {
          customElement.style.display = "block";
        }
        $(".load-more-button a").on("click", function (event) {
          customElement.style.display = "none";
          if (!navigator.onLine) {
            event.preventDefault(); // Prevent default link behavior
            // Define a mapping of view classes to their respective REST export paths.

            // Loop through the viewsData array and make AJAX requests.

            localforage.config({
              driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
              name: viewClass,
              version: 1.0,
              storeName: viewClass,
            });
            // Assuming you have included LocalForage in your project

            // Iterate through all items in LocalForage
            localforage
              .iterate((value, key, iterationNumber) => {
                // Parse the retrieved value
                const parsedValue = JSON.parse(value);
                // Get all the elements with class "views-row"
                const rows = document.querySelectorAll(".views-row");
                var counter = 0;
                // Loop through each row
                rows.forEach((row) => {
                  // Find the "More Details" link within the row
                  const moreDetailsLink = row
                    .querySelector(".service-provider-details a")
                    .getAttribute("href")
                    .match(/\d+/);
                  var parsedOrganisation = "";
                  var parsedFpNumber = "";
                  var parsedFpName = "";
                  var parsedLocation = "";
                  var parsedServiceMode = "";
                  var parsedServicetype = "";
                  var parsedStColor = "";
                  var parsedStIcon = "";
                  var parsedStatus = "";
                  var parsedSubmittedBy = "";
                  var parsedStatusClass = "";
                  if (key == moreDetailsLink[0]) {
                  } else {
                    if (parsedValue["Organisation"]) {
                      parsedOrganisation = parsedValue["Organisation"];
                    } else {
                      parsedOrganisation = Drupal.t("Not available.");
                    }
                    if (
                      parsedValue[
                        "Name of focal point for referrals (first/last name)"
                      ]
                    ) {
                      parsedFpName =
                        parsedValue[
                          "Name of focal point for referrals (first/last name)"
                        ];
                    } else {
                      parsedFpName = Drupal.t("Not available.");
                    }
                    if (parsedValue["Phone number of focal point"]) {
                      parsedFpNumber =
                        parsedValue["Phone number of focal point"];
                    } else {
                      parsedFpNumber = Drupal.t("Not available.");
                    }
                    if (parsedValue["Location"]) {
                      parsedLocation = parsedValue["Location"];
                    } else {
                      parsedLocation = Drupal.t("Not available.");
                    }
                    if (parsedValue["Service Mode"]) {
                      parsedServiceMode = parsedValue["Service Mode"];
                    } else {
                      parsedServiceMode = Drupal.t("Not available.");
                    }
                    if (parsedValue["Status"]) {
                      parsedStatus = parsedValue["Status"];
                    } else {
                      parsedStatus = Drupal.t("Not available.");
                    }
                    if (parsedValue["Submitted By"]) {
                      parsedSubmittedBy = parsedValue["Submitted By"];
                    } else {
                      parsedSubmittedBy = Drupal.t("Not available.");
                    }
                    if (parsedValue["StatusClass"]) {
                      parsedStatusClass = parsedValue["StatusClass"];
                    } else {
                      parsedStatusClass = "";
                    }
                    if (parsedValue["Service Type"]) {
                      parsedServicetype = parsedValue["Service Type"];
                    }
                    if (parsedValue["Service Type Color"]) {
                      parsedStColor = parsedValue["Service Type Color"];
                    }
                    if (parsedValue["Service Type Icon"]) {
                      parsedStIcon = parsedValue["Service Type Icon"];
                    }
                    // Create the div structure
                    const div = document.createElement("div");
                    div.classList.add("views-row");
                    div.dataset.key = key; // You might need to adjust this based on your structure
                    var linkHTML = "";
                    if (
                      viewClass ==
                      "view-service-based-service-providers-listing"
                    ) {
                      linkHTML = `<div class="service-provider-details">
                        <div class="service-detail-heading">
                          <div class="view-delete-links">
                            <span class="view-link">
                              <a href="/en/service/${key}/approved">More Details</a>
                            </span>
                          </div>
                        </div>
                      </div>`;
                    } else if (
                      viewClass ==
                      "block-views-blockmanage-webform-services-block-1"
                    ) {
                      linkHTML = `<div class="service-provider-details">
                        <div class="service-detail-heading">
                          <div class="edit-delete-links">
                            <span class="edit-link">
                              <a href="/en/admin/structure/webform/manage/${parsedValue["webformID"]}/submission/${key}/edit" style="background:none;">Edit</a>
                            </span>
                          </div>
                        </div>
                      </div>`;
                    } else if (
                      viewClass ==
                      "block-views-blockwebform-submissions-block-1"
                    ) {
                      linkHTML = `<div class="service-provider-details">
                        <div class="service-detail-heading">
                          <div class="view-delete-links">
                            <span class="view-link">
                              <a href="/en/service/${key}">More Details</a>
                            </span>
                          </div>
                        </div>
                      </div>`;
                    } else if (
                      viewClass ==
                      "view-manage-in-review-webform-services-listing"
                    ) {
                      linkHTML = `<div class="service-provider-details">
                        <div class="service-detail-heading">
                          <div class="view-delete-links">
                            <span class="view-link">
                              <a href="/en/service/moderate/${key}">REVIEW</a>
                            </span>
                          </div>
                        </div>
                      </div>`;
                    }
                    if (
                      viewClass ==
                      "block-views-blockwebform-submissions-block-1"
                    ) {
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
                              	<div class="views-field views-field-webform-id">
                                  <span class="views-label views-label-webform-id">Service Type:
                                  </span>
                                  <span class="field-content">
                                    ${parsedServicetype}
                                  </span>
                                </div>
                                <div class="views-field views-field-webform-submission-organisation-field">
                                  <span class="views-label views-label-webform-submission-organisation-field">Organisation:
                                  </span>
                                  <span class="field-content">
                                  ${parsedOrganisation}
                                  </span>
                                </div>
                                <div class="views-field views-field-webform-submission-focal-point-field">
                                  <span class="views-label views-label-webform-submission-focal-point-field">Focal point:
                                  </span>
                                  <span class="field-content">
                                    ${parsedFpName}
                                  </span>
                                </div>
                                <div class="views-field views-field-uid">
                                  <span class="views-label views-label-uid">Submitted by:
                                  </span>
                                  <span class="field-content">
                                    ${parsedSubmittedBy}
                                  </span>
                                </div>
                                <div class="views-field views-field-webform-submission-workflow">
                                  <span class="views-label views-label-webform-submission-workflow">Status:
                                  </span>
                                  <span class="field-content">
                                    <div class="workflow-label-class ${parsedStatusClass}">
                                      ${parsedStatus}</div>
                                  </span>
                                </div>
                              ${linkHTML}
                            </div>`;
                    } else {
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
                    }
                    // Append the div to the view-content div
                    if (counter == 0) {
                      document.querySelector(".view-content").appendChild(div);
                      counter++;
                    }
                  }
                });
              })
              .then(() => {
                console.log("Iteration finished.");
              })
              .catch((error) => {
                console.error("Error during iteration:", error);
              });
          } else {
            const customElement = document.querySelector(
              "#offline-load-more-button"
            );
            customElement.style.display = "none";

            const pagerItems = document.querySelector(
              `.${viewClass} .pager .pager__items`
            );
            pagerItems.style.display = "block";
          }
        });
      }
    }

    // Message for offline
    const mainElement = document.querySelector("main");
    const divElement = document.createElement("div");
    divElement.id = "offline-message-div"; // Assign an ID to the div
    divElement.textContent = Drupal.t(
      "NOTE!! You are viewing this page offline, your internet connectivity is lost. Some features might not work or work differently."
    );
    mainElement.insertBefore(divElement, mainElement.firstChild);
    //  Disable the edit/view more details link
    // Loop through all .views-row elements
    $(".view-content").on(
      "click",
      ".service-detail-heading a",
      function (event) {
        if (!navigator.onLine) {
          event.preventDefault();
        }
      }
    );
    $(".views-row").each(function () {
      if (!navigator.onLine) {
        // Find the anchor tag inside .service-detail-heading
        var anchorTag = $(this).find(".service-detail-heading a");

        // Set pointer events to "none"
        anchorTag.css("pointer-events", "none");
      }
    });
    $(".views-row").each(function () {
      if (!navigator.onLine) {
        // Find the edit anchor tag inside .service-detail-heading
        var anchorTag = $(this).find(
          ".service-detail-heading .edit-delete-links .edit-link a"
        );

        // Set background to "none"
        anchorTag.css("background", "none");
      }
    });

    // Attach click event handler to view items
    $(".view-content").on(
      "click",
      ".views-row .row-header a",
      function (event) {
        if (!navigator.onLine) {
          event.preventDefault(); // Prevent default link behavior
          var viewRow = $(this).closest(".views-row");
          // Get the href value of the <a> tag in .service-detail-heading
          var hrefValue = $(this)
            .closest(".views-row")
            .find(".service-detail-heading a");
          var viewsDataSkip = [];
          viewsDataSkip["view-service-based-service-providers-listing"] = [
            "Organisation",
            "Name of focal point for referrals (first/last name)",
            "Phone number of focal point",
            "Location",
            "Service Mode",
            "Status",
            "Submitted By",
            "sid",
            "Service Type Color",
            "Service Type Icon",
            "webformID",
            "StatusClass",
          ];
          viewsDataSkip["block-views-blockmanage-webform-services-block-1"] = [
            "Organisation",
            "Name of focal point for referrals (first/last name)",
            "Phone number of focal point",
            "Location",
            "Service Mode",
            "Status",
            "Submitted By",
            "sid",
            "Service Type Color",
            "Service Type Icon",
            "webformID",
            "StatusClass",
          ];
          viewsDataSkip["block-views-blockwebform-submissions-block-1"] = [
            "Organisation",
            "Name of focal point for referrals (first/last name)",
            "Submitted By",
            "Status",
            "Service Type",
            "sid",
            "Service Type Color",
            "Service Type Icon",
            "webformID",
            "StatusClass",
          ];
          viewsDataSkip["view-manage-in-review-webform-services-listing"] = [
            "Organisation",
            "Name of focal point for referrals (first/last name)",
            "Phone number of focal point",
            "Location",
            "Service Mode",
            "Status",
            "Submitted By",
            "sid",
            "Service Type Color",
            "Service Type Icon",
            "webformID",
            "StatusClass",
          ];

          if (hrefValue.length) {
            const IdURL = hrefValue.attr("href");
            // Extract the item ID from the edit URL using regular expressions
            const itemIdMatch = IdURL.match(/\/(\d+)(\/|$)/);
            if (itemIdMatch && itemIdMatch[1]) {
              const itemId = itemIdMatch[1];
              localforage
                .getItem(itemId)
                .then((itemData) => {
                  try {
                    if (!additionalInfoClick.includes(itemId)) {
                      // Loop through the viewsData array and make AJAX requests.
                      for (let viewClass in viewsData) {
                        const viewElement = document.querySelector(
                          `.${viewClass}`
                        );
                        if (viewElement) {
                          additionalInfoClick.push(itemId);
                          const container = document.createElement("div");
                          container.id = "additional-details";
                          container.className = "additional-details-offline";
                          container.style.backgroundColor =
                            "rgba(243, 193, 191, 0.53)";
                          container.style.color = "#444";

                          const serviceDetailHeading =
                            document.createElement("div");
                          serviceDetailHeading.className =
                            "service-detail-heading";

                          const h3 = document.createElement("h3");
                          h3.textContent = Drupal.t(
                            "Additional Details (Offline Mode)"
                          );

                          serviceDetailHeading.appendChild(h3);
                          container.appendChild(serviceDetailHeading);
                          for (const key in JSON.parse(itemData)) {
                            if (!viewsDataSkip[viewClass].includes(key)) {
                              const pairContainer =
                                document.createElement("div");
                              pairContainer.className = "pair-container";

                              const label = document.createElement("span");
                              label.className = "label";
                              label.textContent = key + ":";

                              const value = document.createElement("span");
                              value.className = "value";
                              value.textContent = JSON.parse(itemData)[key];

                              pairContainer.appendChild(label);
                              pairContainer.appendChild(value);

                              container.appendChild(pairContainer);
                            }
                          }
                          if (
                            viewClass ==
                            "block-views-blockmanage-webform-services-block-1"
                          ) {
                            // Insert a new div before the "Offline Edit" anchor tag
                            const disclaimer = document.createElement("div");
                            disclaimer.className = "offline-edit-disclaimer";
                            disclaimer.textContent =
                              "*Some fields cannot be edited offline.";
                            container.appendChild(disclaimer);

                            const editLink = document.createElement("a");
                            editLink.href = "#"; // Set the URL you want for the link
                            editLink.textContent = "Make edits offline"; // Set the text for the link
                            editLink.id = "offline-edit"; // Set the ID for the link
                            container.appendChild(editLink);
                          }
                          var lastElement = viewRow[0].querySelector(
                            "div.service-providers-submission-row"
                          ).lastElementChild;
                          viewRow[0]
                            .querySelector(
                              "div.service-providers-submission-row"
                            )
                            .insertBefore(container, lastElement);
                        }
                      }
                    }
                  } catch (error) {
                    console.error("Error parsing offlineItemData:", error);
                  }
                })

                .catch((error) => {
                  console.error(
                    `Error retrieving data from IndexedDB for item ${itemId}`,
                    error
                  );
                });
            }
          }
        }
        return false;
      }
    );

    // Integrate form on edit offline link click.
    $(".view-content").on("click", "#offline-edit", function (event) {
      if (!navigator.onLine) {
        event.preventDefault();

        // Find the edit-link within the same parent div.
        var editLink = $(this)
          .closest(".additional-details-offline")
          .siblings(".service-provider-details")
          .find(".edit-link a");

        const href = editLink.attr("href");
        const idMatch = href.match(/\/(\d+)\/edit$/); // Regex to match the ID
        var id = "";
        if (idMatch) {
          id = idMatch[1]; // Extract the ID from the matched regex
        }
        var saveClass = "offline-save-".concat(id);
        // Check if the "offline-save" anchor already exists
        if ($(".".concat(saveClass)).length === 0) {
          const saveAnchor = document.createElement("a");
          saveAnchor.href = "#"; // Set the URL you want for the link
          saveAnchor.textContent = "Save changes offline"; // Set the text for the link
          saveAnchor.id = "offline-save"; // Set the ID for the link
          saveAnchor.className = saveClass;
          // Insert the new anchor tag after the current element ($(this))
          $(saveAnchor).insertAfter($(this));
        }

        localforage.config({
          driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
          name: "block-views-blockmanage-webform-services-block-1",
          version: 1.0,
          storeName: "block-views-blockmanage-webform-services-block-1",
        });
        // Inside your fetchDataAndStore function:
        localforageForms = localforage.createInstance({
          driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
          name: "serviceFormsData",
          version: 1.0,
          storeName: "serviceFormsData",
        });
        // Check if key already exists
        localforage
          .getItem(id)
          .then((serviceData) => {
            if (serviceData) {
              // Loop through the key-value pairs
              for (const key in JSON.parse(serviceData)) {
                if (key == "webformID") {
                  localforageForms
                    .getItem(JSON.parse(serviceData)[key])
                    .then((serviceFormData) => {
                      // Loop through the elements.
                      for (const elementKey in serviceFormData[
                        "elementsFlattened"
                      ]) {
                        elementData =
                          serviceFormData["elementsFlattened"][elementKey];
                        // Find the nearest .views-row
                        var nearestViewsRow = $(this).closest(
                          ".service-providers-submission-row"
                        );
                        // Find .pair-container elements within the nearest .views-row
                        nearestViewsRow
                          .find(".pair-container")
                          .each(function () {
                            // Get the label and value elements within the pair container
                            var labelElement = $(this).find(".label");
                            var valueElement = $(this).find(".value");

                            // Get the label text
                            var labelText = labelElement.text().trim();
                            if (
                              elementData.hasOwnProperty("#title") &&
                              labelText == elementData["#title"].concat(":")
                            ) {
                              // Check if the label text matches the title in elementData
                              // Check if the type is "textfield"
                              if (
                                (elementData.hasOwnProperty("#type") &&
                                  elementData["#type"] == "tel") ||
                                elementData["#type"] == "textfield" ||
                                elementData["#type"] == "textarea" ||
                                elementData["#type"] == "email"
                              ) {
                                // Add the contentEditable attribute to the value element
                                valueElement.attr("contentEditable", "true");
                              }
                            }
                          });
                        nearestViewsRow.find(".views-field").each(function () {
                          // Get the label and value elements within the pair container
                          var labelElement = $(this).find(".views-label");
                          var valueElement = $(this).find(".field-content");

                          // Get the label text
                          var labelText = labelElement.text().trim();
                          if (
                            labelText == "Contact:" ||
                            labelText == "Focal Point:"
                          ) {
                            valueElement.attr("contentEditable", "true");
                          }
                        });
                      }
                    })
                    .catch((error) =>
                      console.error(
                        `Error checking existing data for key ${id}`,
                        error
                      )
                    );
                }
              }
            } else {
            }
          })
          .catch((error) =>
            console.error(`Error checking existing data for key ${id}`, error)
          );
      }
    });

    // Save the edit offline form.
    // Handle the click event of the new save anchor tag
    $(".view-content").on("click", "#offline-save", function (event) {
      event.preventDefault();
      var currentUserId = drupalSettings.user.uid;
      var serviceID = $(this).attr("class").match(/\d+/)[0];
      // Find the nearest .views-row
      var nearestViewsRow = $(this).closest(
        ".service-providers-submission-row"
      );
      localforage.config({
        driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
        name: "block-views-blockmanage-webform-services-block-1",
        version: 1.0,
        storeName: "block-views-blockmanage-webform-services-block-1",
      });
      // Inside your fetchDataAndStore function:
      localforageUserServiceChanges = localforage.createInstance({
        driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
        name: "userServiceChanges".concat(currentUserId),
        version: 1.0,
        storeName: "userServiceChanges".concat(currentUserId),
      });

      localforage
        .getItem(serviceID)
        .then((serviceData) => {
          if (serviceData) {
            // Initialize an empty object to store the data
            var contentEditableData = {};
            var contentEditableChanges = {};
            var contentChanges = {};
            // Find all elements with contentEditable="true"
            nearestViewsRow
              .find(".field-content[contenteditable='true']")
              .each(function () {
                // Get the label text
                var label = $(this).siblings(".views-label").text().trim();
                var value = $(this).text().trim();
                contentEditableData[label] = value; // Store in the object
                $(this).removeAttr("contenteditable");
              });

            nearestViewsRow
              .find(".pair-container .value[contenteditable='true']")
              .each(function () {
                // Get the label text
                var label = $(this).siblings(".label").text().trim();
                var value = $(this).text().trim(); // Get the value text
                contentEditableData[label] = value; // Store in the object
                $(this).removeAttr("contenteditable");
              });
            // Loop through the key-value pairs
            for (const serviceKey in JSON.parse(serviceData)) {
              serviceKeyData = JSON.parse(serviceData)[serviceKey];

              // Loop through the key-value pairs
              for (const contentKey in contentEditableData) {
                // console.log(JSON.parse(serviceData)[contentKey]);
                if (
                  contentKey == Drupal.t("Contact").concat(":") &&
                  JSON.parse(serviceData)["Phone number of focal point"] ===
                    undefined &&
                  contentEditableData[contentKey] != "Not available."
                ) {
                  contentKeyData = contentEditableData[contentKey];
                  contentEditableChanges["Phone number of focal point"] =
                    contentKeyData;
                } else if (
                  contentKey == Drupal.t("Focal Point").concat(":") &&
                  JSON.parse(serviceData)[
                    "Name of focal point for referrals (first/last name)"
                  ] === undefined &&
                  contentEditableData[contentKey] != "Not available."
                ) {
                  contentKeyData = contentEditableData[contentKey];
                  contentEditableChanges[
                    "Name of focal point for referrals (first/last name)"
                  ] = contentKeyData;
                } else if (
                  (serviceKey ==
                    Drupal.t(
                      "Name of focal point for referrals (first/last name)"
                    ) &&
                    contentKey == Drupal.t("Focal Point").concat(":")) ||
                  (serviceKey == Drupal.t("Phone number of focal point") &&
                    contentKey == Drupal.t("Contact").concat(":")) ||
                  serviceKey.concat(":") == contentKey
                ) {
                  contentKeyData = contentEditableData[contentKey];
                  if (serviceKeyData != contentKeyData) {
                    contentEditableChanges[serviceKey] = contentKeyData;
                  }
                }
              }
            }
            contentChanges["original"] = JSON.parse(serviceData);
            contentChanges["changes"] = contentEditableChanges;
            // If there are changes then only store the data.
            if (Object.keys(contentChanges["changes"]).length !== 0) {
              // Store updated data values in user related indexedDB.
              localforageUserServiceChanges
                .setItem(serviceID, contentChanges)
                .then(() => {
                  console.log(`Data for updated data entered successfully.`);
                })
                .catch((error) => console.error(`Error updating data`, error));
            } else {
              console.log("No changes made.");
            }
          }
        })
        .catch((error) =>
          console.error(
            `Error checking existing data for key ${serviceID}`,
            error
          )
        );
      $(this).remove(); // Remove the new anchor
    });
  });

  Drupal.behaviors.erpwOfflineServices = {
    attach: function (context, settings) {
      $(document).ready(function () {
        // Reminder details
        if (navigator.onLine) {
          // Check if the code has already been executed
          if (alreadyExecuted) {
            return;
          }
          // Set the flag to indicate that the code has been executed
          alreadyExecuted = true;

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
                  const container = document.createElement("div");
                  container.id = "reminder-details";
                  container.className = "reminder-details-offline";
                  container.style.backgroundColor = "rgba(243, 193, 191, 0.53)";

                  const reminderHeading = document.createElement("div");
                  reminderHeading.className = "reminder-detail-heading";
                  reminderHeading.textContent = Drupal.t(
                    "You have made changes to service providers while being offline."
                  );

                  const listingAnchor = document.createElement("a");
                  listingAnchor.href = "/service-providers-changes-offline"; // Set the URL you want for the link
                  listingAnchor.textContent =
                    "Click here review and submit them."; // Set the text for the link
                  listingAnchor.id = "offline-changes-listing"; // Set the ID for the link

                  reminderHeading.appendChild(listingAnchor);
                  container.appendChild(reminderHeading);
                  // Get a reference to the existing <div class="region region-content">
                  var regionContent = document.querySelector(".region-content");

                  // Insert the new <div> as the first child inside the existing <div class="region region-content">
                  regionContent.insertBefore(
                    container,
                    regionContent.firstChild
                  );
                } else {
                  console.log("localforageUserServiceChanges is empty.");
                }
              })
              .catch(function (error) {
                console.error("No offline changes: error", error);
              });
          }
        }

        const addedDiv = document.getElementById("offline-message-div");
        if (addedDiv) {
          addedDiv.remove();
        }
        if (typeof $(".new-service-type a")[0] !== "undefined") {
          $(".new-service-type a")[0].style.pointerEvents = navigator.onLine
            ? "auto"
            : "none";
        }
        const formElements = Array.from($("select"));
        formElements.forEach((element) => {
          element.disabled = false;
        });
        var viewClassFinal = "";
        var localforageID; // Define localforageID here
        let keysArray = [];
        // Define a mapping of view classes to their respective REST export paths.
        let viewsData = {};
        viewsData["view-service-based-service-providers-listing"] =
          "/service-based/%node/export";
        viewsData["block-views-blockmanage-webform-services-block-1"] =
          "/manage-services/export";
        viewsData["block-views-blockwebform-submissions-block-1"] =
          "/webform_submission/rest_export";
        viewsData["view-manage-in-review-webform-services-listing"] =
          "/manage-draft-services/export";

        function fetchDataAndStore() {
          // Get the current domain dynamically
          var baseUrl = window.location.protocol + "//" + window.location.host;
          var dynamicValue =
            window.location.pathname.match(/\d+/) === null
              ? null
              : window.location.pathname.match(/\d+/)[0];

          // Loop through the viewsData array and make AJAX requests.
          for (let viewClass in viewsData) {
            const viewElement = document.querySelector(`.${viewClass}`);
            if (viewElement) {
              // Find the parent nav element
              const pagerNav = document.querySelector(`.${viewClass} .pager`);

              if (pagerNav) {
                // Insert the custom element before the pagerNav
                if (!elementCreated) {
                  // Create the custom element
                  const customElement = document.createElement("div");
                  customElement.id = "offline-load-more-button";
                  customElement.className = "load-more-button";
                  customElement.innerHTML = '<a href="#">Load More</a>';
                  pagerNav.parentNode.insertBefore(
                    customElement,
                    pagerNav.nextSibling
                  );
                  elementCreated = true;
                }
                const customElement = document.querySelector(
                  "#offline-load-more-button"
                );
                customElement.style.display = "none";

                const pagerItems = document.querySelector(
                  `.${viewClass} .pager .pager__items`
                );
                pagerItems.style.display = "block";
              }
              urlFetch = `${baseUrl}${viewsData[viewClass]}`;
              // Modify AJAX request to fetch data from REST export route.
              if (
                viewClass == "view-service-based-service-providers-listing" &&
                dynamicValue != null
              ) {
                urlFetch = `${baseUrl}/service-based/${dynamicValue}/export`;
              }

              localforage.config({
                driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
                name: viewClass,
                version: 1.0,
                storeName: viewClass,
              });
              // Inside your fetchDataAndStore function:
              localforageID = localforage.createInstance({
                driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
                name: viewClass + "IDS",
                version: 1.0,
                storeName: viewClass + "IDS",
              });
              viewClassFinal = viewClass;
              fetch(urlFetch)
                .then((response) => response.json())
                .then((dataArray) => {
                  // Use localForage to store data
                  dataArray.forEach((dataItem) => {
                    try {
                      const webformData = JSON.parse(
                        dataItem.webform_submission_all_data
                      );
                      if (webformData && webformData.sid) {
                        const key = webformData.sid; // Use 'sid' property from the parsed object

                        // Check if key already exists
                        localforage
                          .getItem(key)
                          .then((existingData) => {
                            if (existingData) {
                              // Update the value
                              localforage
                                .setItem(
                                  key,
                                  dataItem.webform_submission_all_data
                                )
                                .then(() => {
                                  console.log(
                                    `Data for key ${key} updated successfully.`
                                  );
                                })
                                .catch((error) =>
                                  console.error(
                                    `Error updating data for key ${key}`,
                                    error
                                  )
                                );
                            } else {
                              // Key doesn't exist, create a new entry
                              localforage
                                .setItem(
                                  key,
                                  dataItem.webform_submission_all_data
                                )
                                .then(() => {
                                  console.log(
                                    `Data for key ${key} stored successfully.`
                                  );
                                })
                                .catch((error) =>
                                  console.error(
                                    `Error storing data for key ${key}`,
                                    error
                                  )
                                );
                            }
                          })
                          .catch((error) =>
                            console.error(
                              `Error checking existing data for key ${key}`,
                              error
                            )
                          );

                        if (!keysArray.includes(key)) {
                          keysArray.push(key);
                        }
                        // Update the value in localforageID with the updated array
                        localforageID
                          .setItem("keys", keysArray)
                          .then(() => {
                            console.log(
                              `Data for keys Array updated successfully.`
                            );
                          })
                          .catch((error) =>
                            console.error(
                              `Error updating data for keys array`,
                              error
                            )
                          );
                      } else {
                        console.error(
                          `'sid' property not found in webform_submission_all_data`,
                          dataItem
                        );
                      }
                    } catch (parseError) {
                      console.error(
                        `Error parsing 'webform_submission_all_data' property`,
                        dataItem
                      );
                    }
                  });
                })
                .catch((error) =>
                  console.error(`Error fetching data from REST endpoint`, error)
                );
            }
          }
        }
        fetchDataAndStore();
        // Check if the user is online and start the interval only if online
        window.addEventListener("online", function (e) {
          window.location.reload(true);
          $("#reminder-details").css("display", "block");
          if (typeof $(".new-service-type a")[0] !== "undefined") {
            $(".new-service-type a")[0].style.pointerEvents = navigator.onLine
              ? "auto"
              : "none";
          }
          formElements.forEach((element) => {
            element.disabled = false;
          });
          const addedDiv = document.getElementById("offline-message-div");
          if (addedDiv) {
            addedDiv.remove();
          }
          fetchDataAndStore(); // Run the code immediately on page load

          // Set up an interval to run the code every 2 minutes
          const intervalTime = 2 * 60 * 1000; // 2 minutes in milliseconds
          setInterval(fetchDataAndStore, intervalTime);
        });
        window.addEventListener("offline", function (e) {
          $("#reminder-details").css("display", "none");
          // disable add more links.
          if (typeof $(".new-service-type a")[0] !== "undefined") {
            $(".new-service-type a")[0].style.pointerEvents = navigator.onLine
              ? "auto"
              : "none";
          }
          // Disable form elements.
          formElements.forEach((element) => {
            element.disabled = true;
          });
        });
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
