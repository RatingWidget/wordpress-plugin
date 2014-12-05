<div class="postbox rw-body">
    <h3>Save</h3>
    <div class="inside update-nag" style="margin: 0; border: 0;">
        <p class="submit" style="margin: 0; padding: 10px;">
            <input type="hidden" name="<?php echo rw_settings()->form_hidden_field_name; ?>" value="Y">
            <input type="hidden" id="rw_options_hidden" name="rw_options" value="" />
            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
            <?php if (!ratingwidget()->RW_IsPaying()) : ?>
            <a href="<?php echo ratingwidget()->GetUpgradeUrl() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button-secondary gradient rw-upgrade-button" target="_blank"><?php _e('Upgrade Now!', WP_RW__ID) ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>