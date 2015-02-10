<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<title><?php _e('Add Top-Rated Table', WP_RW__ID); ?></title>
		<link rel="stylesheet" href="<?php echo WP_RW__PLUGIN_URL ?>resources/css/toprated-shortcode-old.css"/>
	</head>
	<body>
		<form>
			<div class="tabs">
				<ul>
					<li id="rw-options-tab" aria-controls="rw-options-panel" class="current"><span><?php _e('Table Options', WP_RW__ID); ?></span></li>
				</ul>
			</div>
			<div id="rw-panel-wrapper">
				<div id="rw-options-panel" class="panel current">
					<table>
						<tr>
							<th><label for="rw-toprated-type"><?php _e('Type', WP_RW__ID); ?>:</label></th>
							<td>
								<select id='rw-toprated-type'>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Direction', WP_RW__ID); ?>:</th>
							<td>
								<div id='rw-toprated-direction'>
									<label class="rw-toprated-ltr"><input name="rw-toprated-direction" value="ltr" type="radio" checked /> <span><?php _e('Left to Right', WP_RW__ID); ?></span></label> <label><input name="rw-toprated-direction" value="rtl" type="radio" /> <span><?php _e('Right to Left', WP_RW__ID); ?></span></label>
								</div>
							</td>
						</tr>
						<tr>
							<th><label for="rw-toprated-min-votes"><?php _e('Min Votes', WP_RW__ID); ?> (>=1):</label></th>
							<td>
								<input type="text" id='rw-toprated-min-votes' value="1"/>
							</td>
						</tr>
						<tr>
							<th><label for="rw-toprated-count"><?php _e('Max Items', WP_RW__ID); ?>:</label></th>
							<td>
								<select id='rw-toprated-count'>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="rw-toprated-orderby"><?php _e('Order By', WP_RW__ID); ?>:</label></th>
							<td>
								<select id='rw-toprated-orderby'>
									<option value="avgrate"><?php _e('Average Rate', WP_RW__ID); ?></option>
									<option value="votes"><?php _e('Votes Number', WP_RW__ID); ?></option>
									<option value="likes"><?php _e('Likes (for Thumbs)', WP_RW__ID); ?></option>
									<option value="created"><?php _e('Created', WP_RW__ID); ?></option>
									<option value="updated"><?php _e('Updated', WP_RW__ID); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="rw-toprated-order"><?php _e('Order', WP_RW__ID); ?>:</label></th>
							<td>
								<select id='rw-toprated-order'>
									<option value="DESC"><?php _e('BEST (Descending)', WP_RW__ID); ?></option>
									<option value="ASC"><?php _e('WORST (Ascending)', WP_RW__ID); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="rw-toprated-created-in"><?php _e('Created In', WP_RW__ID); ?>:</label></th>
							<td>
								<select id='rw-toprated-created-in'>
									<option value="all_time"><?php _e('All Time', WP_RW__ID); ?></option>
									<option value="last_year"><?php _e('Last Year', WP_RW__ID); ?></option>
									<option value="last_6_months"><?php _e('Last 6 Months', WP_RW__ID); ?></option>
									<option value="last_30_days"><?php _e('Last 30 Days', WP_RW__ID); ?></option>
									<option value="last_7_days"><?php _e('Last 7 Days', WP_RW__ID); ?></option>
									<option value="last_24_hours"><?php _e('Last 24 Hours', WP_RW__ID); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<div id="rw-toprated-note"><?php _e('Note: After clicking "Add Table", a special shortcode will be added to your editor. It would be rendered as a beautiful table only in your site.', WP_RW__ID); ?></div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="mceActionPanel">
				<input type="submit" id="rw-toprated-insert-shortcode" value="<?php _e('Add Table', WP_RW__ID); ?>" />
			</div>
		</form>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo includes_url('js/tinymce/tiny_mce_popup.js'); ?>"></script>
		<script type="text/javascript">
			(function($) {
				// Helper function for initializing the values of an <option> element
				function updateDialogField($dialogField, values, defaultValue) {
					for (var property in values) {
						if (!values.hasOwnProperty(property)) {
							continue;
						}

						var text = values[property];
						var value = property;
						
						var $option = $('<option value="' + value + '">' + text + '</option>');
						$dialogField.append($option);
					}
					
					if (defaultValue) {
						$dialogField.val(defaultValue);
					}
				}
				
				// Retrieve the passed options
				var RW_TOPRATED_OPTIONS = tinyMCEPopup.getWindowArg('RW_TOPRATED_OPTIONS');
				
				updateDialogField($('#rw-toprated-count'), RW_TOPRATED_OPTIONS.fields.max_items, 10);
				updateDialogField($('#rw-toprated-type'), RW_TOPRATED_OPTIONS.fields.types);
				
				// Event handlers
				$('#rw-toprated-count').on('change', function() {
					var val = $(this).val();

					if ('upgrade' === val) {
						// Redirect to the pricing page
						window.top.location.href = RW_TOPRATED_OPTIONS.upgrade_url;
						tinyMCEPopup.close();
					}
				});

				$('#rw-toprated-insert-shortcode').on('click', function() {
					// Generate the shortcode and insert it to the editor
					var shortcode = '[ratingwidget_toprated'
								+ ' type="' + $('#rw-toprated-type').val() + '"'
								+ ' created_in="' + $('#rw-toprated-created-in').val() + '"'
								+ ' direction="' + $("[name='rw-toprated-direction']:checked").val() + '"'
								+ ' max_items="' + $('#rw-toprated-count').val() + '"'
								+ ' min_votes="' + $('#rw-toprated-min-votes').val() + '"'
								+ ' order="' + $('#rw-toprated-order').val() + '"'
								+ ' order_by="' + $('#rw-toprated-orderby').val() + '"'
								+ ']';

					tinyMCEPopup.editor.execCommand('mceInsertContent', 0, shortcode);
					tinyMCEPopup.close();
					return false;
				});
			})(jQuery);
		</script>
	</body>
</html>