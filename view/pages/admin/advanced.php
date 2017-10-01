<?php
	$settings = rw_settings();
?>
	<div class="wrap rw-dir-ltr">
		<form id="rw_advanced_settings_form" method="post" action="">
			<input type="hidden" name="rw_advanced_settings_nonce"
			       value="<?php echo wp_create_nonce( basename( WP_RW__PLUGIN_FILE_FULL ) ) ?>"/>
			<div id="poststuff">
				<div id="rw_wp_set">
					<div id="rw_identify_by" class="has-sidebar has-right-sidebar">
						<div class="has-sidebar-content">
							<div class="postbox rw-body">
								<h3><?php _erw( 'visitor-identification_title' ) ?></h3>

								<div class="inside rw-ui-content-container rw-no-radius"
								     style="padding: 5px; width: 610px;">
									<div
										class="rw-ui-img-radio rw-ui-hor<?php if ( 'laccount' === $settings->identify_by ) {
											echo ' rw-selected';
										} ?>">
										<input type="radio" name="rw_identify_by"
										       value="laccount" <?php if ( 'laccount' === $settings->identify_by ) {
											echo ' checked="checked"';
										} ?>>
										<span><?php _erw( 'visitor-identification_by-cookie' ) ?></span>
									</div>
									<div class="rw-ui-img-radio rw-ui-hor<?php if ( 'ip' === $settings->identify_by ) {
										echo ' rw-selected';
									} ?>"<?php if ( ! rw_fs()->is_plan_or_trial( 'professional' ) ) : ?> data-alert="<?php _erw( 'visitor-identification_by-cookie_pro-only', WP_RW__ID ) ?>"<?php endif ?>>
										<input type="radio" name="rw_identify_by"
										       value="ip" <?php if ( 'ip' === $settings->identify_by ) {
											echo ' checked="checked"';
										} ?>>
										<span><?php _erw('visitor-identification_by-ip') ?> <b><?php _erw('visitor-identification_by-ip_for-contests') ?></b> <?php _erw('visitor-identification_by-ip_pro-only' ) ?>.</span>
									</div>
									<div
										class="rw-ui-img-radio rw-ui-hor<?php if ( 'account' === $settings->identify_by ) {
											echo ' rw-selected';
										} ?>"
										data-alert="<?php _erw( 'visitor-identification_by-social' ) ?>">
										<input type="radio" name="rw_identify_by"
										       value="account" <?php if ( 'account' === $settings->identify_by ) {
											echo ' checked="checked"';
										} ?>>
										<span><?php _erw( 'visitor-identification_by-social_desc' ) ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="rw_flash_settings" class="has-sidebar has-right-sidebar">
						<div class="has-sidebar-content">
							<div class="postbox rw-body">
								<h3><?php _erw( 'flash-dependency' ) ?></h3>

								<div class="inside rw-ui-content-container rw-no-radius"
								     style="padding: 5px; width: 610px;">
									<div class="rw-ui-img-radio rw-ui-hor<?php if ( $settings->flash_dependency ) {
										echo ' rw-selected';
									} ?>">
										<i class="rw-ui-sprite rw-ui-flash"></i> <input type="radio"
										                                                name="rw_flash_dependency"
										                                                value="true" <?php if ( $settings->flash_dependency ) {
											echo ' checked="checked"';
										} ?>>
										<span><?php _erw( 'flash-dependency_enable-desc' ) ?></span>
									</div>
									<div class="rw-ui-img-radio rw-ui-hor<?php if ( ! $settings->flash_dependency ) {
										echo ' rw-selected';
									} ?>">
										<i class="rw-ui-sprite rw-ui-flash-disabled"></i> <input type="radio"
										                                                         name="rw_flash_dependency"
										                                                         value="false" <?php if ( ! $settings->flash_dependency ) {
											echo ' checked="checked"';
										} ?>>
										<span><?php _erw( 'flash-dependency_disable-desc' ) ?></span>
									</div>
									<span
										style="font-size: 10px; background: white; padding: 2px; border: 1px solid gray; display: block; margin-top: 5px; font-weight: bold; background: rgb(240,240,240); color: black;">Flash dependency <b
											style="text-decoration: underline;">doesn't</b> mean that if a user doesn't have a flash player installed on his browser then it will get stuck. The reason to disable flash is for users which have flash blocking add-ons (e.g. FF Flashblock add-on), which is quite rare.</span>
								</div>
							</div>
						</div>
					</div>
					<div id="rw_mobile_settings" class="has-sidebar has-right-sidebar">
						<div class="has-sidebar-content">
							<div class="postbox rw-body">
								<h3><?php _erw( 'mobile-settings' ) ?></h3>

								<div class="inside rw-ui-content-container rw-no-radius"
								     style="padding: 5px; width: 610px;">
									<div class="rw-ui-img-radio rw-ui-hor<?php if ( $settings->show_on_mobile ) {
										echo ' rw-selected';
									} ?>">
										<i class="rw-ui-sprite rw-ui-mobile"></i> <input type="radio"
										                                                 name="rw_show_on_mobile"
										                                                 value="true" <?php if ( $settings->show_on_mobile ) {
											echo ' checked="checked"';
										} ?>> <span>Show ratings on Mobile devices.</span>
									</div>
									<div class="rw-ui-img-radio rw-ui-hor<?php if ( ! $settings->show_on_mobile ) {
										echo ' rw-selected';
									} ?>">
										<i class="rw-ui-sprite rw-ui-mobile-disabled"></i> <input type="radio"
										                                                          name="rw_show_on_mobile"
										                                                          value="false" <?php if ( ! $settings->show_on_mobile ) {
											echo ' checked="checked"';
										} ?>> <span>Hide ratings on Mobile devices.</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="rw_wp_set_widgets">
					<?php rw_require_once_view( 'save.php' ); ?>
				</div>
			</div>
		</form>
	</div>
<?php fs_require_template( 'powered-by.php' ) ?>