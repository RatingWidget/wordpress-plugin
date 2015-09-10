<?php
    extract($VARS);
	
    $html = '';

	// Backup the value of 'hide-recommendations'
    $hide_recommendations = isset($mr_embed_options['hide-recommendations']) ? $mr_embed_options['hide-recommendations'] : false;
	$original_uarid = isset($mr_embed_options['uarid']) ? $mr_embed_options['uarid'] : false;

	$criteria_count = count($mr_multi_options->criteria);
	$multi_criteria = $criteria_count > 1;

	if ($multi_criteria) {
		if ( ! rw_fs()->is_plan_or_trial__premium_only( 'professional' ) ) {
			if ( $criteria_count > 3 ) {
				$mr_multi_options->criteria = array_splice( $mr_multi_options->criteria, 0, 3 );
			}
		}

		$mr_embed_options['uarid']                = $mr_summary_urid;
		$mr_embed_options['hide-recommendations'] = 'true';
	}
	
	$criteria_id = 1;
	$rw_no_labels = true;
	
	foreach ($mr_multi_options->criteria as $criteria_key => $criteria) {
		$rclass = $mr_element_class;
		
		$add_schema = $mr_add_schema;
		
		if ($multi_criteria) {
			$rclass .= '-criteria-' . $criteria_id;
			
			// If summary rating is visible, don't add snippets because they are
			// already added through the 2nd call to the EmbedRawRating method below.
			if ( $mr_multi_options->show_summary_rating ) {
				$add_schema = false;
			} else if ( !$mr_multi_options->show_summary_rating && ($criteria_id < $criteria_count) ) {
				// If the summary rating is not visible, don't add snippets for every call to
				// EmbedRawRating method for each criterion and only add snippets after reaching
				// the last criterion.
				$add_schema = false;
			}
		}
		
		$criteria_urid = ratingwidget()->get_rating_id_by_element($mr_element_id, $mr_element_class, $multi_criteria ? $criteria_id++ : false);
		
		$raw_rating = ratingwidget()->EmbedRawRating($criteria_urid, $mr_title, $mr_permalink, $rclass, $add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);
		
		// Defaults to &nbsp; instead of empty to keep the widths of all rating widgets same
		if (isset($criteria['label'])) {
			$label = $criteria['label'];
			
			if ($rw_no_labels) {
				$rw_no_labels = false;
			}
		} else {
			$label = '&nbsp;';
		}
		$html .= '<tr>';
		$html .= '<td><nobr>' . $label . '</nobr></td>';
		$html .= '<td>' . $raw_rating . '</td>';
		$html .= '</tr>';
	}

    if (!empty($html)) {
        if ($mr_multi_options->show_summary_rating && $multi_criteria) {
            if ($hide_recommendations) {
                // Restore the value of hide-recommendations
                $mr_embed_options['hide-recommendations'] = 'true';
            } else {
				unset($mr_embed_options['hide-recommendations']);
			}

	        if (false === $original_uarid)
				unset($mr_embed_options['uarid']);
	        else
		        // Restore original aggregated rating ID.
		        $mr_embed_options['uarid'] = $original_uarid;
			
            $mr_embed_options['read-only'] = 'true';
			$mr_embed_options['force-sync'] = 'true';
			
            $raw_rating = ratingwidget()->EmbedRawRating($mr_summary_urid, $mr_title, $mr_permalink, $mr_element_class, $mr_add_schema, $mr_hor_align, $mr_custom_style, $mr_embed_options);
				
			// Defaults to &nbsp; instead of empty to keep the widths of all rating widgets same
			if (isset($mr_multi_options->summary_label)) {
				$summary_label = $mr_multi_options->summary_label;
				
				if ($rw_no_labels) {
					$rw_no_labels = false;
				}
			} else {
				$summary_label = '&nbsp;';
			}
            $html .= '<tr>';
            $html .= '<td><nobr>' . $summary_label . '</nobr></td>';
            $html .= '<td>' . $raw_rating . '</td>';
            $html .= '</tr>';
        }

        $dir = isset($mr_general_options->advanced) && isset($mr_general_options->advanced->layout) && is_string($mr_general_options->advanced->layout->dir) ?
            $mr_general_options->advanced->layout->dir :
            'ltr';

		$table_classes = 'rw-rating-table rw-' . $dir;
		$table_classes .= (false !== $mr_hor_align ? ' rw-' . $mr_hor_align : '');
		$table_classes .= ($rw_no_labels ? ' rw-no-labels' : '');
		
        $html = '<table class="' . $table_classes . '">' . $html . '</table>';

        echo $html;
    }