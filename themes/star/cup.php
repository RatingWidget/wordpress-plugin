<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "cups";

    $theme = array(
        "name" => $theme_options->type . '_' . $theme_options->style,
        "title" => "Cups",
        "options" => $theme_options
    );