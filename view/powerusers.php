<?php
     $custom_settings = rw_settings()->custom_settings;
     $custom_settings_enabled = rw_settings()->custom_settings_enabled;
 ?>
<div id="rw_custom_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body" style="margin-bottom: 0">
            <h3><?php _e('Power User Settings', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <label><input id="rw_custom_settings_enabled" name="rw_custom_settings_enabled" type="checkbox" value="1"<?php if ($custom_settings_enabled) echo ' checked="checked"' ?> /> Activate / In-Activate</label>
                <p>Here you can customize the ratings according to our <a href="<?php rw_the_site_url('documentation'); ?>" target="_blank">advanced documentation</a>.</p>
            </div>
        </div>
    </div>
</div>
<textarea name="rw_custom_settings" style="display: none;"></textarea>
<div id="rw_js_editor" style="min-height: 350px; left: 1px;"><?php
echo !empty($custom_settings) ? stripslashes($custom_settings) :
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
    rtl: "http://imageaddress.com/img.rtl.png"  // Right to Left rating
};

// Example: Disable mobile optimized UI (the fixed star button).
options.mobile = {"showTrigger": false};
'
?>
</div>
<script src="<?php echo WP_RW__PLUGIN_URL . 'resources/js/ace/ace.js'; ?>"></script>
<script>
// Initialize the editor
var rw_js_editor = ace.edit("rw_js_editor");
rw_js_editor.setTheme("ace/theme/monokai");

disableEditor(!jQuery('#rw_custom_settings_enabled').prop('checked'));

function disableEditor(bool) {
	if ( bool ) {
		rw_js_editor.setOptions({
			highlightActiveLine: false,
			highlightGutterLine: false,
			readOnly: true
		});
		rw_js_editor.container.style.opacity=0.5;
		rw_js_editor.getSession().setMode("ace/mode/text");
		rw_js_editor.renderer.$cursorLayer.element.style.opacity=0;
	} else {
		rw_js_editor.setOptions({
			highlightActiveLine: true,
			highlightGutterLine: true,
			readOnly: false
		})
		rw_js_editor.container.style.opacity=1;
		rw_js_editor.getSession().setMode("ace/mode/javascript");
		rw_js_editor.renderer.$cursorLayer.element.style.opacity=1;
	}
}

jQuery('textarea[name="rw_custom_settings"]').closest('form').submit(function() {
	var code = rw_js_editor.getValue();
	jQuery('textarea[name="rw_custom_settings"]').val(code);
});

jQuery('#rw_custom_settings_enabled').on('click', function() {
	disableEditor(!jQuery(this).prop('checked'));
});
</script>