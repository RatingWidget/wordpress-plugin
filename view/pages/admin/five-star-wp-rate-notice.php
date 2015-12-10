<?php
	/**
	 * The view content for the admin notice used by ratingwidget()->five_star_wp_rate_notice() method.
	 *
	 * Generates the HTML content of the "5-star WP rating" message box.
	 * The minimum votes required to show this notice is passed from the five_star_wp_rate_notice method to the $VARS
	 * variable.
	 */

	// Import variables
	extract( $VARS );
?>
<div>
	<?php echo sprintf( __rw( 'rate-ask_message' ), $min_votes_trigger ) ?>
	<br/><br/>
	<strong><em>~ Vova Feldman</em></strong>
</div>
<ul data-nonce="<?php echo wp_create_nonce( 'rw_five_star_wp_rate_action_nonce' ) ?>">
	<li><a data-rate-action="do-rate"
	       href="https://wordpress.org/support/view/plugin-reviews/rating-widget?rate=5#postform"><?php _erw( 'rate-ask_ok' ) ?></a>
	</li>
	<li><a data-rate-action="done-rating" href="#"><?php _erw( 'rate-ask_already-did' ) ?></a></li>
	<li><a data-rate-action="not-enough" href="#"><?php _erw( 'rate-ask_no' ) ?></a></li>
</ul>