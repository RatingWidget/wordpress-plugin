<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "santa_hat";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "red";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "red";

    $theme = array(
        "name" => "star_santa_hat",
        "title" => "Christmas Santa Hat",
        "options" => $theme_options
    );
?>