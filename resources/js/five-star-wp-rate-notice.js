(function($) {
	$(document).ready(function() {
		var container = $('.rw-five-star-wp-rate-action');
		if (container.length) {
			container.find('a').click(function() {
				container.remove();
				
				var rateAction = $(this).attr('data-rate-action');
				$.post(
					ajaxurl,
					{
						action: 'rw-five-star-wp-rate',
						rate_action: rateAction,
						_n: container.find('ul:first').attr('data-nonce')
					},
					function(result) {}
				);
		
				if ('do-rate' !== rateAction) {
					return false;
				}
			});
		}
	});
})(jQuery);