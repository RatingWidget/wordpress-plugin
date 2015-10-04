<div class="wrap rw-dir-ltr rw-report">
	<div id="poststuff" style="width: 750px;">
		<div id="rw_wp_upgrade_widget" class="postbox">
			<h3 class="gradient"><?php _erw( 'upgrade_reports' ) ?></h3>

			<div class="inside">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 50%; vertical-align: top;">
							<div style="font-size: 15px;line-height: 21px;">
								<?php _erw( 'dummy-report-marketing' ) ?>
							</div>
							<div id="rw_new_wp_subscribe">
								<input type="hidden" id="rw_wp_uid"
								       value="<?php echo rw_account()->site_public_key ?>"/>
								<a href="<?php echo rw_fs()->get_upgrade_url() ?>"
								   onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;"
								   class="button-primary gradient"
								   style="display: block; text-align: center;"><?php _erw( 'learn-more' ) ?></a>
							</div>
						</td>
						<td>
							<ul id="rw_wp_premium_features"
							    style="float: right; padding-left: 50px; border-left: 1px solid rgb(152, 223, 152);">
								<li><b><?php _erw( 'upgrade_rich-snippets' ) ?></b></li>
								<li><b><?php _erw( 'upgrade_analytics' ) ?></b></li>
								<li><b><?php _erw( 'upgrade_white-labeled' ) ?></b></li>
								<li><b><?php _erw( 'upgrade_bbpress-forum-topics' ) ?></b></li>
								<li><b><?php _erw( 'upgrade_reputation-rating' ) ?></b></li>
								<li><?php _erw( 'upgrade_priority-email' ) ?></li>
								<li><?php _erw( 'upgrade_ssl' ) ?></li>
								<li><?php _erw( 'upgrade_secure-connection' ) ?></li>
								<li><?php _erw( 'upgrade_wpml' ) ?></li>
							</ul>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<br/>
	<img src="<?php echo WP_RW__ADDRESS_IMG . "wordpress/rw.report.example.png" ?>" alt="">
</div>