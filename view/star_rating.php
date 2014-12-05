<?php
if (!function_exists("show_star_rating")){
    function show_star_rating($roptions, $rate = 5, $label = "")
    {
        $rate = min(5, max(0, $rate));
        $type = " rw-ui-" . $roptions->type;
        $size = " rw-size-" . $roptions->size;
        $style = " rw-style-" . $roptions->style;
        $theme = isset($roptions->theme) ? " rw-theme-" . $roptions->theme : "";
        $class = isset($roptions->rclass) ? " rw-class-" . $roptions->rclass : "";
        $halign = " rw-halign-" . $roptions->advanced->layout->align->hor;
        $valign = " rw-valign-" . $roptions->advanced->layout->align->ver;
        $dir = " rw-dir-" . $roptions->advanced->layout->dir;
        $font_color = "color: " . (isset($roptions->advanced->font->color) ? $roptions->advanced->font->color : "black") . ";";
        $font_type = "font-family: " . (isset($roptions->advanced->font->type) ? $roptions->advanced->font->type : "arial") . ";";
        $font_size = "font-size: " . (isset($roptions->advanced->font->size) ? $roptions->advanced->font->size : "12px") . ";";
        $line_height = "line-height: " . (isset($roptions->advanced->layout->lineHeight) ? $roptions->advanced->layout->lineHeight : "16px") . ";";
?>
<div class="rw-ui-container rw-no-render<?php echo $type . $size . $dir . $halign . $valign . $style;?>">
    <div class="rw-action-area rw-clearfix"><ul class="rw-ui-stars"><?php
        $floor = floor($rate);
        for ($i = 0; $i < $floor; $i++)
        {
            echo '<li class="rw-ui-star-selected"></li>';
        }
        $diff = $rate - $floor;
        if ($diff > 0.75){
            echo '<li class="rw-ui-star-selected"></li>';
        }else if ($diff > 0.25 && $diff < 0.75){
            echo '<li class="rw-ui-star-half-selected"></li>';
        }else if ($diff < 0.25){
            echo '<li></li>';
        }
        for ($i = $i + 1; $i < 5; $i++){
            echo '<li></li>';
        }
    ?></ul><a class="rw-report-link" target="_blank" title="Rating-Widget Report"></a></div><span class="rw-ui-info" style="<?php echo $font_type . $font_color . $font_size . $line_height;?>"><?php echo $label;?></span>
</div>
<?php
    }
}

show_star_rating($vars["options"], $vars["rate"], $vars["label"]);
?>