<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "crystal_penguin";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";

    $theme = array(
        "name" => "star_crystal_penguin",
        "title" => "Crystal Penguins",
        "options" => $theme_options
    );
?>
