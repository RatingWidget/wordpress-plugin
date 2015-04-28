<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "green";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "darkGreen";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "darkGreen";


    $theme = array(
        "name" => "star_green1",
        "title" => "Green Stars",
        "options" => $theme_options
    );
?>
