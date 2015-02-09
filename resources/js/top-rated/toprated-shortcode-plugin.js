(function($) {
	$(document).ready(function() {
		var topRatedPopupDialog = null;
		
		/* "Max Items" field
		--------------------------------------------------------------------------------------------*/
		var dialogMaxItemsField = {
			name: 'rw-toprated-count',
			label: 'Max Items:',
			type: 'listbox',
			values: [],
			onselect : function() {
				if ('upgrade' === this.value()) {
					topRatedPopupDialog.close();
					window.location.href = RW_TOPRATED_OPTIONS.upgrade_url;
				}
			},
			onPostRender: function() {
				// Default total items
				this.value("10");
			}
		};
		
		updateDialogField(dialogMaxItemsField, RW_TOPRATED_OPTIONS.fields.max_items);
		
		
		/* "Type" field
		--------------------------------------------------------------------------------------------*/
		var dialogTypesField = {
			name: 'rw-toprated-type',
			label: 'Type:',
			type: 'listbox',
			values: []
		};
		
		updateDialogField(dialogTypesField, RW_TOPRATED_OPTIONS.fields.types);
		
		
		/* TinyMCE plugin
		--------------------------------------------------------------------------------------------*/
		tinymce.create('tinymce.plugins.rw_toprated_shortcode_plugin', {
			init : function(editor, url) {
				editor.addCommand('rw_insert_toprated_shortcode', function() {
					if (tinymce.majorVersion >= 4) {
						topRatedPopupDialog = editor.windowManager.open({
							inline: true,
							title: 'Add Top-Rated Table',
							body: [
								dialogTypesField, // Insert the "Type" field
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
					} else {
						topRatedPopupDialog = editor.windowManager.open({
							url: ajaxurl + "?action=rw-toprated-popup-html",
							inline: true,
							width: 360,
							height: 335,
						}, {
							RW_TOPRATED_OPTIONS: RW_TOPRATED_OPTIONS
						});
					}
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
	
	function updateDialogField(dialogField, values) {
		for (var property in values) {
			if (!values.hasOwnProperty(property)) {
				continue;
			}
			
			var text = values[property];
			var value = property;
			
			dialogField.values.push({text: text, value: value});
		}
	}
})(jQuery);