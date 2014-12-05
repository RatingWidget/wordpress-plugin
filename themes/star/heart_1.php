<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "heart";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#fc17fc";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#fc17fc";
//    $theme_options->advanced->font->type = "Comic Sans MS";

    $theme = array(
        "name" => "star_heart1",
        "title" => "Hearts",
        "options" => $theme_options
    );
?>
