<?php
$slug = $VARS['slug'];
$wf = wf( $slug );

$settings_tab = apply_filters( 'wf_addons_settings_tab', array() );
$addons_settings = $wf->get_addons_settings();

$selected_key = '';

if ( isset( $_GET['add-on'] ) && ! empty( $_GET['add-on'] ) ) {
	$selected_key = $_GET['add-on'];
} else {
	$tab_keys = array_keys( $settings_tab );
	$selected_key = $tab_keys[0];
}
?>
<div class="wrap rw-dir-ltr rw-wp-container">
	<h2 class="nav-tab-wrapper rw-nav-tab-wrapper">
		<?php foreach ( $settings_tab as $settings_key => $settings ) { ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'add-on' => $settings_key, 'message' => false ) ) );?>" class="nav-tab<?php if ( $settings_key === $selected_key ) echo ' nav-tab-active'; ?>"><?php _e( $settings['title'], $slug );?></a>
		<?php } ?>
	</h2>
	
	<form method="post" action="">
		<div id="poststuff">
			<div id="rw_wp_set">
				<?php
				foreach ( $settings_tab[ $selected_key ]['sections'] as $section ) {
				?>
				<div class="has-sidebar has-right-sidebar">
					<div class="has-sidebar-content">
						<div class="postbox rw-body">
							<h3><?php echo $section['title']; ?></h3>
							
							<div id="section-<?php echo $section['id']; ?>" class="inside rw-ui-content-container rw-no-radius">
								<table>
									<tbody>
										<?php
										$odd = false;

										foreach( $section['fields'] as $field_id => $field ) {
											$odd = ! $odd;
											
											$value = false;
											
											if ( $addons_settings && isset( $addons_settings->{ $selected_key } ) ) {
												$addon_settings = $addons_settings->{ $selected_key };
												if ( isset( $addon_settings->{ $field_id } ) ) {
													$value = $addon_settings->{ $field_id };
												}
											}
											
											if ( ! $value ) {
												$value = $field['default'];
											}
										?>
										<tr id="<?php echo $field_id; ?>" class="rw-<?php $odd ? 'odd' : 'even'; ?>">
											<td>
												<span class="rw-ui-def"><?php echo $field['title']; ?>:</span>
											</td>
											<td>
												<?php if ( 'textfield' === $field['type'] ) { ?>
													<input type="text" id="<?php echo $field_id; ?>" name="field-<?php echo $field_id; ?>" value="<?php echo $value; ?>" style="width: 430px;" />
												<?php } else if ( 'textarea' === $field['type'] ) { ?>
													<textarea id="<?php echo $field_id; ?>" name="field-<?php echo $field_id; ?>" style="width: 430px; height: 100px;"><?php echo $value; ?></textarea>
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
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
			</div>
			<div id="rw_wp_set_widgets" class="rw-static">
				<div class="postbox">
					<h3 class="gradient">Template Variables</h3>
					<div class="inside">
						<ul>
							<li>
								<b>{{vote}}</b>
								<p class="description" id="tagline-description">The vote given by the user. e.g.: 5-star</p>
							</li>
							<li>
								<b>{{avg_rate}}</b>
								<p class="description" id="tagline-description">The average rating value. e.g.: 4.5</p>
							</li>
							<li>
								<b>{{post.title}}</b>
								<p class="description" id="tagline-description">The title of the current post.</p>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="addon" value="<?php echo $selected_key; ?>" />
		<input type="hidden" name="rw-save-addons-settings" />
	</form>
</div>