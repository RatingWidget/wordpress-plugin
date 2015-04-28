<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "christmas_red_ball";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "red";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "red";

    $theme = array(
        "name" => "star_christmas_red_ball",
        "title" => "Christmas Red Ball 1",
        "options" => $theme_options
    );
?>