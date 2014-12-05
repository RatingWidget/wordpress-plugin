<?php
	$slug = $VARS['slug'];

	$fs = fs($slug);
?>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="<?php $fs->get_account_url() ?>" class="nav-tab nav-tab-active"><?php _e('Account', WP_FS__SLUG) ?></a>
		<?php if (!$fs->is_paying()) : ?>
			<a href="<?php echo $fs->get_upgrade_url() ?>" class="nav-tab" target="_blank"><?php _e('Upgrade', WP_FS__SLUG) ?></a>
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

									if (is_numeric($site->id))
										$profile[] = array('id' => 'site_id', 'title' => __('Site ID', WP_FS__SLUG), 'value' => $site->id);

									$profile[] = array('id' => 'public', 'title' => __('Public Key', WP_FS__SLUG), 'value' => $site->public_key);

									$profile[] = array('id' => 'secret', 'title' => __('Secret Key', WP_FS__SLUG), 'value' => ((is_string($site->secret_key)) ? $site->secret_key : __('No Secret', WP_FS__SLUG)));

									$profile[] = array('id' => 'plan', 'title' => __('Plan', WP_FS__SLUG), 'value' => is_string(WP_RW__SITE_PLAN) ? strtoupper(WP_RW__SITE_PLAN) : 'FREE');
								?>
								<?php $odd = true; foreach ($profile as $p) : ?>
									<tr class="fs-<?php echo $odd ? 'odd' : 'even' ?>">
										<td>
											<?php echo $p['title'] ?>:
										</td>
										<td><code><?php echo htmlspecialchars($p['value']) ?></code></td>
										<td class="fs-right">
											<?php if ('plan' === $p['id']) : ?>
												<form action="" method="POST" class="button-group">
													<input type="hidden" name="rw_action" value="sync_license">
													<?php wp_nonce_field('sync_license') ?>
													<input type="submit" class="button" value="<?php _e('Sync License', WP_FS__SLUG) ?>">

													<?php if ( !$fs->is_paying() ) : ?>
														<a href="<?php echo $fs->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button button-primary gradient button-upgrade" target="_blank"><?php _e('Upgrade', WP_FS__SLUG) ?></a>
													<?php else : ?>
														<a href="<?php echo $fs->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'change-plan', 'wordpress', 'account', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button gradient button-secondary button-upgrade" target="_blank"><?php _e('Change Plan', WP_FS__SLUG) ?></a>
													<?php endif; ?>
												</form>
											<?php elseif ('secret' === $p['id']) : ?>
												<form action="" method="POST" onsubmit="var secret = prompt('<?php _e('What is your secret key?', WP_FS__SLUG) ?>', '<?php echo ((is_string($site->secret_key)) ? $site->secret_key : '') ?>'); if (null == secret || '' === secret) return false; jQuery('input[name=fs_site_secret_<?php echo $slug ?>]').val(secret); return true;">
													<input type="hidden" name="fs_action" value="update_secret">
													<input type="hidden" name="fs_site_secret_<?php echo $slug ?>" value="">
													<?php wp_nonce_field('update_secret') ?>
													<input type="submit" class="button" value="<?php _e('Update Secret', WP_FS__SLUG) ?>">
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