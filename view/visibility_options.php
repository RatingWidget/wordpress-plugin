<?php
    $types = ucwords(str_replace("-", " ", rw_settings_rating_type()));
    $type = substr($types, 0, strlen($types) - 1);
    
    function implode_or_empty($array)
    {
        if (is_string($array))
            return $array;
            
        if (!is_array($array))
            return "";
            
        return implode(',', $array);
    }
    
    $visibility = rw_settings()->visibility;
?>
<div id="rw_visibiliy_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3><?php _e('Specific Visibility Settings', WP_RW__ID) ?></h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($visibility->selected == 0) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite rw-ui-visibility-all"></i> <input type="radio" name="rw_visibility" value="0" <?php if ($visibility->selected == 0) echo ' checked="checked"';?>> <span><?php printf(__('Show rating on every %s', WP_RW__ID), $type); ?></span>
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($visibility->selected == 1) echo ' rw-selected';?>" onclick="jQuery(this).children('input[type=text]').focus();">
                    <i class="rw-ui-sprite rw-ui-visibility-exclude"></i> <input type="radio" name="rw_visibility" value="1" <?php if ($visibility->selected == 1) echo ' checked="checked"';?>> <span><?php printf(__('Show rating on every %1$s except the listed %2$s', WP_RW__ID), $type, $types); ?> </span> <input type="text" name="rw_visibility_exclude" value="<?php echo implode_or_empty($visibility->exclude);?>" />
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($visibility->selected == 2) echo ' rw-selected';?>" onclick="jQuery(this).children('input[type=text]').focus();">
                    <i class="rw-ui-sprite  rw-ui-visibility-include"></i> <input type="radio" name="rw_visibility" value="2" <?php if ($visibility->selected == 2) echo ' checked="checked"';?>> <span><?php printf(__('Show rating on only the listed %s', WP_RW__ID), $types); ?></span> <input type="text" name="rw_visibility_include" value="<?php echo implode_or_empty($visibility->include);?>" />
                </div>
                <span style="font-size: 10px; background: white; padding: 2px; border: 1px solid gray; display: block; margin-top: 5px; font-weight: bold; background: rgb(240,240,240); color: black;"><?php _e('Seperate ids with commas (e.g. "3,17,8").', WP_RW__ID) ?></span>
            </div>
        </div>
    </div>
</div>
