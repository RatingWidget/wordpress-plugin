<div class="postbox rw-body">
	<h3><?php _erw( 'save' ) ?></h3>

	<div class="inside update-nag" style="margin: 0; border: 0;">
		<p class="submit" style="margin: 0; padding: 10px;">
			<input type="hidden" name="<?php echo rw_settings()->form_hidden_field_name; ?>" value="Y">
			<input type="hidden" id="rw_options_hidden" name="rw_options" value=""/>
			<input type="submit" name="Submit" class="button-primary" value="<?php _erw( 'save-changes' ) ?>"/>
			<?php if ( rw_fs()->is_not_paying() ) : ?>
				<a href="<?php echo rw_fs()->get_upgrade_url() ?>"
				   onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;"
				   class="button-secondary gradient rw-upgrade-button"><?php _erw( 'upgrade-now' ) ?></a>
			<?php endif; ?>
		</p>
	</div>
</div>