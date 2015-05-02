<?php
    $theme_options = new stdClass();
    $theme_options->type = "nero";
    $theme_options->style = "masks";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";
        
    $theme = array(
        "name" => "masks",
        "title" => "Masks (by David Shenberger)",
        "options" => $theme_options
    );
?>
