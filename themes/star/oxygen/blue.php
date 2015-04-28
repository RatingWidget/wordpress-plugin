<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "oxygen_blue";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkBlue";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkBlue";

    $theme = array(
        "name" => "star_oxygen_blue",
        "title" => "Oxygen Blue Stars 1",
        "options" => $theme_options
    );
?>