(function ($, Drupal, drupalSettings, localforage) {
  var alreadyExecuted = false; // Flag to track whether the code has already run
  Drupal.behaviors.erpwOfflineServiceView = {
    attach: function (context, settings) {
      // Check if the code has already been executed
      if (alreadyExecuted) {
        return;
      }
      // Set the flag to indicate that the code has been executed
      alreadyExecuted = true;

      $(document).ready(function () {
        if (window.location.href.includes("/service-information-offline")) {
          // Parse the URL search parameters
          var urlParams = new URLSearchParams(window.location.search);

          // Get the value of the 'serviceId' parameter from the URL
          var itemId = urlParams.get("serviceId");
          var view = urlParams.get("view");
          localforage.config({
            driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
            name: view,
            version: 1.0,
            storeName: view,
          });
          if (itemId) {
            localforage
              .getItem(itemId)
              .then((itemData) => {
                try {
                  const container = document.createElement("div");
                  container.id = "block-erpw-content";
                  container.className =
                    "block block-system block-system-main-block offline-service-content";

                  const serviceProviderDetails = document.createElement("div");
                  serviceProviderDetails.className = "service-provider-details";

                  const serviceDetailHeading = document.createElement("div");
                  serviceDetailHeading.className = "service-detail-heading";

                  const h3 = document.createElement("h3");
                  h3.textContent = "Service Details";

                  serviceDetailHeading.appendChild(h3);
                  serviceProviderDetails.appendChild(serviceDetailHeading);
                  container.appendChild(serviceProviderDetails);
                  for (const key in JSON.parse(itemData)) {
                    if (key != "sid") {
                      const pairContainer = document.createElement("div");
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
                  document.body.appendChild(container);
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
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
