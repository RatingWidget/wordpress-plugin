<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "gray";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#999";
    $theme_options->advanced->font->size = "11px";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#999";
    
    $theme = array(
        "name" => "star_bp1",
        "title" => "BuddyPress Stars",
        "options" => $theme_options
    );
?>
