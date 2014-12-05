<?php
     $custom_settings = rw_settings()->custom_settings;
     $custom_settings_enabled = rw_settings()->custom_settings_enabled;
 ?>
<div id="rw_custom_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('Power User Settings', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <p>Here you can customize the ratings according to our <a href="<?php rw_the_site_url('documentation'); ?>" target="_blank">advanced documentation</a>.</p>
                <textarea  name="rw_custom_settings" cols="50" rows="10"<?php if (!$custom_settings_enabled) echo ' readonly="readonly"' ?>><?php 
                    echo !empty($custom_settings) ?
                        stripslashes($custom_settings) :
'/*
 * We recommend to use this section only if you familiar with JavaScript.
 *
 * For your convenience, we have collected a set of examples which we are frequently
 * being asked about. Make sure to delete (or comment) the code you do NOT want to use.
 */

// Example: Hide ratings tooltip.
options.showTooltip = false;

// Example: Hide posts recommendations.
options.hideRecommendations = true;

// Example: Hide ratings report.
options.showReport = false;

// Example: Hide the ratings loading gif.
options.showLoader = false;

// Example: Hide the text bubble - only show the star ratings.
options.showInfo = false;

// Example: Set custom rating file.
//
// More information:
//      http://rating-widget.com/support/how-can-i-customize-the-ratings-image-theme-in-wordpress/
options.style = RW.CUSTOM;
options.imgUrl = {
    ltr: "http://imageaddress.com/img.ltr.png", // Left to Right rating
    ltr: "http://imageaddress.com/img.rtl.png"  // Right to Left rating
};

// Example: Disable mobile optimized UI (the fixed star button).
options.mobile = {"showTrigger": false};
'
                ?></textarea>
                <label><input name="rw_custom_settings_enabled" type="checkbox" value="1"<?php if ($custom_settings_enabled) echo ' checked="checked"' ?> /> Activate / In-Activate</label>
            </div>
        </div>
    </div>
</div>
