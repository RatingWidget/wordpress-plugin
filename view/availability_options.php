<?php
    $types = ucwords(str_replace("-", " ", rw_settings_rating_type()));
    $type = substr($types, 0, strlen($types) - 1);
    $availability = rw_settings()->availability;
?>
<div id="rw_availability_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('User Availability Settings', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($availability == 0) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite rw-ui-availability-active"></i> <input type="radio" name="rw_availability" value="0" <?php checked($availability, 0) ?>> <span>Show active <?php echo $types;?> ratings for all users.</span>
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($availability == 1) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite rw-ui-availability-disabled"></i> <input type="radio" name="rw_availability" value="1" <?php checked($availability, 1) ?>> <span>Disable <?php echo $types;?> ratings for unlogged users.</span>
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($availability == 2) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite  rw-ui-availability-hidden"></i> <input type="radio" name="rw_availability" value="2" <?php checked($availability, 2) ?>> <span>Hide <?php echo $types;?> ratings from unlogged users.</span>
                </div>
            </div>
        </div>
    </div>
</div>
