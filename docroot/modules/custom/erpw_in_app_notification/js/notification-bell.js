(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.notificationHelper = {
		attach: function (context, settings) {
		// Set the interval time in milliseconds (e.g., 5000ms for 5 seconds).
    var intervalTime = 60000;
    var bellIconImg = document.querySelector('.notification-bell-icon');
    var notificationNum = document.getElementById('notificationCount').innerText;
    if (notificationNum > 0) {
      bellIconImg.classList.add('scaled');
    } else {
      bellIconImg.classList.remove('scaled');
    }

    // Define the AJAX call function to be executed at the specified interval.
    setInterval(function () {
      // Perform your AJAX call here.
      $.ajax({
      url: 'ajax/getnotificaitoncount', // Replace with your custom AJAX path.
      type: 'GET', // Use the appropriate HTTP method.
      dataType: 'json',
      success: function (response) {
        var bellIcon = document.querySelector('.notification-bell-icon');
        var notificationValue = response.count;
        document.getElementById('notificationCount').innerText = notificationValue;
        if (notificationValue > 0) {
          bellIcon.classList.add('scaled');
        } else {
          bellIcon.classList.remove('scaled');
        }
      },
      });
    }, intervalTime);
    },
  };
})(jQuery, Drupal, drupalSettings);
