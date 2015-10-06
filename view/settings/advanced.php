<table cellspacing="0">
	<tr>
		<td>
			<div id="rw_ui_advanced_container">
				<div id="advanced_trigger">
					<i class="rw-ui-expander"></i>
					<a><?php _erw( 'advanced-settings' ) ?></a>
				</div>
				<div id="rw_advanced_settings" style="display: none;">
					<br/>

					<div class="rw-tabs rw-clearfix">
						<div class="rw-selected"><?php _erw( 'font' ) ?></div>
						<div><?php _erw( 'layout' ) ?></div>
						<div><?php _erw( 'text' ) ?></div>
						<div id="rw_advanced_star_tab"<?php if ( rw_options()->type === "nero" ) {
							echo ' style="display: none;"';
						} ?>><?php _erw( 'star' ) ?>
						</div>
						<div id="rw_advanced_nero_tab"<?php if ( rw_options()->type === "star" ) {
							echo ' style="display: none;"';
						} ?>><?php _erw( 'thumbs' ) ?>
						</div>
					</div>
					<div id="rw_advanced_settings_body" class="rw-clearfix">
						<?php require_once( dirname( __FILE__ ) . "/advanced/font.php" ); ?>
						<?php require_once( dirname( __FILE__ ) . "/advanced/layout.php" ); ?>
						<?php require_once( dirname( __FILE__ ) . "/advanced/text.php" ); ?>
						<?php require_once( dirname( __FILE__ ) . "/advanced/star.php" ); ?>
						<?php require_once( dirname( __FILE__ ) . "/advanced/nero.php" ); ?>
					</div>
				</div>
			</div>
		</td>
	</tr>
</table>