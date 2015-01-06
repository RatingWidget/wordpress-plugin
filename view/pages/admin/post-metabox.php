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
		case 'post':
		default:
			$excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'front-post') && false === $rwp->rw_validate_visibility($post->ID, 'blog-post'));
			$rclass = 'blog-post';
			break;
	}

	RWLogger::Log('ShowPostMetaBox', 'Post Type = ' . $post_type);

	add_action('admin_footer', array(&$rwp, "rw_attach_rating_js"), 5);
	
	if ($rclass == 'blog-post') {
		$options = $rwp->GetOption(WP_RW__BLOG_POSTS_OPTIONS);
	} else if ($rclass == 'page') {
		$options = $rwp->GetOption(WP_RW__PAGES_OPTIONS);
	} else if ($rclass == 'product') {
		$options = $rwp->GetOption(WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS);
	}
	
	$multirating_settings_list = $rwp->GetOption(WP_RW__MULTIRATING_SETTINGS);
	$multirating_options = $multirating_settings_list->{$rclass};
?>
<p>
	<input type="hidden" name="rw_post_meta_box_nonce" value="<?php echo wp_create_nonce(basename(WP_RW__PLUGIN_FILE_FULL)) ?>" />
	<table>
		<?php
		$urid_summary = $rwp->_getPostRatingGuid($post->ID);
		
		foreach ($multirating_options->criteria as $criteria_id => $criteria) {
			$urid = $rwp->_getPostRatingGuid($post->ID, $criteria_id);
			$rwp->QueueRatingData($urid, '', '', $rclass);
		?>
		<tr>
		<td>
			<div><?php echo $criteria['label']; ?></div>
			<div class="rw-ui-container rw-class-<?php echo $rclass ?>" data-uarid="<?php echo $urid_summary; ?>" data-urid="<?php echo $urid; ?>" data-sync="false"></div>
			<p></p>
		</td>
		</tr>
		<?php
		}
		
		if ($multirating_options->show_summary_rating && count($multirating_options->criteria) > 1) {
			$rwp->QueueRatingData($urid_summary, '', '', $rclass);
			?>
			<tr>
			<td>
				<div><?php echo $multirating_options->summary_label; ?></div>
				<div class="rw-ui-container rw-class-<?php echo $rclass ?>" data-urid="<?php echo $urid_summary; ?>" data-read-only="true" data-sync="false"></div>
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