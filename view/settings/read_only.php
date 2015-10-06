<?php
    $readOnly = rw_options()->readOnly;
?>
<td class="rw-ui-def-width">
    <span class="rw-ui-def"><?php _erw( 'read-only' ) ?>:</span>
</td>
<td>
    <div class="rw-ui-img-radio<?php if ($readOnly == false) echo " rw-selected";?>" onclick="rwStar.setReadOnly(false); rwNero.setReadOnly(false);">
        <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-unlocked rw-ui-default"></i></i>
        <span><?php _erw( 'active' ) ?></span>
        <input type="radio" name="rw-readonly" value="star"<?php if ($readOnly == false) echo ' checked="checked"';?> />
    </div>
    <div class="rw-ui-img-radio<?php if ($readOnly == true) echo " rw-selected";?>" onclick="rwStar.setReadOnly(true); rwNero.setReadOnly(true);">
        <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-locked"></i></i>
        <span><?php _erw( 'read-only' ) ?></span>
        <input type="radio" name="rw-readonly" value="nero"<?php if ($readOnly == true) echo ' checked="checked"';?> />
    </div>
</td>