<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "flames";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkOrange";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkOrange";

    $theme = array(
        "name" => "star_flames",
        "title" => "Flames",
        "options" => $theme_options
    );
?>
