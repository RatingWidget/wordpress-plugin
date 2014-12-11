<?php
	global $post, $rwp;

	$post_type = get_post_type($post);

	switch ($post_type)
	{
		case 'page':
			$excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'page'));
			$readonly_post = (true === $rwp->is_rating_readonly($post->ID, 'page'));
			$rclass = 'page';
			break;
		case 'product':
			$excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'collection-product') && false === $rwp->rw_validate_visibility($post->ID, 'product'));
			$readonly_post = (true === $rwp->is_rating_readonly($post->ID, 'product'));
			$rclass = 'product';
			break;
		case 'post':
		default:
			$excluded_post = (false === $rwp->rw_validate_visibility($post->ID, 'front-post') && false === $rwp->rw_validate_visibility($post->ID, 'blog-post'));
			$readonly_post = (true === $rwp->is_rating_readonly($post->ID, 'post'));
			$rclass = 'blog-post';
			break;
	}

	$checked = $excluded_post ? '' : ' checked="checked"';

	$urid = $rwp->_getPostRatingGuid($post->ID);

	$rwp->QueueRatingData($urid, '', '', $rclass);

	RWLogger::Log('ShowPostMetaBox', 'Post Type = ' . $post_type);

	add_action('admin_footer', array(&$rwp, "rw_attach_rating_js"), 5);
?>
<p>
	<input type="hidden" name="rw_post_meta_box_nonce" value="<?php echo wp_create_nonce(basename(WP_RW__PLUGIN_FILE_FULL)) ?>" />
	<div class="rw-ui-container rw-class-<?php echo $rclass ?>" data-urid="<?php echo $urid ?>" data-read-only="false" data-sync="false"></div>
	<br><br>
	<label for="rw_include_post"><input type="checkbox" name="rw_include_post" id="rw_include_post" value="1"<?php echo $checked ?> /><?php _e('Show Rating (Uncheck to Hide)', WP_RW__ID) ?></label>
        <br>
	<label for="rw_readonly_post"><input type="checkbox" name="rw_readonly_post" id="rw_readonly_post" value="1"<?php checked(true, $readonly_post); ?> /><?php _e('Read-Only Rating (Uncheck to Make Active)', WP_RW__ID) ?></label>
</p>