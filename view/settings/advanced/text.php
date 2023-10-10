<table id="rw_text_settings" cellspacing="0" style="display: none;">
<?php
    global $LNG_EN;
    
    $odd = true;
    $i = 0;
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/languages/en.php");
    
    $text = rw_options()->advanced->text;
    
    foreach ($LNG_EN as $key => $en_val)
    {
?>
    <tr id="rw_text_<?php echo esc_attr( $key ) ?>" class="rw-<?php echo ($odd) ? "odd" : "even"; ?>">
        <td<?php if ($i == 0) echo ' class="rw-ui-def-width"';?>>
            <span class="rw-ui-def"><?php echo esc_html( $en_val ) ?>:</span>
        </td>
        <td>
            <input onfocus="var e = this; setTimeout(function(){jQuery(e).select();}, 100);" onblur="RWM.Set.text('<?php echo esc_js( $key ) ?>');" type="text" id="rw_text_input_<?php echo esc_attr( $key ) ?>" value="<?php echo esc_attr($text->$key);?>" />
        </td>
    </tr>
<?php
        $odd = !$odd;
        $i++;
    }
?>
</table>
