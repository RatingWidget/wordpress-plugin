<?php
	/**
	 * @var Freemius           $rw_fs
	 * @var RatingWidgetPlugin $rwp
	 */
	global $rw_fs, $rwp;

	$rclass           = rtrim( rw_settings_rating_type(), 's' );
	$has_multi_rating = ratingwidget()->has_multirating_options( $rclass );
	$multi_criterion  = false;

	if ( $has_multi_rating ) {
		$multirating_options = ratingwidget()->get_multirating_options_by_class( $rclass );

		// Check if there are more than one criteria so that we can hide or show additional options
		$total_criteria  = count( $multirating_options->criteria );
		$multi_criterion = ( $total_criteria > 1 );

		if ( ! rw_fs()->is_plan_or_trial__premium_only( 'professional' ) ) {
			if ( $total_criteria > 3 ) {
				$multirating_options->criteria = array_splice( $multirating_options->criteria, 0, 3 );
			}
		}
	}

	$options = rw_options();

	$default_hide_recommendations = isset( $options->hideRecommendations ) ? $options->hideRecommendations : false;
	$urid_summary_star            = 1;
	$urid_summary_nero            = 2;

	$add_label_str = __rw( 'add-label' );
	$add_criteria_str = __rw( 'add-criteria-rating' );
?>
<div id="rw_wp_preview" class="postbox rw-body<?php echo $multi_criterion ? ' multi-rating' : '';
	echo ' rw-' . $options->advanced->layout->dir; ?>">
<table cellpadding="0" cellspacing="0" style="float: right;height: 45px;">
	<tr>
		<td style="vertical-align: middle;">
			<iframe
				src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Frating.widget&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=false&amp;height=21&amp;appId=1423642847870677"
				scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px; width: 100px;"
				allowTransparency="true"></iframe>
		</td>
		<td style="vertical-align: middle;">
			<a href="https://twitter.com/ratingwidget" data-show-screen-name="false"
			   class="twitter-follow-button"><?php _erw( 'follow' ) ?></a>
			<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
		</td>
		<td style="vertical-align: middle;">
			<!-- Place this tag where you want the +1 button to render. -->
			<div class="g-plusone" data-size="medium" data-href="http://rating-widget.com"></div>

			<!-- Place this tag after the last +1 button tag. -->
			<script type="text/javascript">
				(function () {
					var po = document.createElement('script');
					po.type = 'text/javascript';
					po.async = true;
					po.src = 'https://apis.google.com/js/platform.js';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(po, s);
				})();
			</script>
		</td>
	</tr>
</table>
<h3>Live Preview</h3>

<div class="inside" style="padding: 10px;">
<div id="rw-preview-scrollable">
	<div id="rw_preview_container" style="text-align: <?php
		if ( $options->advanced->layout->align->ver != "middle" ) {
			echo "center";
		} else {
			if ( $options->advanced->layout->align->hor == "right" ) {
				echo "left";
			} else {
				echo "right";
			}
		}
	?>;">
		<?php
			if ( $has_multi_rating ) {
				?>
				<!--
					The base rating widgets whose options are used as the basis for initializing the
					criteria widgets' options.
				-->
				<div id="base-rating" style="display: none;">
					<div class="rw-ui-container rw-ui-star rw-urid-3" data-sync="false"></div>
					<div class="rw-ui-container rw-ui-nero rw-urid-17" data-sync="false"></div>
				</div>
				<table class="rw-preview rw-preview-<?php echo $options->type; ?>" data-rclass="<?php echo $rclass; ?>">
					<?php
						$criterion_id = 1;
						foreach ( $multirating_options->criteria as $criterion ) {
							$urid_star = $urid_summary_star . ( $multi_criterion ? '-' . $criterion_id : '' );
							$urid_nero = $urid_summary_nero . ( $multi_criterion ? '-' . $criterion_id : '' );

							$criteria_rclass = $rclass;
							if ( $multi_criterion ) {
								$criteria_rclass .= '-criteria-' . $criterion_id;
							}

							$criterion_id ++;
							?>
							<tr class="rw-rating">
								<td>
									<span class="rw-add-label"><a href="#"
									                              data-placeholder="<?php echo $add_label_str ?>"
									                              class="<?php echo ( isset( $criterion['label'] ) && $criterion['label'] != $add_label_str ) ? 'has-custom-value' : ''; ?>">
											<nobr><?php echo( isset( $criterion['label'] ) ? $criterion['label'] : $add_label_str ); ?></nobr>
										</a></span>
								</td>
								<td class="rw-rating-type">
									<div
										class="rw-ui-container rw-class-<?php echo $criteria_rclass; ?> rw-ui-star" <?php echo $multi_criterion ? "data-uarid=\"$urid_summary_star\"" : '';
										echo ( $multi_criterion || $default_hide_recommendations ) ? ' data-hide-recommendations="true"' : ''; ?>
										data-urid="<?php echo $urid_star; ?>"></div>
									<div
										class="rw-ui-container rw-class-<?php echo $criteria_rclass; ?> rw-ui-nero" <?php echo $multi_criterion ? "data-uarid=\"$urid_summary_nero\"" : '';
										echo ( $multi_criterion || $default_hide_recommendations ) ? ' data-hide-recommendations="true"' : ''; ?>
										data-urid="<?php echo $urid_nero; ?>"></div>
								</td>
								<td class="rw-action">
									<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
								</td>
								<input type="hidden" class="multi-rating-label" name="multi_rating[criteria][][label]"
								       value="<?php echo( isset( $criterion['label'] ) ? $criterion['label'] : '' ); ?>"/>
							</tr>
						<?php
						}
					?>
					<tr class="rw-add-rating-container">
						<td colspan="3">
							<div class="rw-dash">
								<?php
									$upgrade_label_text = __rw( 'upgrade_criteria-limit' );
									if ( $total_criteria >= 3 && ! rw_fs()->is_plan_or_trial( 'professional' ) ) {
										?>
										<a class="rw-add-rating upgrade" href="<?php echo $rw_fs->get_upgrade_url(); ?>"
										   data-upgrade-href="<?php echo $rw_fs->get_upgrade_url(); ?>"
										   data-upgrade-text="[+] <?php echo $upgrade_label_text ?>"
										   data-default-text="[+] <?php echo $add_criteria_str ?>">[+] <?php echo $upgrade_label_text ?></a>
									<?php
									} else {
										?>
										<a class="rw-add-rating" href="#"
										   data-upgrade-href="<?php echo $rw_fs->get_upgrade_url(); ?>"
										   data-upgrade-text="[+] <?php echo $upgrade_label_text ?>"
										   data-default-text="[+] <?php echo $add_criteria_str ?>">[+] <?php echo $add_criteria_str ?></a>
									<?php
									}
								?>
							</div>
							<div class="summary-rating-option">
								<label><input type="checkbox" class="show-summary-rating"
								              name="multi_rating[show_summary_rating]" <?php checked( true, $multirating_options->show_summary_rating ); ?>/> <?php _erw( 'show-summary-rating' ) ?>
								</label>
							</div>
						</td>
					</tr>
					<tr class="rw-summary-rating"
					    style="<?php echo $multirating_options->show_summary_rating ? '' : 'display: none'; ?>">
						<?php $summary_label = ( isset( $multirating_options->summary_label ) && $multirating_options->summary_label != $add_label_str ) ? $multirating_options->summary_label : '' ?>
						<td>
							<span class="rw-add-label rw-summary-label"><a href="#"
							                                               data-placeholder="<?php echo $add_label_str ?>"
							                                               class="<?php echo ! empty( $summary_label ) ? 'has-custom-value' : ''; ?>">
									<nobr><?php echo( ! empty( $summary_label ) ? $summary_label : $add_label_str ) ?></nobr>
								</a></span>
						</td>
						<td colspan="2">
							<?php
								// Create the summary rating for multi-criterion case only
								if ( $multi_criterion ) {
									?>
									<div
										class="rw-ui-container rw-class-<?php echo $rclass; ?> rw-ui-star"<?php echo $default_hide_recommendations ? ' data-hide-recommendations="true"' : ''; ?>
										data-urid="<?php echo $urid_summary_star; ?>" data-read-only="true"></div>
									<div
										class="rw-ui-container rw-class-<?php echo $rclass; ?> rw-ui-nero"<?php echo $default_hide_recommendations ? ' data-hide-recommendations="true"' : ''; ?>
										data-urid="<?php echo $urid_summary_nero; ?>" data-read-only="true"></div>
								<?php
								} else {
									?>
									<div
										class="rw-ui-star"<?php echo $default_hide_recommendations ? ' data-hide-recommendations="true" ' : ''; ?>
										data-read-only="true"></div>
									<div
										class="rw-ui-nero"<?php echo $default_hide_recommendations ? ' data-hide-recommendations="true" ' : ''; ?>
										data-read-only="true"></div>
								<?php
								}
							?>
						</td>
						<input type="hidden" class="multi-rating-label" name="multi_rating[summary_label]"
						       value="<?php echo $summary_label; ?>"/>
					</tr>
					<tr class="rw-template-rating" data-cid="0">
						<td>
							<span class="rw-add-label"><a href="#"
							                              data-placeholder="<?php echo $add_label_str ?>">
									<nobr><?php echo $add_label_str ?></nobr>
								</a></span>
						</td>
						<td class="rw-rating-type">
							<div class="rw-ui-star" data-hide-recommendations="true"></div>
							<div class="rw-ui-nero" data-hide-recommendations="true"></div>
						</td>
						<td class="rw-action">
							<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
						</td>
						<input type="hidden" class="multi-rating-label"/>
					</tr>
				</table>
			<?php
			} else {
				?>
				<div id="rw_preview_star" class="rw-ui-container rw-urid-3" data-sync="false"></div>
				<div id="rw_preview_nero" class="rw-ui-container rw-ui-nero rw-urid-17" data-sync="false"
				     style="display: none;"></div>
			<?php
			}
		?>
	</div>

	<?php
		if ( $has_multi_rating ) {
			?>
			<h3><?php _erw( 'multi-rating-options' ); ?></h3>
			<div id="multi-rating-options">
				<div>
					<label><input type="checkbox"
					              class="hide-info-bubble" <?php checked( false, $options->showInfo ) ?>> <?php _erw( 'multi-rating-options_hide-info' ) ?>
					</label>
				</div>
				<div>
					<label><input type="checkbox"
					              class="author-rating-readonly" <?php checked( true, $options->readOnly ) ?>> <?php _erw( 'multi-rating-options_author-rating' ) ?>
					</label>
				</div>
			</div>
		<?php
		}
	?>
</div>

<div class="rw-js-container">
	<script type="text/javascript">
		var rwStar, rwNero;

		function getSummaryUrid(type) {
			if (type == RW.TYPE.STAR) {
				return '<?php echo $urid_summary_star; ?>';
			} else {
				return '<?php echo $urid_summary_nero; ?>';
			}
		}

		// Initialize ratings.
		function RW_Async_Init() {
			RW.init('<?php echo rw_account()->site_public_key ?>');
			<?php
				$b_type = $options->type;
				$b_theme = $options->theme;
				$b_style = $options->style;

				$types = array("star", "nero");
				$default_themes = array("star" => DEF_STAR_THEME, "nero" => DEF_NERO_THEME);
				$ratings_uids = array("star" => 3, "nero" => 17);
				foreach($types as $type)
				{
			?>
			RW.initRating(<?php
                        if ($options->type !== $type)
                        {
                            $options->type = $type;
                            $options->theme = $default_themes[$type];
                            $options->style = "";
                        }
                        
                        echo $ratings_uids[$type] . ", ";
                        echo json_encode($options);
                        
                        // Recover.
                        $options->type = $b_type;
                        $options->theme = $b_theme;
                        $options->style = $b_style;                        
                    ?>);
			<?php
			}
			?>
			RW.render(function (ratings) {
				rwStar = RWM.STAR = ratings[3].getInstances(0);
				rwNero = RWM.NERO = ratings[17].getInstances(0);

				jQuery("#rw_theme_loader").hide();
				jQuery("#rw_<?php echo $options->type;?>_theme_select").show();

				RWM.Set.sizeIcons(RW.TYPE.<?php echo strtoupper($options->type);?>);

				<?php
					if ($options->type == "star"){
						echo 'jQuery("#rw_preview_nero").hide();';
						echo 'jQuery("#rw_preview_star").show();';
					}else{
						echo 'jQuery("#rw_preview_star").hide();';
						echo 'jQuery("#rw_preview_nero").show();';
					}
				?>

				// Set selected themes.
				RWM.Set.selectedTheme.star = "<?php
                            echo (isset($options->type) && 
                                  $options->type == "star" && 
                                  isset($options->theme) && 
                                  $options->theme !== "") ? $options->theme : DEF_STAR_THEME;
                        ?>";
				RWM.Set.selectedTheme.nero = "<?php
                            echo (isset($options->type) &&
                                  $options->type == "nero" &&
                                  isset($options->theme) && 
                                  $options->theme !== "") ? $options->theme : DEF_NERO_THEME;
                        ?>";

				RWM.Set.selectedType = RW.TYPE.<?php echo strtoupper($options->type);?>;

				// Add all themes inline css.
				for (var t in RWT) {
					if (RWT[t].options.style == RW.STYLE.CUSTOM) {
						RW._addCustomImgStyle(RWT[t].options.imgUrl.large, [RWT[t].options.type], "theme", t);
					}
				}

				RWM.Code.refresh();
			}, false);
		}

		// Append RW JS lib.
		if (typeof(RW) == "undefined") {
			(function () {
				var rw = document.createElement("script");
				rw.type = "text/javascript";
				rw.async = true;
				rw.src = "<?php echo rw_get_js_url('external.php');?>?wp=<?php echo WP_RW__VERSION;?>";
				var s = document.getElementsByTagName("script")[0];
				s.parentNode.insertBefore(rw, s);
			})();
		}
	</script>
</div>
<div class="submit" style="margin-top: 10px; padding: 0;">
	<input type="hidden" name="<?php echo rw_settings()->form_hidden_field_name; ?>" value="Y">
	<input type="hidden" id="rw_options_hidden" name="rw_options" value=""/>

	<input type="submit" name="Submit" class="button-primary" value="<?php echo esc_attr( __rw( 'save-changes' ) ) ?>"/>
	<?php if ( $rw_fs->is_not_paying() ) : ?>
		<a href="<?php echo $rw_fs->get_upgrade_url() ?>"
		   onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;"
		   class="button-secondary gradient rw-upgrade-button"
		   style="float: right;"><?php _erw( 'upgrade-now' ) ?></a>
	<?php endif; ?>
	<span style="margin: 0 10px; font-size: 1em; float: right; line-height: 30px;"><b
			style="font-size: 24px;vertical-align: top;color: #999;">&#9829;</b> <?php _erw( 'preview-rate-ask' ) ?>
		<a href="http://wordpress.org/support/view/plugin-reviews/rating-widget?rate=5#postform" target="_blank" style="
    font-weight: bold;
"><?php _erw( 'preview-rate-ask_title' ) ?></a></span>
</div>
</div>
</div>
