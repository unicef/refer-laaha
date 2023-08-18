(function ($, Drupal, drupalSettings, localforage) {
  Drupal.behaviors.erpwOfflineServices = {
    attach: function (context, settings) {
      $(document).ready(function () {
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
      });
    },
  };
})(jQuery, Drupal, drupalSettings, localforage);
