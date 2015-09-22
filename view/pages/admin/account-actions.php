<div id="rw_account_actions" class="postbox rw-body">
	<h3><?php _e('Account Actions', WP_RW__ID) ?></h3>
	<div class="inside">
		<table cellspacing="0" class="fs-key-value-table">
			<tr class="rw-odd">
				<td>
					<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e('Are you sure you want to restore to default settings?', WP_RW__ID) ?>');">
						<input type="hidden" name="rw_action" value="default_settings">
						<?php wp_nonce_field('default_settings') ?>
						<input type="submit" class="button" value="<?php _e('Default Settings', WP_RW__ID) ?>">
					</form>
				</td>
				<td><span><?php _e('Restore the default factory settings.', WP_RW__ID) ?></span></td>
			</tr>
			<tr class="rw-even">
				<td>
					<form class="rw-button-form" action="" method="POST">
						<input type="hidden" name="rw_action" value="clear_cache">
						<?php wp_nonce_field('clear_cache') ?>
						<input type="submit" class="button button-secondary" value="<?php _e('Clear Cache', WP_RW__ID) ?>">
					</form>
				</td>
				<td><span><?php _e('Clear plugin\'s cache, including the Top-Rated Widget cache.', WP_RW__ID) ?></span></td>
			</tr>
			<tr class="rw-odd">
				<td>
					<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e('Are you sure you want to delete all the ratings and votes?', WP_RW__ID) ?>');">
						<input type="hidden" name="rw_action" value="clear_ratings">
						<?php wp_nonce_field('clear_ratings') ?>
						<input type="submit" class="button button-secondary rw-delete-button" value="<?php _e('Clear Ratings', WP_RW__ID) ?>">
					</form>
				</td>
				<td><span><?php _e('Delete all ratings and votes.', WP_RW__ID) ?></span></td>
			</tr>
			<tr class="rw-even">
				<td>
					<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e('Are you sure you want to delete all the ratings and votes, and restore to default factory settings?', WP_RW__ID) ?>');">
						<input type="hidden" name="rw_action" value="go_factory">
						<?php wp_nonce_field('go_factory') ?>
						<input type="submit" class="button button-secondary rw-delete-button" value="<?php _e('Start Fresh', WP_RW__ID) ?>">
					</form>
				</td>
				<td><span><?php _e('Start fresh as if you just installed the plugin. Delete all your ratings and votes, and restore the default factory settings.', WP_RW__ID) ?></span></td>
			</tr>
			<?php if (WP_RW__DEBUG) : ?>
				<!--<tr class="rw-odd">
					<td>
						<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e('Are you sure you want to delete the account?', WP_RW__ID) ?>');">
							<input type="hidden" name="rw_action" value="delete_account">
							<?php wp_nonce_field('delete_account') ?>
							<input type="submit" class="button button-secondary rw-delete-button" value="<?php _e('Delete Account', WP_RW__ID) ?>">
						</form>
					</td>
					<td><span><?php _e('Delete the account.', WP_RW__ID) ?></span></td>
				</tr>-->
			<?php endif ?>
		</table>
	</div>
</div>

<?php $rw_account = rw_account() ?>
<div id="rw_account" class="postbox rw-body">
	<h3><?php _e('RatingWidget Account', WP_RW__ID) ?></h3>
	<div class="inside">
		<table id="rw_account_details" cellspacing="0" class="fs-key-value-table">
			<?php
				$profile = array();
		//		if (isset($user->email) && false !== strpos($user->email, '@'))
		//			$profile[] = array('id' => 'email', 'title' => __('Email', WP_RW__ID), 'value' => $user->email);
				if ($rw_account->has_owner())
					$profile[] = array('id' => 'user_id', 'title' => __('User ID', WP_RW__ID), 'value' => $rw_account->user_id);

				$profile[] = array('id' => 'site_id', 'title' => __('Site ID', WP_RW__ID), 'value' => $rw_account->has_site_id() ? $rw_account->site_id : 'No ID');

				$profile[] = array('id' => 'site_public_key', 'title' => __('Public Key', WP_RW__ID), 'value' => $rw_account->site_public_key);

				$profile[] = array('id' => 'site_secret_key', 'title' => __('Secret Key', WP_RW__ID), 'value' => ($rw_account->has_secret_key() ? $rw_account->site_secret_key : __('No Secret', WP_RW__ID)));

		//		$profile[] = array('id' => 'plan', 'title' => __('Plan', WP_RW__ID), 'value' => is_string($site->plan->name) ? strtoupper($site->plan->title) : 'FREE');
		//
			?>
			<?php $odd = true; foreach ($profile as $p) : ?>
				<tr class="fs-field-<?php echo $p['id'] ?><?php if ($odd) :?> alternate<?php endif ?>">
					<td>
						<nobr><?php echo $p['title'] ?>:</nobr>
					</td>
					<td>
						<code><?php echo htmlspecialchars($p['value']) ?></code>
					</td>
					<?php if (WP_RW__DEBUG) : ?>
					<td class="fs-right">
							<form action="<?php echo rw_fs()->_get_admin_page_url('account') ?>" method="POST" onsubmit="var val = prompt('<?php echo __('What is your', WP_RW__ID) . ' ' . $p['title'] . '?' ?>', '<?php echo $p['value'] ?>'); if (null == val || '' === val) return false; jQuery('input[name=rw_<?php echo $p['id'] ?>]').val(val); return true;">
								<input type="hidden" name="rw_action" value="update_<?php echo $p['id'] ?>">
								<input type="hidden" name="rw_<?php echo $p['id'] ?>" value="">
								<?php wp_nonce_field('update_' . $p['id']) ?>
								<input type="submit" class="button button-small" value="<?php _e('Edit', WP_RW__ID) ?>">
							</form>
					</td>
					<?php endif ?>
				</tr>
				<?php $odd = !$odd; endforeach ?>
		</table>
	</div>
</div>
