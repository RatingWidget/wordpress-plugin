<?php
    $theme_options = new stdClass();
    $theme_options->type = "nero";
    $theme_options->style = "arrows";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";
        
    $theme = array(
        "name" => "arrows_1",
        "title" => "Arrows 1",
        "options" => $theme_options
    );
?>
