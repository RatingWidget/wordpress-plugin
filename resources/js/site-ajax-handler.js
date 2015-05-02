(function($) {
	$(document).ajaxComplete(function(event, request, settings) {
		// Retrieve the data of an AJAX request
		var queryStr = "?" + settings.data;

		var action = getParameterByName(queryStr, 'action');
		var cookie = getParameterByName(queryStr, 'cookie');

		// Check if BuddyPress is inserting new status update or comment
		if (action && cookie && 0 === cookie.indexOf('bp-activity')) {
			if ('post_update' == action || 'new_activity_comment' == action) {
				// Wait for BuddyPress' post success callback to finish executing
				// then render the new rating
				var timer = setTimeout(function() {
					var container = null;

					if ('post_update' == action) {
						if ($('#activity-stream').length) {
							// Retrieve the container of the new status update's rating
							var containerId = $(request.responseText).attr('id');
							container = $('#' + containerId).find('.activity-meta:first').get(0);
						}
					} else {
						// Retrieve the container of the new comment's rating
						var containerId = $(request.responseText).attr('id');
						container = RW._getById(containerId);
					}

					// Only render new rating
					RW.render(null, false, container);

					clearTimeout(timer);
				}, 500);
			}
		}
	});


	/**
	 * Helper function for retrieving HTTP query values
	 */
	function getParameterByName(queryStr, name) {
		var match = RegExp('[?&]' + name + '=([^&]*)').exec(queryStr);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}
})(jQuery);