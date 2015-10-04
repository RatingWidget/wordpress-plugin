<div id="rw_account_actions" class="postbox rw-body">
	<h3><?php _erw( 'account-actions' ) ?></h3>

	<div class="inside">
		<table cellspacing="0" class="fs-key-value-table">
			<tr class="rw-odd">
				<td>
					<form class="rw-button-form" action="" method="POST"
					      onsubmit="return confirm('<?php _erw( 'default-settings-confirm' ) ?>');">
						<input type="hidden" name="rw_action" value="default_settings">
						<?php wp_nonce_field( 'default_settings' ) ?>
						<input type="submit" class="button" value="<?php _erw( 'default-settings' ) ?>">
					</form>
				</td>
				<td><span><?php _erw( 'default-settings_desc' ) ?></span></td>
			</tr>
			<tr class="rw-even">
				<td>
					<form class="rw-button-form" action="" method="POST">
						<input type="hidden" name="rw_action" value="clear_cache">
						<?php wp_nonce_field( 'clear_cache' ) ?>
						<input type="submit" class="button button-secondary" value="<?php _erw( 'clear-cache' ) ?>">
					</form>
				</td>
				<td><span><?php _erw( 'clear-cache_desc' ) ?></span></td>
			</tr>
			<tr class="rw-odd">
				<td>
					<form class="rw-button-form" action="" method="POST"
					      onsubmit="return confirm('<?php _erw( 'clear-ratings_confirm' ) ?>');">
						<input type="hidden" name="rw_action" value="clear_ratings">
						<?php wp_nonce_field( 'clear_ratings' ) ?>
						<input type="submit" class="button button-secondary rw-delete-button"
						       value="<?php _erw( 'clear-ratings' ) ?>">
					</form>
				</td>
				<td><span><?php _erw( 'clear-ratings_desc' ) ?></span></td>
			</tr>
			<tr class="rw-even">
				<td>
					<form class="rw-button-form" action="" method="POST"
					      onsubmit="return confirm('<?php _erw( 'start-fresh_confirm' ) ?>');">
						<input type="hidden" name="rw_action" value="go_factory">
						<?php wp_nonce_field( 'go_factory' ) ?>
						<input type="submit" class="button button-secondary rw-delete-button"
						       value="<?php _erw( 'start-fresh' ) ?>">
					</form>
				</td>
				<td><span><?php _erw( 'start-fresh_desc' ) ?></span></td>
			</tr>
			<?php if ( WP_RW__DEBUG ) : ?>
				<!--<tr class="rw-odd">
					<td>
						<form class="rw-button-form" action="" method="POST" onsubmit="return confirm('<?php _e( 'Are you sure you want to delete the account?', WP_RW__ID ) ?>');">
							<input type="hidden" name="rw_action" value="delete_account">
							<?php wp_nonce_field( 'delete_account' ) ?>
							<input type="submit" class="button button-secondary rw-delete-button" value="<?php _e( 'Delete Account', WP_RW__ID ) ?>">
						</form>
					</td>
					<td><span><?php _e( 'Delete the account.', WP_RW__ID ) ?></span></td>
				</tr>-->
			<?php endif ?>
		</table>
	</div>
</div>

<?php $rw_account = rw_account() ?>
<div id="rw_account" class="postbox rw-body">
	<h3><?php _erw( 'ratingwidget-account' ) ?></h3>

	<div class="inside">
		<table id="rw_account_details" cellspacing="0" class="fs-key-value-table">
			<?php
				$profile = array();
				//		if (isset($user->email) && false !== strpos($user->email, '@'))
				//			$profile[] = array('id' => 'email', 'title' => __rw('email'), 'value' => $user->email);
				if ( $rw_account->has_owner() ) {
					$profile[] = array( 'id'    => 'user_id',
					                    'title' => __rw( 'user-id' ),
					                    'value' => $rw_account->user_id
					);
				}

				$profile[] = array( 'id'    => 'site_id',
				                    'title' => __rw( 'site-id' ),
				                    'value' => $rw_account->has_site_id() ? $rw_account->site_id : __rw( 'no-id' )
				);

				$profile[] = array( 'id'    => 'site_public_key',
				                    'title' => __rw( 'public-key' ),
				                    'value' => $rw_account->site_public_key
				);

				$profile[] = array( 'id'    => 'site_secret_key',
				                    'title' => __rw( 'secret-key' ),
				                    'value' => ( $rw_account->has_secret_key() ? $rw_account->site_secret_key : __rw( 'no-secret' ) )
				);
			?>
			<?php $odd = true;
				foreach ( $profile as $p ) : ?>
					<tr class="fs-field-<?php echo $p['id'] ?><?php if ( $odd ) : ?> alternate<?php endif ?>">
						<td>
							<nobr><?php echo $p['title'] ?>:</nobr>
						</td>
						<td>
							<code><?php echo htmlspecialchars( $p['value'] ) ?></code>
						</td>
						<?php if ( WP_RW__DEBUG ) : ?>
							<td class="fs-right">
								<form action="<?php echo rw_fs()->_get_admin_page_url( 'account' ) ?>" method="POST"
								      onsubmit="var val = prompt('<?php printf( __rw( 'what-is-your' ), $p['title'] ) ?>', '<?php echo $p['value'] ?>'); if (null == val || '' === val) return false; jQuery('input[name=rw_<?php echo $p['id'] ?>]').val(val); return true;">
									<input type="hidden" name="rw_action" value="update_<?php echo $p['id'] ?>">
									<input type="hidden" name="rw_<?php echo $p['id'] ?>" value="">
									<?php wp_nonce_field( 'update_' . $p['id'] ) ?>
									<input type="submit" class="button button-small" value="<?php _erw( 'edit' ) ?>">
								</form>
							</td>
						<?php endif ?>
					</tr>
					<?php $odd = ! $odd; endforeach ?>
		</table>
	</div>
</div>
