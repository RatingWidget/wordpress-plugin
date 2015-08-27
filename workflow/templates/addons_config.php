<?php
$slug = $VARS['slug'];
$wf = wf( $slug );

// Retrieve available add-on settings tabs and their form fields.
$addons_settings_tab = apply_filters( 'wf_addons_settings_tab', array() );

// Get the selected add-on tab
$selected_key = '';
if ( isset( $_GET['add-on'] ) && ! empty( $_GET['add-on'] ) ) {
	$selected_key = $_GET['add-on'];
} else {
	// If there is no selected add-on tab, select the first tab.
	$tab_keys = array_keys( $addons_settings_tab );
	$selected_key = $tab_keys[0];
}
?>
<div class="wrap rw-dir-ltr rw-wp-container">
	<h2 class="nav-tab-wrapper rw-nav-tab-wrapper">
		<?php foreach ( $addons_settings_tab as $tab_key => $tab_settings ) { ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'add-on' => $tab_key ) ) ); ?>" class="nav-tab<?php if ( $tab_key === $selected_key ) echo ' nav-tab-active'; ?>"><?php _e( $tab_settings['title'], WP_WF__SLUG );?></a>
		<?php } ?>
	</h2>
	
	<form method="post" action="">
		<div id="poststuff">
			<div id="rw_wp_set">
				<?php
				// Retrieve all add-ons' settings.
				$addons_settings = $wf->get_addons_settings();

				foreach ( $addons_settings_tab[ $selected_key ]['sections'] as $section ) {
				?>
				<div class="has-sidebar has-right-sidebar">
					<div class="has-sidebar-content">
						<div class="postbox rw-body">
							<h3><?php echo $section['title']; ?></h3>
							
							<div id="section-<?php echo $section['id']; ?>" class="inside rw-ui-content-container rw-no-radius">
								<table>
									<tbody>
										<?php
										$is_odd_row = false;
										
										// Add form fields.
										foreach ( $section['fields'] as $field_id => $field ) {
											$is_odd_row = ( ! $is_odd_row );
											
											$value = false;
											
											// Get the field's value from the options saved to the database.
											if ( ( false !== $addons_settings ) && isset( $addons_settings->{ $selected_key } ) ) {
												$selected_addon_settings = $addons_settings->{ $selected_key };
												if ( isset( $selected_addon_settings->{ $field_id } ) ) {
													$value = $selected_addon_settings->{ $field_id };
												}
											}
											
											// Retrieve the default value if there is no value saved to the database.
											if ( ( false === $value ) ) {
												$value = $field['default'];
											}
										?>
										<tr id="<?php echo $field_id; ?>" class="rw-<?php echo $is_odd_row ? 'odd' : 'even'; ?>">
											<td>
												<span class="rw-ui-def"><?php echo $field['title']; ?>:</span>
											</td>
											<td>
												<?php if ( 'textfield' === $field['type'] ) { ?>
													<input type="text" id="<?php echo $field_id; ?>" name="addon-fields[<?php echo $field_id; ?>]" value="<?php echo $value; ?>"/>
												<?php } else if ( 'textarea' === $field['type'] ) { ?>
													<textarea id="<?php echo $field_id; ?>" name="addon-fields[<?php echo $field_id; ?>]" rows="5"><?php echo $value; ?></textarea>
												<?php } ?>
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php
				}
				?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', WP_WF__SLUG ); ?>"></p>
			</div>
			<div id="rw_wp_set_widgets" class="rw-static">
				<div class="postbox">
					<h3 class="gradient"><?php _e( 'Template Variables', WP_WF__SLUG ); ?></h3>
					<div class="inside">
						<ul>
							<li>
								<b>{{vote}}</b>
								<p class="description"><?php _e( 'The vote given by the user. e.g.: 5-star', WP_WF__SLUG ); ?></p>
							</li>
							<li>
								<b>{{avg_rate}}</b>
								<p class="description"><?php _e( 'The average rating value. e.g.: 4.5', WP_WF__SLUG ); ?></p>
							</li>
							<li>
								<b>{{post.title}}</b>
								<p class="description" id="tagline-description"><?php _e( 'The title of the current post.', WP_WF__SLUG ); ?></p>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="add-on" value="<?php echo $selected_key; ?>" />
		<input type="hidden" name="rw-save-addons-settings" />
	</form>
</div>