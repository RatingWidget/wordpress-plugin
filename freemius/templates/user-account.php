<?php
	$slug = $VARS['slug'];
	$fs = fs($slug);
?>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo $fs->get_account_url() ?>" class="nav-tab nav-tab-active"><?php _e('Account', WP_FS__SLUG) ?></a>
		<a href="<?php echo fs_get_admin_plugin_url('addons') ?>" class="nav-tab"><?php _e('Add Ons', WP_FS__SLUG) ?></a>
		<?php if (!$fs->is_paying()) : ?>
			<a href="<?php echo $fs->get_upgrade_url() ?>" class="nav-tab"><?php _e('Upgrade', WP_FS__SLUG) ?></a>
		<?php endif ?>
	</h2>
	<div id="poststuff">
		<div id="fs_account_settings">
			<div class="has-sidebar has-right-sidebar">
				<div class="has-sidebar-content">
					<div class="postbox">
						<h3><?php _e('Account Details', WP_FS__SLUG) ?></h3>
						<div class="inside">
							<table cellspacing="0">
								<?php
									$profile = array();
									$user = $fs->get_user();
									$site = $fs->get_site();
									if (is_numeric($user->id))
										$profile[] = array('id' => 'user_id', 'title' => __('User ID', WP_FS__SLUG), 'value' => $user->id);
									if (isset($user->email) && false !== strpos($user->email, '@'))
										$profile[] = array('id' => 'email', 'title' => __('User Email', WP_FS__SLUG), 'value' => $user->email);

									$profile[] = array('id' => 'site_id', 'title' => __('Site ID', WP_FS__SLUG), 'value' => is_string($site->id) ? $site->id : 'No ID');

									$profile[] = array('id' => 'site_public_key', 'title' => __('Public Key', WP_FS__SLUG), 'value' => $site->public_key);

									$profile[] = array('id' => 'site_secret_key', 'title' => __('Secret Key', WP_FS__SLUG), 'value' => ((is_string($site->secret_key)) ? $site->secret_key : __('No Secret', WP_FS__SLUG)));

									$profile[] = array('id' => 'plan', 'title' => __('Plan', WP_FS__SLUG), 'value' => is_string(WP_RW__SITE_PLAN) ? strtoupper(WP_RW__SITE_PLAN) : 'FREE');
								?>
								<?php $odd = true; foreach ($profile as $p) : ?>
									<tr class="fs-<?php echo $odd ? 'odd' : 'even' ?>">
										<td>
											<nobr><?php echo $p['title'] ?>:</nobr>
										</td>
										<td><code><?php echo htmlspecialchars($p['value']) ?></code></td>
										<td class="fs-right">
											<?php if ('plan' === $p['id']) : ?>
												<form action="" method="POST" class="button-group">
													<input type="hidden" name="rw_action" value="sync_license">
													<?php wp_nonce_field('sync_license') ?>
													<input type="submit" class="button" value="<?php _e('Sync License', WP_FS__SLUG) ?>">

													<?php if ( !$fs->is_paying() ) : ?>
														<a href="<?php echo $fs->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button button-primary gradient button-upgrade"><?php _e('Upgrade', WP_FS__SLUG) ?></a>
													<?php else : ?>
														<a href="<?php echo $fs->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'change-plan', 'wordpress', 'account', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button gradient button-secondary button-upgrade"><?php _e('Change Plan', WP_FS__SLUG) ?></a>
													<?php endif; ?>
												</form>
											<?php elseif (in_array($p['id'], array('site_secret_key', 'site_id', 'site_public_key')) ) : ?>
												<form action="" method="POST" onsubmit="var val = prompt('<?php echo __('What is your', WP_FS__SLUG) . ' ' . $p['title'] . '?' ?>', '<?php echo $p['value'] ?>'); if (null == val || '' === val) return false; jQuery('input[name=fs_<?php echo $p['id'] ?>_<?php echo $slug ?>]').val(val); return true;">
													<input type="hidden" name="fs_action" value="update_<?php echo $p['id'] ?>">
													<input type="hidden" name="fs_<?php echo $p['id'] ?>_<?php echo $slug ?>" value="">
													<?php wp_nonce_field('update_' . $p['id']) ?>
													<input type="submit" class="button button-small" value="<?php _e('Edit', WP_FS__SLUG) ?>">
												</form>
											<?php endif ?>
										</td>
									</tr>
									<?php $odd = !$odd; endforeach ?>
							</table>
						</div>
					</div>

					<?php $fs->do_action( 'fs_after_account_details' ) ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php fs_require_template('powered-by.php') ?>