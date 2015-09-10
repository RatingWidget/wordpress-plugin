<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.3
	 */
?>
	<div data-id="<?php echo $VARS['id'] ?>" data-slug="<?php echo $VARS['slug'] ?>" class="<?php
	switch ($VARS['type']) {
		case 'error':
			echo 'error form-invalid';
			break;
		case 'promotion':
			echo 'updated promotion';
			break;
		case 'update':
//			echo 'update-nag update';
//			break;
		case 'success':
		default:
			echo 'updated success';
			break;
	}
?> fs-notice<?php if ($VARS['sticky']) echo ' fs-sticky' ?><?php if (!empty($VARS['title'])) echo ' fs-has-title' ?>"><?php if (!empty($VARS['title'])) : ?><label class="fs-plugin-title"><?php echo $VARS['title'] ?></label><?php endif ?><p>
		<?php echo $VARS['message'] ?>
	</p><?php if ($VARS['sticky']) : ?><i class="fs-close dashicons dashicons-no" title="<?php _e('Dismiss', WP_FS__SLUG) ?>"></i><?php endif ?></div>
