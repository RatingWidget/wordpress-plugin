<?php
/**
 * Dashboard statistics view file called by ratingwidget()->stats_widget_callback() method.
 * 
 * Generates the HTML content of the dashboard statistics widget.
 * The ratings and votes counts are passed from the stats_widget_callback method to the $VARS variable.
 */

// Import variables
extract($VARS);
?>
<div class="rw-stats-container">
	<div class="rw-stats-width-half rw-stats-numbers">
		<span><?php
			//English notation
			echo number_format($ratings);
		?></span>
		<?php echo _n('Rating', 'Ratings', $ratings, WP_RW__ID); ?>
	</div> 
	<div class="rw-stats-width-half rw-stats-numbers">
		<span><?php echo number_format($votes); ?></span>
		<?php echo _n('Vote', 'Votes', $votes, WP_RW__ID); ?>
	</div>
	<div class="rw-stats-width-full rw-stats-pos-bottom">
		<div class="rw-stats-share-icons clear">
			<p>
				<a href="https://twitter.com/ratingwidget" target="blank" class="rw-stats-share-link">
					<span class="rw-stats-icon rw-stats-icon-twitter"></span>
					<span class="rw-stats-icon-label"><?php _e('Follow us on Twitter', WP_RW__ID); ?></span>
				</a>
				<a href="https://www.facebook.com/rating.widget" target="blank" class="rw-stats-share-link">
					<span class="rw-stats-icon rw-stats-icon-facebook"></span>
					<span class="rw-stats-icon-label"><?php _e('Like us on Facebook', WP_RW__ID); ?></span>
				</a>
			</p>
		</div>
	</div>  
</div>