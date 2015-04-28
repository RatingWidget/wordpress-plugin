<?php
    global $post, $rwp;

    $post_type = get_post_type($post);
    $readonly_post = (true === $rwp->is_rating_readonly($post->ID, $post_type));

    switch ($post_type)
    {
        case 'page':
            $excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'page'));
            $rclass = 'page';
            break;
        case 'product':
            $excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'collection-product') && false === $rwp->rw_validate_visibility($post->ID, 'product'));
            $rclass = 'product';
            break;
        case 'topic':
        case 'reply':
            $excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'forum-post'));
            $rclass = ('reply' === $post_type ? 'forum-reply' : 'forum-post');
            break;
        case 'post':
        default:
            $excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'front-post') && false === $rwp->rw_validate_visibility($post->ID, 'blog-post'));
            $rclass = 'blog-post';
            break;
    }

    RWLogger::Log('ShowPostMetaBox', 'Post Type = ' . $post_type);

	add_action('admin_footer', array(&$rwp, "rw_attach_rating_js"), 5);
	
	$multirating_options = ratingwidget()->get_multirating_options_by_class($rclass);
	$multi_criteria = count($multirating_options->criteria) > 1;
	
	$options = ratingwidget()->get_options_by_class($rclass);

    $default_hide_recommendations = isset($options->hideRecommendations) ? $options->hideRecommendations : false;
?>
<p>
	<input type="hidden" name="rw_post_meta_box_nonce" value="<?php echo wp_create_nonce(basename(WP_RW__PLUGIN_FILE_FULL)) ?>" />
	<table class="rw-rating-table rw-<?php echo $options->advanced->layout->dir;?>">
		<?php
		$urid_summary = $rwp->get_rating_id_by_element($post->ID, $rclass, false);
		
		$criteria_id = 1;
		foreach ($multirating_options->criteria as $criteria_key => $criteria) {
			$criteria_rclass = $rclass;
			if ($multi_criteria) {
				$criteria_rclass .= '-criteria-' . $criteria_id;
			}
			
			$urid = $rwp->get_rating_id_by_element($post->ID, $rclass, $multi_criteria ? $criteria_id++ : false);
			$rwp->QueueRatingData($urid, '', '', $criteria_rclass);
		?>
		<tr>
		<td>
			<div><nobr><?php echo (isset($criteria['label']) && !empty($criteria['label'])) ? $criteria['label'] : ''; ?></nobr></div>
			<div class="rw-ui-container rw-class-<?php echo $criteria_rclass ?>" <?php echo $multi_criteria ? "data-uarid=\"$urid_summary\"" : ''; ?> <?php echo ($multi_criteria || $default_hide_recommendations) ? ' data-hide-recommendations="true" ' : ''; ?> data-urid="<?php echo $urid; ?>" data-sync="false"></div>
			<p></p>
		</td>
		</tr>
		<?php
		}
		
		if ($multirating_options->show_summary_rating && $multi_criteria) {
			$rwp->QueueRatingData($urid_summary, '', '', $rclass);
			?>
			<tr>
			<td>
				<div><nobr><?php echo (isset($multirating_options->summary_label) && !empty($multirating_options->summary_label)) ? $multirating_options->summary_label : ''; ?></nobr></div>
				<div class="rw-ui-container rw-class-<?php echo $rclass ?>" <?php echo $default_hide_recommendations ? ' data-hide-recommendations="true" ' : ''; ?> data-urid="<?php echo $urid_summary; ?>" data-read-only="true" data-force-sync="true"></div>
				<p></p>
			</td>
			</tr>
			<?php
		}
		?>
	</table>
	<label for="rw_include_post"><input type="checkbox" name="rw_include_post" id="rw_include_post" value="1"<?php checked(false, $excluded_post); ?> /><?php _e('Show Rating (Uncheck to Hide)', WP_RW__ID) ?></label>
        <br>
	<label for="rw_readonly_post"><input type="checkbox" name="rw_readonly_post" id="rw_readonly_post" value="1"<?php checked(false, $readonly_post); ?> /><?php _e('Active (Uncheck to ReadOnly)', WP_RW__ID) ?></label>
</p>