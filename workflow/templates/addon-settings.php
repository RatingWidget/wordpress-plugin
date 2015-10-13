<?php
	/**
	 * Template file called from RW_Workflows->_addons_config_page_render method.
	 *
	 * @package     RatingWidget
	 * @copyright   Copyright (c) 2015, Rating-Widget, Inc.
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       1.0.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	// Retrieve available add-on settings tabs and their form fields.
	$addons_settings_tab = apply_filters( 'rw_wf_addons_settings_tab', array() );

	// Get the selected add-on tab
	$selected_key = '';
	if ( isset( $_GET['add-on'] ) && ! empty( $_GET['add-on'] ) ) {
		$selected_key = $_GET['add-on'];
	} else {
		// If there is no selected add-on tab, select the first tab.
		$tab_keys     = array_keys( $addons_settings_tab );
		$selected_key = $tab_keys[0];
	}

	// Retrieve all add-ons' settings.
	$addons_settings = rw_wf()->get_addons_settings();

	if ( ( false !== $addons_settings ) && isset( $addons_settings->{$selected_key} ) ) {
		$selected_addon_settings = $addons_settings->{$selected_key};
	} else {
		$selected_addon_settings = new stdClass();
	}

?>
<div class="wrap rw-dir-ltr rw-wp-container">
	<h2 class="nav-tab-wrapper rw-nav-tab-wrapper">
		<?php foreach ( $addons_settings_tab as $tab_key => $tab_settings ) { ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'add-on' => $tab_key ) ) ); ?>"
			   class="nav-tab<?php if ( $tab_key === $selected_key ) {
				   echo ' nav-tab-active';
			   } ?>"><?php echo $tab_settings['title'] ?></a>
		<?php } ?>
	</h2>

	<form method="post" action="">
		<div id="poststuff">
			<div id="rw_wp_set">
				<?php
					foreach ( $addons_settings_tab[ $selected_key ]['sections'] as $section ) {
						?>
						<div class="has-sidebar has-right-sidebar">
							<div class="has-sidebar-content">
								<div class="postbox rw-body">
									<h3><?php echo $section['title']; ?></h3>

									<div id="section-<?php echo $section['id']; ?>"
									     class="inside rw-ui-content-container rw-no-radius">
										<table>
											<tbody>
											<?php
												$is_odd_row = false;

												// Add form fields.
												foreach ( $section['fields'] as $field_id => $field ) {
													$is_odd_row = ( ! $is_odd_row );

													$value = false;

													// Get the field's value from the options saved to the database.
													if ( isset( $selected_addon_settings->{$field_id} ) ) {
														$value = $selected_addon_settings->{$field_id};
													}

													// Retrieve the default value if there is no value saved to the database.
													if ( ( false === $value ) ) {
														$value = $field['default'];
													}
													?>
													<tr id="<?php echo $field_id; ?>"
													    class="rw-<?php echo $is_odd_row ? 'odd' : 'even'; ?>">
														<td>
															<span class="rw-ui-def"><?php echo $field['title']; ?>
																:</span>
														</td>
														<td>
															<?php if ( 'textfield' === $field['type'] ) { ?>
																<input type="text" id="<?php echo $field_id; ?>"
																       name="addon-fields[<?php echo $field_id; ?>]"
																       value="<?php echo $value; ?>"/>
															<?php } else if ( 'textarea' === $field['type'] ) { ?>
																<textarea id="<?php echo $field_id; ?>"
																          name="addon-fields[<?php echo $field_id; ?>]"
																          rows="5"><?php echo $value; ?></textarea>
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
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
				                         value="<?php _erw( 'save-changes' ) ?>"></p>
			</div>
			<div id="rw_wp_set_widgets" class="rw-static">
				<div class="postbox">
					<h3 class="gradient"><?php _erw( 'template-variables' ) ?></h3>

					<div class="inside">
						<ul>
							<li>
								<b>{{vote}}</b>

								<p class="description"><?php _erw( 'template_vote' ) ?></p>
							</li>
							<li>
								<b>{{avg_rate}}</b>

								<p class="description"><?php _erw( 'template_avg-rate' ) ?></p>
							</li>
							<li>
								<b>{{post.title}}</b>

								<p class="description" id="tagline-description"><?php _erw( 'template_title' ) ?></p>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="add-on" value="<?php echo $selected_key; ?>"/>
		<input type="hidden" name="rw-save-addons-settings"/>
	</form>
</div>