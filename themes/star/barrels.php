<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "barrels";

    $theme = array(
        "name" => $theme_options->type . '_' . $theme_options->style,
        "title" => "Jeegy's Barrels",
        "options" => $theme_options
    );