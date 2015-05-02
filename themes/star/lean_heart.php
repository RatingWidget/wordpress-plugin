<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "lean_heart";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#fc17fc";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#fc17fc";

    $theme = array(
        "name" => "star_lean_heart",
        "title" => "Lean Hearts",
        "options" => $theme_options
    );
?>
