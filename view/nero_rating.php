<?php
if (!function_exists("show_nero_rating")){
    function show_nero_rating($roptions, $likes = 0, $dislikes = 0, $label = "")
    {
        $likes = max(0, $likes);
        $dislikes = max(0, $dislikes);
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
    <div class="rw-action-area rw-clearfix"><span class="rw-ui-like"><i class="rw-ui-like-icon"></i><span class="rw-ui-like-label" style="<?php echo $font_type . $font_size . $line_height;?>"><?php echo $likes;?></span></span><span class="rw-ui-dislike"><i class="rw-ui-dislike-icon"></i><span class="rw-ui-dislike-label" style="<?php echo $font_type . $font_size . $line_height;?>"><?php echo $dislikes;?></span></span>
    <a class="rw-report-link" target="_blank" title="Rating-Widget Report"></a></div><span class="rw-ui-info" style="<?php echo $font_type . $font_color . $font_size . $line_height;?>"><?php echo $label;?></span>
</div>
<?php
    }
}

show_nero_rating($vars["options"], $vars["likes"], $vars["dislikes"], $vars["label"]);
?>