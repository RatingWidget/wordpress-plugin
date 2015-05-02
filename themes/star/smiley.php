<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "smiley";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#806000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#806000";

    $theme = array(
        "name" => "star_smiley",
        "title" => "Smiley",
        "options" => $theme_options
    );
?>
