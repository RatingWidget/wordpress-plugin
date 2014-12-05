<div class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('Rating-Widget Options', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <table>
                    <?php $odd = false; ?>
                    <tr id="rw_language" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/language.php"); ?>
                    </tr>
                    <tr id="rw_rate_type" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/type.php"); ?>
                    </tr>
                    <tr id="rw_theme" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/theme.php"); ?>
                    </tr>
                    <tr id="rw_star_size" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/size.php"); ?>
                    </tr>
                    <?php if (ratingwidget()->IsBuddyPressInstalled() || ratingwidget()->IsBBPressInstalled()) : ?>
                    <tr id="rw_rate_background" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/background.php"); ?>
                    </tr>
                    <?php endif; ?>
                    <tr id="rw_rate_readonly" class="rw-<?php echo (($odd = !$odd) ? "odd" : "even");?>">
                        <?php rw_include_once_view("settings/read_only.php"); ?>
                    </tr>
                </table>
                <?php rw_include_once_view("settings/advanced.php");?>
            </div>
        </div>
    </div>
</div>
