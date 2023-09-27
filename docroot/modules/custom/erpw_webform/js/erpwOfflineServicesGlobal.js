(function ($, Drupal, drupalSettings, localforage) {
  Drupal.behaviors.erpwOfflineServicesGlobal = {
    attach: function (context, settings) {
      $(document).ready(function () {
        function fetchDataAndStoreForms() {
          // Get the current domain dynamically
          var baseUrl = window.location.protocol + "//" + window.location.host;
          localforage.config({
            driver: localforage.INDEXEDDB, // You can choose the storage driver you prefer
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
                      // Merge the new data with the existing data if needed
                      // For example, if dataArray[key] is an array, you can concatenate or merge it with existingValue
                      // Example: existingValue.concat(dataArray[key]);
                      // Then, update the key with the merged value
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
        // Check if the user is online and start the interval only if online
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
