<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "blue";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkBlue";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkBlue";

    $theme = array(
        "name" => "star_blue1",
        "title" => "Blue Stars",
        "options" => $theme_options
    );
?>
