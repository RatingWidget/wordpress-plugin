<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "newyear_tree";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";

    $theme = array(
        "name" => "star_newyear_tree",
        "title" => "New-Year Trees",
        "options" => $theme_options
    );
?>
