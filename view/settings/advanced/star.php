<table id="rw_star_settings" cellspacing="0" style="display: none;">
    <tr id="rw_star_stars_number" class="rw-odd">
        <td class="rw-ui-def-width">
            <span class="rw-ui-def">Stars:</span>
        </td>
        <td>
            <select onchange="rwStar.setStarsNum(this.value); RWM.Code.refresh();">
                <?php
                    $stars = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
                    $stars_num = rw_options()->advanced->star->stars;
                    foreach ($stars as $num)
                    {
                        echo '<option value="' . $num . '"' . (($num == $stars_num) ? ' selected="selected"' : '') . '>' . $num . '</option>';
                    }
                ?>
            </select>
        </td>
    </tr>
</table> 
