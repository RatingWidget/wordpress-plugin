<!DOCTYPE html>
<html>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php _erw( 'toprated-table-win_title' ) ?></title>
	<link rel="stylesheet" href="<?php echo WP_RW__PLUGIN_URL ?>resources/css/toprated-shortcode-old.css"/>
</head>
<body>
<form>
	<div class="tabs">
		<ul>
			<li id="rw-options-tab" aria-controls="rw-options-panel" class="current">
				<span><?php _erw( 'table-options' ) ?></span></li>
		</ul>
	</div>
	<div id="rw-panel-wrapper">
		<div id="rw-options-panel" class="panel current">
			<table>
				<tr>
					<th><label for="rw-toprated-type"><?php _erw( 'type' ) ?>:</label></th>
					<td>
						<select id='rw-toprated-type'>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _erw( 'direction' ) ?>:</th>
					<td>
						<div id='rw-toprated-direction'>
							<label class="rw-toprated-ltr"><input name="rw-toprated-direction" value="ltr" type="radio"
							                                      checked/>
								<span><?php _erw( 'ltr' ) ?></span></label> <label><input
									name="rw-toprated-direction" value="rtl" type="radio"/>
								<span><?php _erw( 'rtl' ) ?></span></label>
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="rw-toprated-min-votes"><?php _erw( 'min-votes' ) ?> (>=1):</label></th>
					<td>
						<input type="text" id='rw-toprated-min-votes' value="1"/>
					</td>
				</tr>
				<tr>
					<th><label for="rw-toprated-count"><?php _erw( 'max-items' ) ?>:</label></th>
					<td>
						<select id='rw-toprated-count'>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="rw-toprated-orderby"><?php _erw( 'orderby' ) ?>:</label></th>
					<td>
						<select id='rw-toprated-orderby'>
							<option value="avgrate"><?php _erw( 'average-rate' ) ?></option>
							<option value="votes"><?php _erw( 'votes-number' ) ?></option>
							<option value="likes"><?php _erw( 'likes-for-thumbs' ) ?></option>
							<option value="created"><?php _erw( 'created' ) ?></option>
							<option value="updated"><?php _erw( 'last-updated' ) ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="rw-toprated-order"><?php _erw( 'order' ) ?>:</label></th>
					<td>
						<select id='rw-toprated-order'>
							<option value="DESC"><?php _erw( 'best' ) ?> (<?php _erw( 'descending' ) ?>)</option>
							<option value="ASC"><?php _erw( 'worst' ) ?> (<?php _erw( 'ascending' ) ?>)</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="rw-toprated-created-in"><?php _erw( 'created-in' ) ?>:</label></th>
					<td>
						<select id='rw-toprated-created-in'>
							<option value="all_time"><?php _erw( 'all-time' ) ?></option>
							<option value="last_year"><?php _erw( 'last-year' ) ?></option>
							<option value="last_6_months"><?php printf( __rw( 'last-x-months' ), 6 ) ?></option>
							<option value="last_30_days"><?php printf( __rw( 'last-x-days' ), 30 ) ?></option>
							<option value="last_7_days"><?php printf( __rw( 'last-x-days' ), 7 ) ?></option>
							<option value="last_24_hours"><?php printf( __rw( 'last-x-hours' ), 24 ) ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<div
							id="rw-toprated-note"><?php _erw( 'add-table_shortcode-desc' ) ?></div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="mceActionPanel">
		<input type="submit" id="rw-toprated-insert-shortcode" value="<?php _erw( 'add-table' ) ?>"/>
	</div>
</form>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo includes_url( 'js/tinymce/tiny_mce_popup.js' ); ?>"></script>
<script type="text/javascript">
	(function ($) {
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
		$('#rw-toprated-count').on('change', function () {
			var val = $(this).val();

			if ('upgrade' === val) {
				// Redirect to the pricing page
				window.top.location.href = RW_TOPRATED_OPTIONS.upgrade_url;
				tinyMCEPopup.close();
			}
		});

		$('#rw-toprated-insert-shortcode').on('click', function () {
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