(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.notificationHelper = {
		attach: function (context, settings) {
		// Set the interval time in milliseconds (e.g., 5000ms for 5 seconds).
		var intervalTime = 60000;

		// Define the AJAX call function to be executed at the specified interval.
		setInterval(function () {
			// Perform your AJAX call here.
			$.ajax({
			url: 'ajax/getnotificaitoncount', // Replace with your custom AJAX path.
			type: 'GET', // Use the appropriate HTTP method.
			dataType: 'json',
			success: function (response) {
				document.getElementById('notificationCount').innerText = response.count;
			},
			});
		}, intervalTime);
		},
	};
})(jQuery, Drupal, drupalSettings);