<?php
/**
 * Top-rated table view file called by ratingwidget()->get_toprated_from_shortcode($shortcode_atts) method.
 * 
 * Generates the HTML table view for the "ratingwidget_toprated" shortcode.
 * The shortcode attributes are passed from the get_toprated_from_shortcode method to the $VARS variable.
 */

// Extract the shortcode attributes
extract($VARS);

switch ($created_in) {
	case 'last_year':
		$since_created = WP_RW__TIME_YEAR_IN_SEC;
		break;
	case 'last_6_months':
		$since_created = WP_RW__TIME_6_MONTHS_IN_SEC;
		break;
	case 'last_30_days':
		$since_created = WP_RW__TIME_30_DAYS_IN_SEC;
		break;
	case 'last_7_days':
		$since_created = WP_RW__TIME_WEEK_IN_SEC;
		break;
	case 'last_24_hours':
		$since_created = WP_RW__TIME_24_HOURS_IN_SEC;
		break;
	default:
		$since_created = WP_RW__TIME_ALL_TIME;
		break;
}

// Validate the direction attribute
$direction = strtolower($direction);
if ('ltr' !== $direction && 'rtl' !== $direction) {
	$direction = 'ltr';
}

if (!rw_fs()->is_plan_or_trial__premium_only('professional')){
	// Ensure that the maximum number of items is 10 for the free version.
	$max_items = min($max_items, 10);
}

$rw_ret_obj = ratingwidget()->GetTopRatedData(array($type), $max_items, 0,
		$min_votes, false, false, $order_by, $order, $since_created);

if ($rw_ret_obj && count($rw_ret_obj->data)) {
	// Retrieve the rating types settings
	$types = ratingwidget()->get_rating_types();

	$container_class = 'rw-top-rated-page ' . $direction;
	$html = '<div class="' . $container_class . '">';

	foreach ($rw_ret_obj->data as $type => $ratings) {
		// Now, retrieve the rclass from the type settings
		$rclass = $types[$type]['rclass'];

		if (is_array($ratings) && count($ratings) > 0) {
			$html .= '<div class="rw-top-rated-page-' . $type . '" class="rw-wp-ui-top-rated-list-container">';
			$html .= '<ul class="rw-wp-ui-top-rated-list">';

			$count = 1;
			foreach ($ratings as $rating) {
				$urid = $rating->urid;

				$wp_object = null;
				$excerpt = '';
				$permalink = '';
				
				if ('users' === $type) {
					$user_id = RatingWidgetPlugin::Urid2UserId($urid);
					$wp_object = get_user_by('id', $user_id);
					
					if (function_exists('is_buddypress')) {
						$title     = trim(strip_tags(bp_core_get_user_displayname($user_id)));
						$permalink = bp_core_get_user_domain($user_id);
					} else if (function_exists('is_bbpress')) {
						$title     = trim(strip_tags(bbp_get_user_display_name($user_id)));
						$permalink = bbp_get_user_profile_url($user_id);
					} else {
						$wp_object = null;
					}
					
					// If valid WP_User object, retrieve the avatar URL
					if ($wp_object) {
						$thumbnail = ratingwidget()->get_user_avatar($user_id);
					}
				} else {
					$post_id = RatingWidgetPlugin::Urid2PostId($urid);
					$wp_object = get_post($post_id);
					
					$title = get_the_title($post_id);
					$excerpt = ratingwidget()->GetPostExcerpt($wp_object, 15);
					$permalink = get_permalink($post_id);
					$thumbnail = ratingwidget()->GetPostImage($wp_object);
				}
				
				if ($wp_object) { // Skip null object
					if ($thumbnail) {
						$thumbnail = trim($thumbnail);
					}
					if ( empty( $thumbnail ) ) {
                                            $thumbnail = rw_get_plugin_img_url( 'top-rated/placeholder.png' );
					}
					
//					$short = (mb_strlen($title) > 30) ? trim(mb_substr($title, 0, 30)) . "..." : $title;

					$short = $title;

					ratingwidget()->QueueRatingData($urid, $title, $permalink, $rclass);

					$html .= <<< HTML
					<li class="rw-wp-ui-top-rated-list-item">
						<div>
							<b class="rw-wp-ui-top-rated-list-count">$count</b>
							<a href="$permalink"><img class="rw-wp-ui-top-rated-list-item-thumbnail" src="$thumbnail" alt="" /></a>
							<div class="rw-wp-ui-top-rated-list-item-data">
								<div>
									<a class="rw-wp-ui-top-rated-list-item-title" href="$permalink" title="$title">$short</a>
									<div class="rw-ui-container rw-class-$rclass rw-urid-$urid rw-prop-readOnly-true" data-sync="false"></div>
								</div>
								<p class="rw-wp-ui-top-rated-list-item-excerpt">$excerpt</p>
							</div>
						</div>
					</li>
HTML;
					$count++;
				}
			}

			$html .= "</ul>";
			$html .= "</div>";
		}
	}

	// Set a flag that the widget is loaded.
	ratingwidget()->TopRatedWidgetLoaded();

	$html .= '</div>';

	echo $html;
}
?>