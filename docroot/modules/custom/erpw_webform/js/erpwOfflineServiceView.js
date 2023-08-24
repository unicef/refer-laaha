(function ($, Drupal) {
  Drupal.behaviors.erpwOfflineServiceView = {
    attach: function (context, settings) {
      $(document).ready(function () {
        if (window.location.href.includes("/service-information-offline")) {
          // Retrieve the data from localStorage
          const data = JSON.parse(localStorage.getItem("offlineItemData"));

          if (data) {
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
              for (const key in data) {
                if (key != "sid") {
                  const pairContainer = document.createElement("div");
                  pairContainer.className = "pair-container";

                  const label = document.createElement("span");
                  label.className = "label";
                  label.textContent = key + ":";

                  const value = document.createElement("span");
                  value.className = "value";
                  value.textContent = data[key];

                  pairContainer.appendChild(label);
                  pairContainer.appendChild(value);

                  container.appendChild(pairContainer);
                }
              }
              document.body.appendChild(container);
              localStorage.removeItem("offlineItemData");
            } catch (error) {
              console.error("Error parsing offlineItemData:", error);
            }
          }
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
