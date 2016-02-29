<div id="rw_wp_upgrade_widget" class="postbox">
	<h3 class="gradient"><?php _erw( 'upgrade_why' ) ?></h3>

	<div class="inside">
		<ul id="rw_wp_premium_features">
			<li><b><?php _erw( 'upgrade_rich-snippets' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_analytics' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_white-labeled' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_bbpress-forum-topics' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_ip-identification' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_custom-design' ) ?></b></li>
			<li><b><?php _erw( 'upgrade_unlimited-criteria' ) ?></b></li>
			<li><?php _erw( 'upgrade_reputation-rating' ) ?></li>
			<li><?php _erw( 'upgrade_priority-email' ) ?></li>
			<li><?php _erw( 'upgrade_ssl' ) ?></li>
			<li><?php _erw( 'upgrade_secure-connection' ) ?></li>
			<li><?php _erw( 'upgrade_wpml' ) ?></li>
		</ul>
		<div id="rw_new_wp_subscribe">
			<a style="display: block; text-align: center;" href="<?php echo rw_fs()->get_upgrade_url() ?>"
			   onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;"
			   class="button-secondary gradient rw-upgrade-button"><?php _erw( 'learn-more' ) ?></a>
		</div>
	</div>
</div>