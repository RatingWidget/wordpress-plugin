<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "christmas_blue_ball";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkBlue";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkBlue";

    $theme = array(
        "name" => "star_christmas_blue_ball",
        "title" => "Christmas Blue Ball 1",
        "options" => $theme_options
    );
?>