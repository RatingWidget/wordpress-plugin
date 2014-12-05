<div id="rw_account_actions" class="postbox rw-body">
	<h3><?php _e('Account Actions', WP_RW__ID) ?></h3>
	<div class="inside">
		<table cellspacing="0">
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
				<tr class="rw-odd">
					<td>
						<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e('Are you sure you want to delete the account?', WP_RW__ID) ?>');">
							<input type="hidden" name="rw_action" value="delete_account">
							<?php wp_nonce_field('delete_account') ?>
							<input type="submit" class="button button-secondary rw-delete-button" value="<?php _e('Delete Account', WP_RW__ID) ?>">
						</form>
					</td>
					<td><span><?php _e('Delete the account.', WP_RW__ID) ?></span></td>
				</tr>
			<?php endif ?>
		</table>
	</div>
</div>
