<?php
/**
 * The view content for the admin notice used by ratingwidget()->five_star_wp_rate_notice() method.
 * 
 * Generates the HTML content of the "5-star WP rating" message box.
 * The minimum votes required to show this notice is passed from the five_star_wp_rate_notice method to the $VARS variable.
 */

// Import variables
extract($VARS);
?>
<br /><?php echo sprintf(__("Hey, I noticed you just crossed the %d votes on RatingWidget - that's awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation. %s", WP_RW__ID), $min_votes_trigger, '<br /><strong><em>~ Vova Feldman</em></strong>'); ?>
<ul data-nonce="<?php echo wp_create_nonce('rw_five_star_wp_rate_action_nonce'); ?>">
	<li><a data-rate-action="do-rate" href="https://wordpress.org/support/view/plugin-reviews/rating-widget?rate=5#postform"><?php _e('Ok, you deserve it', WP_RW__ID); ?></a></li>
	<li><a data-rate-action="done-rating" href="#"><?php _e('I already did', WP_RW__ID); ?></a></li>
	<li><a data-rate-action="not-enough" href="#"><?php _e('No, not good enough', WP_RW__ID); ?></a></li>
</ul>