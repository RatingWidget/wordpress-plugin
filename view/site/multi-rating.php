<?php
    extract($VARS);

    $html = '';

	$multi_criteria = count($mr_multi_options->criteria) > 1;
	
	if ($multi_criteria) {
	    $mr_embed_options['uarid'] = $mr_summary_urid;
	} else {
		unset($mr_embed_options['uarid']);
	}
	
	$criteria_id = 1;
	foreach ($mr_multi_options->criteria as $criteria_key => $criteria) {
		$criteria_urid = ratingwidget()->get_rating_id_by_element($mr_element_id, $mr_element_class, $multi_criteria ? $criteria_id++ : false);

		$raw_rating = ratingwidget()->EmbedRawRating($criteria_urid, $mr_title, $mr_permalink, $mr_element_class, $mr_add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);
		
		// Defaults to &nbsp; instead of empty to keep the widths of all rating widgets same
		$label = (isset($criteria['label']) ? $criteria['label'] : '&nbsp;');
		$html .= '<tr>';
		$html .= '<td><nobr>' . $label . '</nobr></td>';
		$html .= '<td>' . $raw_rating . '</td>';
		$html .= '</tr>';
	}

    if (!empty($html))
    {
        if ($mr_multi_options->show_summary_rating && count($mr_multi_options->criteria) > 1) {
			unset($mr_embed_options['uarid']);
            $mr_embed_options['read-only'] = 'true';

            $raw_rating = ratingwidget()->EmbedRawRating($mr_summary_urid, $mr_title, $mr_permalink, $mr_element_class, $mr_add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);
				
            $html .= '<tr>';
            $html .= '<td><nobr>' . $mr_multi_options->summary_label . '</nobr></td>';
            $html .= '<td>' . $raw_rating . '</td>';
            $html .= '</tr>';
        }

        $dir = isset($mr_general_options->advanced) && isset($mr_general_options->advanced->layout) && is_string($mr_general_options->advanced->layout->dir) ?
            $mr_general_options->advanced->layout->dir :
            'ltr';

        $html = '<table class="rw-rating-table rw-' . $dir . (false !== $mr_hor_align ? ' rw-' . $mr_hor_align : '') . '">' . $html . '</table>';

        echo $html;
    }