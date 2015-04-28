<?php
     $layout = rw_options()->advanced->layout;
 ?>
<table id="rw_layout_settings" cellspacing="0" style="display: none;">
    <tr id="rw_layout_direction" class="rw-odd">
        <td class="rw-ui-def-width">
            <span class="rw-ui-def">Direction:</span>
        </td>
        <td>
        <?php
            $directions = array("ltr" => "Left to Right", "rtl" => "Right to Left");
            foreach ($directions as $dir => $direction)
            {
                $selected = strtolower($dir) == $layout->dir;
        ?>
            <div class="rw-ui-img-radio<?php if ($selected) echo ' rw-selected';?>" onclick="RWM.Set.direction(RW.DIR.<?php echo strtoupper($dir);?>);">
                <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-large rw-ui-<?php echo strtolower($dir);?>"></i></i>
                <span><?php echo $direction;?></span>
                <input type="radio" name="rw-direction" value="0"<?php if ($selected) echo ' checked="checked"';?> />
            </div>
        <?php
            }
        ?>
        </td>
    </tr>
    <tr id="rw_layout_align" class="rw-even">
        <td>
            <span class="rw-ui-def">Alignment:</span>
        </td>
        <td>
            <?php
                $vers = array("top", "middle", "bottom");
                $hors = array("left", "center", "right");
                foreach ($vers as $i => $ver)
                {
                    if ($i > 0) echo "<br />";
                    echo '<div class="rw-clearfix">';
                    foreach ($hors as $j => $hor)
                    {
                        if ($ver == "middle" && $hor == "center")
                        {
                            echo '<div class="rw-ui-img-radio-holder"></div>';
                        }
                        else
                        {
                            $selected = ($ver == $layout->align->ver && $hor == $layout->align->hor);
            ?>
                <div class="rw-ui-img-radio<?php if ($selected) echo ' rw-selected';?>" onclick="RWM.Set.align('<?php echo $ver . "', '" . $hor?>')">
                    <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-large rw-ui-<?php echo $ver . $hor;?>"></i></i>
                    <span><?php echo ucwords($ver) . ucwords($hor);?></span>
                    <input type="radio" name="rw-align" value="<?php echo $i*3 + $j;?>"<?php if ($selected) echo ' checked="checked"'?> />
                </div>
            <?php                                                                  
                        }
                    }
                    echo '</div>';
                }
            ?>
        </td>
    </tr>
    <tr id="rw_layout_line_height" class="rw-odd">
        <td>
            <span class="rw-ui-def">Line Height:</span>
        </td>
        <td>
            <select onchange="rwStar.setLineHeight(this.value + 'px'); rwNero.setLineHeight(this.value + 'px'); RWM.Code.refresh();">
                <?php
                    $line_heights = array(6,8,9,10,11,12,13,14,15,16,18,20,24,30,36);
                    foreach ($line_heights as $height)
                    {
                        echo '<option value="' . $height . '"' . (($height . "px" == $layout->lineHeight) ? ' selected="selected"' : '') . '>' . $height . 'px</option>';
                    }
                ?>
            </select>
        </td>
    </tr>
</table>
