<?php
    $theme_options = new stdClass();
    $theme_options->type = "nero";
    $theme_options->style = "thumbs_bp";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#999";
    $theme_options->advanced->font->size = "11px";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#999";
    
    $theme = array(
        "name" => "thumbs_bp1",
        "title" => "BuddyPress Thumbs",
        "options" => $theme_options
    );
?>
