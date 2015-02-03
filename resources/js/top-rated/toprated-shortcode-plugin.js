(function($) {
	$(document).ready(function() {
		var topRatedPopupDialog = null;
		
		// Initialize the "Max Items" field --------------------------------------------
		var dialogMaxItemsField = {
			name: 'rw-toprated-count',
			label: 'Max Items:',
			type: 'listbox',
			values: [],
			onselect : function() {
				if ('upgrade' === this.value()) {
					topRatedPopupDialog.close();
					window.location.href = 'admin.php?page=rating-widget-pricing';
				}
			},
			onPostRender: function() {
				// Default total items
				this.value("5");
			}
		};
		
		var nonProfessionalLimit = 11;
		var limit = (RW._isProfessional() || RW._isTrial()) ? 50 : nonProfessionalLimit;
		
		for (var counter = 1; counter <= limit; counter++) {
			var text = counter.toString();
			var value = counter;
			
			if (nonProfessionalLimit === counter && nonProfessionalLimit === limit) {
				text = 'Upgrade to Professional for 50 Items';
				value = 'upgrade';
			}
			
			dialogMaxItemsField.values.push({text: text, value: value});
		}
		// -----------------------------------------------------------------------------		
	
		// Initialize the "Type" field -------------------------------------------------
		var dialogTypeField = {
			name: 'rw-toprated-type',
			label: 'Type:',
			type: 'listbox',
			values: [
				{text: 'Pages', value: 'pages'},
				{text: 'Posts', value: 'posts'}
			]
		};
		
		if (RW_TOPRATED_OPTIONS.woocommerce_installed) {
			dialogTypeField.values.push({text: 'Products', value: 'products'});
		}
		
		if (RW_TOPRATED_OPTIONS.bbpress_installed) {
			dialogTypeField.values.push({text: 'Topics', value: 'forum_posts'});
		}
		
		if (RW_TOPRATED_OPTIONS.bbpress_installed || RW_TOPRATED_OPTIONS.buddypress_installed) {
			dialogTypeField.values.push({text: 'Users', value: 'users'});
		}
		// -----------------------------------------------------------------------------		
	
		tinymce.create('tinymce.plugins.rw_toprated_shortcode_plugin', {
			init : function(editor, url) {
				editor.addCommand('rw_insert_toprated_shortcode', function() {
					topRatedPopupDialog = editor.windowManager.open({
						title: 'Add Top-Rated Table',
						body: [
							dialogTypeField, // Insert the "Type" field
							{
								id: 'rw-toprated-direction',
								label: 'Direction:',
								type: 'container',
								html: '<label class="rw-toprated-ltr"><input name="rw-toprated-direction" value="ltr" type="radio" checked /> <span>Left to Right</span></label> <label><input name="rw-toprated-direction" value="rtl" type="radio" /> <span>Right to Left</span></label>'
							},
							{
								id: 'rw-toprated-min-votes',
								name: 'rw-toprated-min-votes',
								label: 'Min Votes (>=1):',
								type: 'textbox',
								onPostRender: function() {
									// Minimum value should be 1
									this.value("1");
								}
							},
							dialogMaxItemsField, // Insert the "Max Items" field
							{
								name: 'rw-toprated-orderby',
								label: 'Order By:',
								type: 'listbox',
								values: [
									{text: 'Average Rate', value: 'avgrate'},
									{text: 'Votes Number', value: 'votes'},
									{text: 'Likes (for Thumbs)', value: 'likes'},
									{text: 'Created', value: 'created'},
									{text: 'Updated', value: 'updated'}
								]
							},
							{
								name: 'rw-toprated-order',
								label: 'Order:',
								type: 'listbox',
								values: [
									{text: 'BEST (Descending)', value: 'DESC'},
									{text: 'WORST (Ascending)', value: 'ASC'}
								]
							},
							{
								name: 'rw-toprated-created-in',
								label: 'Created In:',
								type: 'listbox',
								values: [
									{text: 'All Time', value: 'all_time'},
									{text: 'Last Year', value: 'last_year'},
									{text: 'Last 6 Months', value: 'last_6_months'},
									{text: 'Last 30 Days', value: 'last_30_days'},
									{text: 'Last 7 Days', value: 'last_7_days'},
									{text: 'Last 24 Hours', value: 'last_24_hours'}
								]
							},
							{
								type: 'container',
								html: '<span id="rw-toprated-note">Note: After clicking "Add Table", a special shortcode will be<br>added to your editor. It would be rendered as a beautiful<br>table only in your site.</span>'
							}
						],
						buttons: [{
							id: 'rw-toprated-insert-shortcode',
							classes: 'widget btn primary first abs-layout-item',
							text: "Add Table",
							onclick: function() {
								topRatedPopupDialog.submit();
							}
						}],
						onsubmit: function(e) {
							var data = e.data;
							var shortcode = '[ratingwidget_toprated'
										+ ' type="' + data['rw-toprated-type'] + '"'
										+ ' created_in="' + data['rw-toprated-created-in'] + '"'
										+ ' direction="' + $("[name='rw-toprated-direction']:checked").val() + '"'
										+ ' max_items="' + data['rw-toprated-count'] + '"'
										+ ' min_votes="' + data['rw-toprated-min-votes'] + '"'
										+ ' order="' + data['rw-toprated-order'] + '"'
										+ ' order_by="' + data['rw-toprated-orderby'] + '"'
										+ ']';
								
							// Insert the shortcode into the post's edit page textarea.
							tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
							
							topRatedPopupDialog.close();
						}
					});
				});
				
				editor.addButton('rw_toprated_shortcode_button', {
					title : 'Add Top-Rated Table (by RatingWidget)',
					cmd : 'rw_insert_toprated_shortcode',
					image: $('#toplevel_page_rating-widget .wp-menu-image img:first').attr('src')
				});
			},   
		});

		tinymce.PluginManager.add('rw_toprated_shortcode_button', tinymce.plugins.rw_toprated_shortcode_plugin);
	});
})(jQuery);