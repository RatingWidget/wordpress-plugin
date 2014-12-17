<div class="wrap rw-dir-ltr rw-report">
    <div id="poststuff" style="width: 750px;">
        <div id="rw_wp_upgrade_widget" class="postbox">
            <h3 class="gradient"><?php _e('Upgrade now to get reports and more Professional Features', WP_RW__ID) ?></h3>
            <div class="inside">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <div style="font-size: 15px;line-height: 21px;">
                                <?php _e('Reports provides you with an analytical overview of your blog-ratings\' votes in one page.
                                Here, you can gain an understanding of how interesting and attractive your blog elements (e.g. posts, pages),
                                how active your users, and check the segmentation of the votes.', WP_RW__ID) ?>
                            </div>
                            <div id="rw_new_wp_subscribe">
                                <input type="hidden" id="rw_wp_uid" value="<?php echo WP_RW__SITE_PUBLIC_KEY; ?>" />
                                <a href="<?php echo rw_fs()->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button-primary gradient" style="display: block; text-align: center;"><?php _e('Learn More', WP_RW__ID) ?></a>
                            </div>
                        </td>
                        <td>
                            <ul id="rw_wp_premium_features" style="float: right; padding-left: 50px; border-left: 1px solid rgb(152, 223, 152);">
                                <li><b><?php _e('Google Rich Snippets (schema.org)', WP_RW__ID) ?></b></li>
                                <li><b><?php _e('Advanced Ratings\' Analytics', WP_RW__ID) ?></b></li>
                                <li><b><?php _e('White-labeled - Ads free', WP_RW__ID) ?></b></li>
                                <li><b><?php _e('bbPress Forum Ratings', WP_RW__ID) ?></b></li>
                                <li><b><?php _e('User Reputation-Rating (BuddyPress/bbPress)', WP_RW__ID) ?></b></li>
                                <li><?php _e('Priority Email Support', WP_RW__ID) ?></li>
                                <li><?php _e('SSL Support', WP_RW__ID) ?></li>
                                <li><?php _e('Secure Connection (Fraud protection)', WP_RW__ID) ?></li>
                                <li><?php _e('WMPL Language Auto-Selection', WP_RW__ID) ?></li>
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <br />
    <img src="<?php echo WP_RW__ADDRESS_IMG . "wordpress/rw.report.example.png"  ?>" alt="">
</div>