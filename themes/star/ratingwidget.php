<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "ratingwidget";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000";

    $theme = array(
        "name" => "star_ratingwidget",
        "title" => "Rating-Widget",
        "options" => $theme_options
    );
?>
