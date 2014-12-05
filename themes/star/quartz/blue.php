<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "quartz_blue";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkBlue";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkBlue";

    $theme = array(
        "name" => "star_quartz_blue",
        "title" => "Quartz Blue Stars",
        "options" => $theme_options
    );
?>