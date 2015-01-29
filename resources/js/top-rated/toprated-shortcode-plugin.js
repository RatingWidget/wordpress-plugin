(function($) {
	$(document).ready(function() {
		tinymce.create('tinymce.plugins.rw_toprated_shortcode_plugin', {
			init : function(ed, url) {
				ed.addCommand('rw_insert_toprated_shortcode', function() {
					$('#rw-toprated-shortcode-dialog').dialog({
						title: 'Insert Top-rated Shortcode',
						width: 'auto',
						height: 'auto',
						modal: true,
						fluid: true,
						resizable: false

					});
				});

				ed.addButton('rw_toprated_shortcode_button', {title : 'Rating-Widget Top-Rated Shortcode', cmd : 'rw_insert_toprated_shortcode', image: url + '/../../../icon.png' });
			},   
		});

		tinymce.PluginManager.add('rw_toprated_shortcode_button', tinymce.plugins.rw_toprated_shortcode_plugin);

		$(document).ready(function() {
			var data = { 
				action: 'rw_create_toprated_shortcode'
			}
			
			$.post(ajaxurl, data, function(button_content) {
				var response=button_content;
				$(response).appendTo('body').hide();  
			}); 
		});
	});
})(jQuery);