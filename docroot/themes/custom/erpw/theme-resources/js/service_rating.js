(function ($, Drupal, drupalSettings, once) {
    let questionFlag = false;
    Drupal.behaviors.erpw_service_rating = {
        attach: function (context, settings) {

            const pathName = window.location.pathname;
            const path = pathName.split('/');
            const service_rating_form_id = path[path.length - 1];
            if (pathName.includes('admin/structure/webform/manage/webform_service_rating_')) {
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
                    let service_id = '';
                    if (service_rating_form_id != null) {
                        service_id = '/' + service_rating_form_id.split('_')[3]
                    }
                    const pathPrefix = settings.path.baseUrl + settings.path.currentLanguage
                    let addNewQuestion = document.createElement("a");
                    addNewQuestion.classList.add('add-new-question');
                    addNewQuestion.href = `${pathPrefix}/add-service-rating-question${service_id}`;
                    addNewQuestion.textContent = "+ Add New Question";
                    let li1 = document.createElement("li");
                    li1.appendChild(addNewQuestion);

                    // Publish button
                    let publish = document.createElement("button");
                    // publish.textContent = "Publish";
                    publish.id = "service-rating-form-publish-btn";
                    publish.textContent = 'Loading..'

                    $.ajax({
                        url: `/service-rating-form/${service_rating_form_id}`,
                        method: 'GET',
                        success: function (data) {
                            console.log(`DATA`, data)
                            document.getElementById('service-rating-form-publish-btn').textContent = data.form_status;
                        },
                        error: function (xhr, status, error) {
                            // Handle errors
                            console.error('Ajax request failed: ' + error);
                        }
                    });

                    let li2 = document.createElement("li");
                    li2.appendChild(publish);

                    ulElement.appendChild(li1);
                    ulElement.appendChild(li2);
                    divElement.appendChild(ulElement);
                    helpActionsBlock.append(divElement);
                }
            }

            $(once("click", "#service-rating-form-publish-btn")).on("click", function (event) {
                event.preventDefault();
                $.ajax({
                    url: `/service-rating-form/publish/${service_rating_form_id}`,
                    method: 'POST',
                    success: function (data) {
                        document.getElementById('service-rating-form-publish-btn').textContent = data.form_button_text;
                    },
                    error: function (xhr, status, error) {
                        // Handle errors
                        console.error('Ajax request failed: ' + error);
                    }
                });
            });

            if (!questionFlag) {
                if (pathName.includes('service-feedback-form')) {
                    const questions = document.getElementsByClassName('fieldset-legend');
                    if (questions != null && questions.length > 0) {
                        let question_number = 1;
                        for (let i = 0; i < questions.length; i++) {
                            questions[i].textContent = question_number.toString() + '. ' + questions[i].textContent;
                            question_number += 1;
                        }
                    }
                }
                questionFlag = true;
            }

            // Overall average star ratings.
            const averageStarRatings = document.getElementsByClassName('average-service-ratings-box')[0];
            if (averageStarRatings != null && averageStarRatings.children != null) {
                const avgRatingValue = averageStarRatings.children[0]?.children?.[0]?.textContent;
                if (avgRatingValue != null) {
                    const stars = document.getElementById('overall-average-ratings').children
                    for (let i = avgRatingValue; i < 5; i++) {
                        stars[i].classList.add('star-empty')
                    }
                }
            }

            // Star ratings
            const serviceRatingsList = document.getElementsByClassName('service-ratings-services-list')[0];
            if (serviceRatingsList != null && serviceRatingsList.children != null) {
                for (rating of serviceRatingsList.children) {
                    const ratingValue = rating.getElementsByClassName('service-average-rating')[0]?.innerText.replace(/\s/g, "");
                    if (ratingValue != null) {
                        // const stars = rating.getElementById(`service-star-rating-${ratingValue}`).children
                        const stars = rating.children[2].children
                        for (let i = ratingValue; i < 5; i++) {
                            stars[i].classList.add('star-empty')
                        }
                        if (ratingValue > 3) {
                            rating.getElementsByClassName('rating-arrow-image')[0].classList.add('rating-arrow-image-green');
                        } else {
                            rating.getElementsByClassName('rating-arrow-image')[0].classList.add('rating-arrow-image-red');
                        }
                    }
                }
            }

            const webformTable  = document.getElementsByClassName('webform-forms');
            if (webformTable != null) {
                const tableBody = webformTable[0]?.children?.[1]
                if (tableBody != null) {
                    const forms = tableBody?.children;
                    for (form of forms) {
                        const formRow = form;
                        const td = form?.children[0]?.children?.[0]?.attributes?.[0]?.value;
                        if (td?.includes('webform-service-rating')) {
                            form.style.display = "none";
                        }
                    }
                }
            }
        },
    };
})(jQuery, Drupal, drupalSettings, once);
