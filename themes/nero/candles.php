<?php
    $theme_options = new stdClass();
    $theme_options->type = "nero";
    $theme_options->style = "candles";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#000";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#000";
        
    $theme = array(
        "name" => "candles",
        "title" => "Candles (by Avelim)",
        "options" => $theme_options
    );
?>
