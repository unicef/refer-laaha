(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.erpw_service_rating = {
        attach: function (context, settings) {

            const pathName = window.location.pathname;
            if (pathName.includes('admin/structure/webform/manage/webform_service_rating_') || pathName.includes('webform_service_rating_')) {
                const helpBlock = document.getElementById('block-erpw-help')
                if (helpBlock != null) {
                    helpBlock.style.display = 'none';
                }

                const helpActionsBlock = document.getElementById('block-erpw-local-actions')
                const helpActionsNav = helpActionsBlock.getElementsByClassName('action-links');
                if (helpActionsNav != null) {
                    helpActionsNav[0].style.display = 'none';
                }

                const serviceRatingActions = document.getElementById('rating-action-block');
                if (serviceRatingActions == null) {
                    // The two action buttons = Add new question and publish.
                    let divElement = document.createElement("div");
                    divElement.classList.add('service-rating-action-links')
                    divElement.id = "rating-action-block";
                    let ulElement = document.createElement("ul");

                    // Add new question link
                    const path = pathName.split('/');
                    const service_rating_form_id = path[path.length - 1];
                    let service_id = '';
                    if (service_rating_form_id != null) {
                        service_id = '/' + service_rating_form_id.split('_')[3]
                    } 
                    const pathPrefix = settings.path.baseUrl + settings.path.currentLanguage
                    let addNewQuestion = document.createElement("a");
                    addNewQuestion.href = `${pathPrefix}/add-service-rating-question${service_id}`;
                    addNewQuestion.textContent = "+ Add New Question";
                    let li1 = document.createElement("li");
                    li1.appendChild(addNewQuestion);

                    // Publish link
                    let publish = document.createElement("a");
                    publish.href = "https://example.com/link2";
                    publish.textContent = "Publish";
                    let li2 = document.createElement("li");
                    li2.appendChild(publish);

                    ulElement.appendChild(li1);
                    ulElement.appendChild(li2);
                    divElement.appendChild(ulElement);
                    helpActionsBlock.appendChild(divElement);
                }

            }
        },
    };
})(jQuery, Drupal, drupalSettings);
