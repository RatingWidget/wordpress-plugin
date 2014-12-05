<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "oxygen1_red";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "red";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "red";

    $theme = array(
        "name" => "star_oxygen1_red",
        "title" => "Oxygen Red Stars 2",
        "options" => $theme_options
    );
?>