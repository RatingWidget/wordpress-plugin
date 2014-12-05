<?php
    $theme_options = new stdClass();
    $theme_options->type = "nero";
    $theme_options->style = "christmas";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";
        
    $theme = array(
        "name" => "christmas",
        "title" => "Christmas Santa's Glove",
        "options" => $theme_options
    );
?>
