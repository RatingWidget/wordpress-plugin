<td><span class="rw-ui-def"><?php _erw( 'language' ) ?>:</span></td>
<td>
    <select id="rw_lng_select" tabindex="1" name="rw_language" style="font-size: 12px;" onchange="RWM.Set.language(this.value);">
        <?php
            $language_str = isset(rw_settings()->language_str) ? rw_settings()->language_str : "en";
            foreach (rw_settings()->languages as $short => $long)
                echo '<option value="' . esc_attr( $short ) . '"' . (($short == $language_str) ? ' selected="selected"' : '') . '>' . esc_html( $long ) . '</option>';
        ?>
    </select>
</td>