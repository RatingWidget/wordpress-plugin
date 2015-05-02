<?php
     $font = rw_options()->advanced->font;
 ?>
<table id="rw_font_settings" cellspacing="0">
    <tr id="rw_font_size" class="rw-odd">
        <td>
            <span class="rw-ui-def">Size:</span>
        </td>
        <td>
            <select onchange="rwStar.setFontSize(this.value + 'px'); rwNero.setFontSize(this.value + 'px'); RWM.Code.refresh();">
                <?php
                    $font_sizes = array(6,8,9,10,11,12,13,14,15,16,18,20,24,30,36);
                    foreach ($font_sizes as $size)
                    {
                        echo '<option value="' . $size . '"' . (($size . "px" == $font->size) ? ' selected="selected"' : '') . '>' . $size . 'px</option>';
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr id="rw_font_color" class="rw-even">
        <td>
            <span class="rw-ui-def">Color:</span>
        </td>
        <td>
            <div id="colorSelector" class="rw-color-selector"><div style="background-color: <?php echo $font->color;?>"></div></div>
        </td>
    </tr>
    <tr id="rw_font_hover_color" class="rw-odd">
        <td>
            <span class="rw-ui-def">HOver:</span>
        </td>
        <td>
            <div id="hoverColorSelector" class="rw-color-selector"><div style="background-color: <?php echo $font->hover->color;?>"></div></div>
        </td>
    </tr>
    <tr id="rw_font_type" class="rw-even">
        <td>
            <span class="rw-ui-def">Type:</span>
        </td>
        <td>
            <?php
                $fonts = array(
                    "inherit" => 'inherit',
                    "arial" => 'arial',
                    "courier" => 'courier',
//                    "lucida" => 'lucida grande',
                    "tahoma" => 'tahoma',
                    "times" => 'times',
                    "verdana" => 'verdana',
                );
                
                foreach ($fonts as $label => $f)
                {
            ?>
            <div class="rw-ui-img-radio<?php if ($font->type == $f) echo " rw-selected";?> rw-font-<?php echo $label;?>" onclick="rwStar.setFontType('<?php echo $f;?>'); rwNero.setFontType('<?php echo $f;?>');">
                <span class="rw-font-sample">A</span>
                <span><?php echo ucwords($label);?></span>
                <input type="radio" name="rw-font-type" value="<?php echo $f;?>"<?php if ($font->type == $f) echo ' checked="checked"';?> />
            </div>
            <?php
                }
            ?>
        </td>
    </tr>
    <tr id="rw_font_style" class="rw-odd">
        <td class="rw-ui-def-width">
            <span class="rw-ui-def">Style:</span>
        </td>
        <td>
            <div class="rw-ui-img-radio<?php if ($font->bold) echo " rw-selected";?>" onclick="rwStar.toggleBold(); rwNero.toggleBold();">
                <i class="rw-ui-holder"><i id="rw_ui_bold" class="rw-ui-sprite rw-ui-large"></i></i>
                <span><b>Bold</b></span>
                <input type="checkbox" name="rw-font-bold" value="0"<?php if ($font->bold) echo ' checked="checked"';?> />
            </div>
            <div class="rw-ui-img-radio<?php if ($font->italic) echo " rw-selected";?>" onclick="rwStar.toggleItalic(); rwNero.toggleItalic();">
                <i class="rw-ui-holder"><i id="rw_ui_italic" class="rw-ui-sprite rw-ui-large"></i></i>
                <span><i>Italic</i></span>
                <input type="checkbox" name="rw-font-italic" value="1"<?php if ($font->italic) echo ' checked="checked"';?> />
            </div>
        </td>
    </tr>
</table> 
