<?php
	$bbpress_support = ratingwidget()->IsBBPressInstalled();

	$is_accumulated = ( $bbpress_support ? rw_settings()->is_user_accumulated : false );

?>
<div id="rw_user_rating_type_settings" class="has-sidebar has-right-sidebar">
	<div class="has-sidebar-content">
		<div class="postbox rw-body">
			<h3><?php _erw( 'rating-type' ) ?></h3>

			<div class="inside rw-ui-content-container rw-no-radius">
				<div class="rw-ui-img-radio rw-ui-hor<?php if ( $is_accumulated ) {
					echo ' rw-selected';
				} ?>"<?php if ( ! $bbpress_support ) {
					echo ' data-alert="' . __rw('reputational-rating_pro-only-alert') . '"';
				} ?>>
					<i class="rw-ui-sprite"></i> <input type="radio" name="rw_accumulated_user_rating"
					                                    value="true" <?php if ( $is_accumulated ) {
						echo ' checked="checked"';
					} ?>>
					<span><?php _erw( 'reputational-rating_desc' ) ?></span>
				</div>
				<div class="rw-ui-img-radio rw-ui-hor<?php if ( ! $is_accumulated ) {
					echo ' rw-selected';
				} ?>">
					<i class="rw-ui-sprite"></i> <input type="radio" name="rw_accumulated_user_rating"
					                                    value="false" <?php if ( ! $is_accumulated ) {
						echo ' checked="checked"';
					} ?>>
					<span><?php _erw( 'standard-rating_desc' ) ?></span>
				</div>
			</div>
		</div>
	</div>
</div>
