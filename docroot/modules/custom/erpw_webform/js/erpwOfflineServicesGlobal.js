(function ($, Drupal, drupalSettings, localforage) {
  var alreadyExecuted = false; // Flag to track whether the code has already run
  function fetchDataAndStoreForms() {
    // Get the current domain dynamically
    var baseUrl = window.location.protocol + "//" + window.location.host;
    localforage.config({
      driver: localforage.INDEXEDDB,
      name: "serviceFormsData",
      version: 1.0,
      storeName: "serviceFormsData",
    });
    urlFetch = `${baseUrl}/api/erpw-webform-serviceformsexporter`;
    fetch(urlFetch)
      .then((response) => response.json())
      .then((dataArray) => {
        // Use localForage to store data
        for (const key in dataArray) {
          // Check if the key already exists in localforage
          localforage
            .getItem(key)
            .then((existingValue) => {
              // If the key exists, update its value
              if (existingValue !== null) {
                return localforage.setItem(key, existingValue);
              } else {
                // If the key doesn't exist, set it with the new data
                return localforage.setItem(key, dataArray[key]);
              }
            })
            .then(() => {
              console.log(`Form data for ${key} updated successfully.`);
            })
            .catch((error) =>
              console.error(`Error updating form data for ${key}`, error)
            );
        }
      })
      .catch((error) =>
        console.error(`Error fetching data from REST endpoint`, error)
      );
  }

  fetchDataAndStoreForms();
  Drupal.behaviors.erpwOfflineServicesGlobal = {
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
              localforageUserServiceCreated
                .length()
                .then(function (count) {
                  if (numberOfKeys > 0 || count > 0) {
                    const container = document.createElement("div");
                    container.id = "reminder-details";
                    container.className = "reminder-details-offline";
                    container.style.backgroundColor =
                      "rgba(243, 193, 191, 0.53)";

                    const reminderHeading = document.createElement("div");
                    reminderHeading.className = "reminder-detail-heading";
                    reminderHeading.textContent = Drupal.t(
                      "You have made changes to service providers while being offline."
                    );

                    const listingAnchor = document.createElement("a");
                    listingAnchor.href = "/service-providers-changes-offline";
                    listingAnchor.textContent =
                      "Click here review and submit them."; // Set the text for the link
                    listingAnchor.id = "offline-changes-listing"; // Set the ID for the link

                    reminderHeading.appendChild(listingAnchor);
                    container.appendChild(reminderHeading);
                    // Get a reference to the existing <div class="region region-content">
                    var regionContent =
                      document.querySelector(".region-content");

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
            })
            .catch(function (error) {
              console.error("No offline changes: error", error);
            });
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
