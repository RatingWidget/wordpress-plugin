<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "gray";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "rgb(100,100,100)";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "rgb(100,100,100)";

    $theme = array(
        "name" => "star_gray1",
        "title" => "Gray Stars",
        "options" => $theme_options
    );
?>
