<?php
    extract($VARS);

    $html = '';

    $mr_embed_options['uarid'] = $mr_summary_urid;

    foreach ($mr_multi_options->criteria as $criteria_id => $criteria) {
        $criteria_urid = ratingwidget()->get_rating_id_by_element($mr_element_id, $mr_element_class, $criteria_id);

        $raw_rating = ratingwidget()->EmbedRawRating($criteria_urid, $mr_title, $mr_permalink, $mr_element_class, $mr_add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);

        $html .= '<tr>';
        $html .= '<td><nobr>' . $criteria['label'] . '</nobr></td>';
        $html .= '<td>' . $raw_rating . '</td>';
        $html .= '</tr>';
    }


    if (!empty($html))
    {
        if ($mr_multi_options->show_summary_rating && count($mr_multi_options->criteria) > 1) {
            $mr_embed_options['read-only'] = 'true';
            $mr_embed_options['uarid'] = 0;
            $raw_rating = ratingwidget()->EmbedRawRating($mr_summary_urid, $mr_title, $mr_permalink, $mr_element_class, $mr_add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);
            $html .= '<tr>';
            $html .= '<td><nobr>' . $mr_multi_options->summary_label . '</nobr></td>';
            $html .= '<td>' . $raw_rating . '</td>';
            $html .= '</tr>';
        }

        $dir = isset($mr_general_options->advanced) && isset($mr_general_options->advanced->layout) && is_string($mr_general_options->advanced->layout->dir) ?
            $mr_general_options->advanced->layout->dir :
            'ltr';

        $html = '<table class="rw-rating-table rw-' . $dir . '">' . $html . '</table>';

        echo $html;
    }