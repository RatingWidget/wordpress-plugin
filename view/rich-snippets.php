<div id="rich_snippets" class="postbox">
	<div class="google-header">
		<b class="blue"></b>
		<b class="red"></b>
		<b class="yellow"></b>
		<b class="green"></b>
	</div>
	<div class="inside">
		<div class="title"><img src="<?php echo rw_get_site_img_path( '/common/google-logo.png' ) ?>"
		                        alt="Google Logo"/> <span>Rich Snippets</span></div>
		<p>
			<? _erw( 'rich-snippets_marketing' ) ?>
		</p>
		<img class="featured-image" src="<?php echo rw_get_site_img_path( '/wordpress/rich-snippets.png' ) ?>"
		     alt="Google SERP with Rich-Snippets">

		<div class="disclaimer">
			<a class="trigger" href="#"
			   onclick="jQuery(this).parent().find('p').toggle(); return false;">[+] <?php _erw( 'disclaimer-uppercase' ) ?></a>

			<p style="display: none">
				<?php printf(
					__rw( 'rich-snippets_disclaimer' ),
					sprintf(
						'<a href="%s" target="_blank">%s</a>',
						'http://www.google.com/webmasters/tools/richsnippets',
						__rw( 'rich-snippets_testing-tool' )
					),
					'<b>' . __rw( 'rich-snippets_disclaimer-bold' ) . '</b>'
				) ?>
			</p>
		</div>
		<div>
			<a style="display: block; text-align: center;" href="<?php echo rw_fs()->get_upgrade_url() ?>"
			   onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'richsnippets_button', 1, true]); _gaq.push(['_link', this.href]); return false;"
			   class="button-secondary gradient rw-upgrade-button"><?php _erw( 'upgrade-to-pro' ) ?></a>
		</div>
	</div>
</div>