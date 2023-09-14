(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.location = {
      attach: function (context, settings) {
        $(document).ready(function () {
          const downloadProceedButton = document.getElementById('edit-proceed');
          downloadProceedButton.addEventListener('click', () => {
            const downloadMessageSectionBox = document.getElementById('download-message-box');
            const downloadMessageSection = document.getElementById('download-message');
            downloadMessageSection.innerHTML = "File download successful, please wait for the download to finish."
            downloadMessageSectionBox.style.display = 'flex';
          });
        });
      },
    };
  })(jQuery, Drupal, drupalSettings);
  