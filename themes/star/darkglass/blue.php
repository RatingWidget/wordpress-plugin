<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "darkglass_blue";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkBlue";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkBlue";

    $theme = array(
        "name" => "star_darkglass_blue",
        "title" => "Dark Glass Blue Stars",
        "options" => $theme_options
    );
?>