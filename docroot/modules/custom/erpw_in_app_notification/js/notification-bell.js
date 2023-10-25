(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.notificationHelper = {
			attach: function (context, settings) {
			  // TODO
			},
		};
})(jQuery, Drupal, drupalSettings);

let count = 0;
function increaseCount() {
	count++;
	document.getElementById('notificationCount').innerText = count;
}