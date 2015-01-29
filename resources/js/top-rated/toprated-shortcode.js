(function($) {
	$(document).ready(function() {
		$('body').on('click', '#rw-toprated-insert-shortcode', function() {
			var shortcode = '[ratingwidget_toprated'
						+ ' type="' + $("#rw-toprated-type").val() + '"'
						+ ' created_in="' + $("#rw-toprated-created-in").val() + '"'
						+ ' direction="' + $("[name='rw-toprated-direction']:checked").val() + '"'
						+ ' max_items="' + $("#rw-toprated-count").val() + '"'
						+ ' min_votes="' + $("#rw-toprated-min-votes").val() + '"'
						+ ' order="' + $("#rw-toprated-order").val() + '"'
						+ ' order_by="' + $("#rw-toprated-orderby").val() + '"'
						+ ']';
			
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			$('#rw-toprated-shortcode-dialog').dialog('close');
		});
	});
	
})(jQuery);