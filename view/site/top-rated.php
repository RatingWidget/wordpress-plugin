<?php
extract($VARS);

switch ($created_in) {
	case 'last_year':
		$since_created = WP_RW__TIME_YEAR_IN_SEC;
		break;
	case 'last_6_months':
		$since_created = WP_RW__TIME_YEAR_IN_SEC;
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

$direction = strtolower($direction);

if ('ltr' !== $direction && 'rtl' !== $direction) {
	$direction = 'ltr';
}

$rw_ret_obj = ratingwidget()->GetTopRatedData(array($type), $max_items, 0,
		$min_votes, false, false, $order_by, $order, $since_created);

if (false === $rw_ret_obj || count($rw_ret_obj->data) == 0) {
	echo '';
	exit;
}

$types = ratingwidget()->get_rating_types();

$container_class = 'rw-top-rated-page ' . $direction;
$html = '<div class="' . $container_class . '">';

foreach ($rw_ret_obj->data as $type => $ratings) {
	$rclass = $types[$type]['rclass'];

	if (is_array($ratings) && count($ratings) > 0) {
		$html .= '<div class="rw-top-rated-page-' . $type . '" class="rw-wp-ui-top-rated-list-container">';
		$html .= '<ul class="rw-wp-ui-top-rated-list">';

		$count = 1;
		foreach ($ratings as $rating) {
			$urid = $rating->urid;

			$id = RatingWidgetPlugin::Urid2PostId($urid);
			
			$post = get_post($id);
			
			$title = get_the_title($id);
			$excerpt = ratingwidget()->GetPostExcerpt($post, 15);
			$permalink = get_permalink($id);
			$thumbnail = ratingwidget()->GetPostFeaturedImage($id);
			
			$short = (mb_strlen($title) > 30) ? trim(mb_substr($title, 0, 30)) . "..." : $title;

			ratingwidget()->QueueRatingData($urid, $title, $permalink, $rclass);

			if ($post) { // Skip null $post
			$html .= <<< HTML
<li class="rw-wp-ui-top-rated-list-item">
	<div>
		<b class="rw-wp-ui-top-rated-list-count">$count</b>
		<img class="rw-wp-ui-top-rated-list-item-thumbnail" src="$thumbnail" alt="" />
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

$script .= <<< SCRIPT
<script type="text/javascript">
	// Hook render widget.
	if (typeof(RW_HOOK_READY) === "undefined"){ RW_HOOK_READY = []; }
	RW_HOOK_READY.push(function(){
		RW._foreach(RW._getByClassName("rw-wp-ui-top-rated-list", "ul"), function(list){
			RW._foreach(RW._getByClassName("rw-ui-container", "div", list), function(rating){
				// Deactivate rating.
				RW._Class.remove(rating, "rw-active");
				var i = (RW._getByClassName("rw-report-link", "a", rating))[0];
				if (RW._is(i)){ i.parentNode.removeChild(i); }
			});
		});
	});
</script>
SCRIPT;

$html .= '</div>';

echo $html;
?>