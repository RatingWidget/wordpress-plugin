<?php
	$class = rtrim(rw_settings_rating_type(), 's');
	$has_multi_rating = in_array($class, array('blog-post', 'front-post', 'page', 'product'));
	
	$is_multicriteria = false;
	
	if ($has_multi_rating) {
		// Enqueue live preview JS
		rw_enqueue_script('rw-js-live-preview', WP_RW__PLUGIN_URL . '/resources/js/live-preview.js');
	
		$multirating_options = ratingwidget()->multirating_settings_list->{$class};
		$is_multicriteria = count($multirating_options->criteria) > 1;
	}
	
    $options = rw_options();
?>
<div id="rw_wp_preview" class="postbox rw-body<?php echo $is_multicriteria ? ' multi-rating' : ''; ?>">
    <table cellpadding="0" cellspacing="0" style="float: right;height: 45px;">
        <tr>
            <td style="vertical-align: middle;">
                <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Frating.widget&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=false&amp;height=21&amp;appId=1423642847870677" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px; width: 100px;" allowTransparency="true"></iframe>
            </td>
            <td style="vertical-align: middle;">
                <a href="https://twitter.com/ratingwidget" data-show-screen-name="false" class="twitter-follow-button"><?php _e('Follow', WP_RW__ID) ?></a>
                <script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
            </td>
            <td style="vertical-align: middle;">
<!-- Place this tag where you want the +1 button to render. -->
<div class="g-plusone" data-size="medium" data-href="http://rating-widget.com"></div>

<!-- Place this tag after the last +1 button tag. -->
<script type="text/javascript">
(function() {
var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
po.src = 'https://apis.google.com/js/platform.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script>
            </td>
        </tr>
    </table>
	<h3>Live Preview</h3>
	
    <div class="inside" style="padding: 10px;">
		<div id="rw-preview-scrollable">
			<div id="rw_preview_container" style="text-align: <?php
				if ($options->advanced->layout->align->ver != "middle")
				{
					echo "center";
				}
				else
				{
					if ($options->advanced->layout->align->hor == "right"){
						echo "left";
					}else{
						echo "right";
					}
				}
			?>;">
				<?php
				if ($has_multi_rating) { ?>
					<div id="base-rating" style="display: none;">
						<div class="rw-ui-container rw-urid-3" data-sync="false"></div>
						<div class="rw-ui-container rw-ui-nero rw-urid-17" data-sync="false"></div>
					</div>
					<table id="rw_preview_star" class="rw-preview" data-type="star">
						<?php
						foreach ($multirating_options->criteria as $criteria_id => $criteria) {
						?>
							<tr class="rw-rating" data-rid="<?php echo $criteria_id; ?>">
								<td>
									<span class="rw-add-label"><a href="#" data-placeholder="<?php _e('Add Label', WP_RW__ID); ?>" class="<?php echo __('Add Label', WP_RW__ID) != $criteria['label'] ? 'has-custom-value' : ''; ?>"><?php echo $criteria['label']; ?></a></span>
								</td>
								<td class="rw-rating-type">
									<div class="rw-preview-star rw-ui-container rw-urid-preview-<?php echo $criteria_id; ?>0" data-sync="false"></div>
								</td>
								<td class="rw-action">
									<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
								</td>
								<input type="hidden" name="multi_rating[criteria][<?php echo $criteria_id; ?>][label]" value="<?php echo $criteria['label']; ?>" />
							</tr>
						<?php
						}
						?>
						<tr class="rw-add-rating-container">
							<td colspan="3">
								<div class="rw-dash">
									<a class="rw-add-rating" href="#"><?php _e('[+] Add Rating / Criteria', WP_RW__ID); ?></a>
								</div>
								<div class="summary-rating-option">
									<label><input type="checkbox" class="show-summary-rating" name="multi_rating[show_summary_rating]" <?php checked(true, $multirating_options->show_summary_rating); ?>/> <?php _e('Show Summary Rating', WP_RW__ID); ?></label>
								</div>
							</td>
						</tr>
						<tr class="rw-summary-rating" data-rid="1" style="<?php echo $multirating_options->show_summary_rating ? '' : 'display: none'; ?>">
							<td>
								<span class="rw-summary-label"><a href="#"><?php _e('Summary', WP_RW__ID); ?></a></span>
							</td>
							<td colspan="2">
								<div class="rw-ui-container rw-urid-preview-summary-10" data-sync="false"></div>
							</td>
						</tr>
						<tr class="rw-template-rating" data-rid="0">
							<td>
								<span class="rw-add-label"><a href="#" data-placeholder="<?php _e('Add Label'); ?>"><?php _e('Add Label', WP_RW__ID); ?></a></span>
							</td>
							<td class="rw-rating-type">
								<div data-sync="false"></div>
							</td>
							<td class="rw-action">
								<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
							</td>
							<input type="hidden" class="multi-rating-label" />
						</tr>
					</table>
					<table id="rw_preview_nero" class="rw-preview" data-type="nero" style="display: none;">
						<?php
						foreach ($multirating_options->criteria as $criteria_id => $criteria) {
						?>
							<tr class="rw-rating" data-rid="<?php echo $criteria_id; ?>">
								<td>
									<span class="rw-add-label"><a href="#" data-placeholder="<?php _e('Add Label'); ?>" class="<?php echo __('Add Label', WP_RW__ID) != $criteria['label'] ? 'has-custom-value' : ''; ?>"><?php echo $criteria['label']; ?></a></span>
								</td>
								<td class="rw-rating-type">
									<div class="rw-preview-star rw-ui-container rw-urid-preview-<?php echo $criteria_id; ?>1" data-sync="false"></div>
								</td>
								<td class="rw-action">
									<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
								</td>
								<input type="hidden" name="multi_rating[criteria][<?php echo $criteria_id; ?>][label]" value="<?php echo $criteria['label']; ?>" />
							</tr>
						<?php
						}
						?>
						<tr class="rw-add-rating-container">
							<td colspan="3">
								<div class="rw-dash">
									<a class="rw-add-rating" href="#"><?php _e('[+] Add Rating / Criteria', WP_RW__ID); ?></a>
								</div>
								<div class="summary-rating-option">
									<label><input type="checkbox" class="show-summary-rating" name="multi_rating[show_summary_rating]" <?php checked(true, $multirating_options->show_summary_rating); ?>/> <?php _e('Show Summary Rating', WP_RW__ID); ?></label>
								</div>
							</td>
						</tr>
						<tr class="rw-summary-rating" data-rid="1" style="<?php echo $multirating_options->show_summary_rating ? '' : 'display: none'; ?>">
							<td>
								<span class="rw-summary-label"><a href="#"><?php _e('Summary', WP_RW__ID); ?></a></span>
							</td>
							<td colspan="2">
								<div class="rw-ui-container rw-urid-preview-summary-11" data-sync="false"></div>
							</td>
						</tr>
						<tr class="rw-template-rating" data-rid="0">
							<td>
								<span class="rw-add-label"><a href="#" data-placeholder="<?php _e('Add Label'); ?>"><?php _e('Add Label', WP_RW__ID); ?></a></span>
							</td>
							<td class="rw-rating-type">
								<div data-sync="false"></div>
							</td>
							<td class="rw-action">
								<span class="rw-remove"><a href="#" class="rw-remove-button"></a></span>
							</td>
							<input type="hidden" class="multi-rating-label" />
						</tr>
					</table>
				<?php
				} else { ?>
					<div id="rw_preview_star" class="rw-ui-container rw-urid-3" data-sync="false"></div>
					<div id="rw_preview_nero" class="rw-ui-container rw-ui-nero rw-urid-17" data-sync="false" style="display: none;"></div>
				<?php
				}
				?>
			</div>

			<?php
			if ($has_multi_rating) { ?>
				<h3><?php _e('Multi-Rating Options', WP_RW__ID); ?></h3>
				<div id="multi-rating-options">
					<div>
						<label><input type="checkbox" class="hide_info_bubble" name="multi_rating[hide_info_bubble]" <?php checked(false, $multirating_options->show_info); ?>> <?php _e('Hide Info Bubble', WP_RW__ID); ?></label>
					</div>
					<div>
						<label><input type="checkbox" class="author_rating_readonly" name="multi_rating[author_rating_readonly]" <?php checked(true, $multirating_options->readonly); ?>> <?php _e('Author Rating (readOnly for visitors)', WP_RW__ID); ?></label>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		
        <div class="rw-js-container">
            <script type="text/javascript">
                var rwStar, rwNero;
                
				function getOptions() {
					return <?php echo json_encode($options); ?>; 
				}
				
                // Initialize ratings.
                function RW_Async_Init() {
                    RW.init("cfcd208495d565ef66e7dff9f98764da");
					
                    <?php
                        $b_type = $options->type;
                        $b_theme = $options->theme;
                        $b_style = $options->style;
						
						if ($is_multicriteria) {
							$options->readOnly = $multirating_options->readonly;
							$options->showInfo = $multirating_options->show_info;
						}
                        
                        $types = array("star", "nero");
                        $default_themes = array("star" => DEF_STAR_THEME, "nero" => DEF_NERO_THEME);
                        $ratings_uids = array(
							3 => 'star',
							17 => 'nero',
							'preview-summary-10' => 'star',
							'preview-summary-11' => 'nero'
							);
                        foreach($ratings_uids as $rating_uid => $type)
                        {
                    ?>
                    RW.initRating(<?php
                        if ($options->type !== $type)
                        {
                            $options->type = $type;
                            $options->theme = $default_themes[$type];
                            $options->style = "";
                        }
                        
                        echo "'" . $rating_uid . "', ";
                        echo json_encode($options);
                        
                        // Recover.
                        $options->type = $b_type;
                        $options->theme = $b_theme;
                        $options->style = $b_style;                        
                    ?>);
                    <?php
                        }
						
						if ($has_multi_rating) {
							foreach ($multirating_options->criteria as $criteria_id => $criteria) {
								for ($count = 0; $count <= 1; $count++) {
									$type = (0 == $count ? 'star' : 'nero');
								?>
								RW.initRating(<?php
									if ($options->type !== $type)
									{
										$options->type = $type;
										$options->theme = $default_themes[$type];
										$options->style = "";
									}

									echo "'preview-" . $criteria_id . $count . "', ";
									echo json_encode($options);

									// Recover.
									$options->type = $b_type;
									$options->theme = $b_theme;
									$options->style = $b_style;                        
								?>);
								<?php
								}
							}
						}
                    ?>
                    RW.render(function(ratings){
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
                        for (var t in RWT)
                        {
                            if (RWT[t].options.style == RW.STYLE.CUSTOM){
                                RW._addCustomImgStyle(RWT[t].options.imgUrl.large, [RWT[t].options.type], "theme", t);
                            }
                        }

                        RWM.Code.refresh();
                    }, false);
                }

                // Append RW JS lib.
                if (typeof(RW) == "undefined"){ 
                    (function(){
                        var rw = document.createElement("script"); rw.type = "text/javascript"; rw.async = true;
                        rw.src = "<?php echo rw_get_js_url('external.php');?>?wp=<?php echo WP_RW__VERSION;?>";
                        var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(rw, s);
                    })();
                }
            </script>
        </div>
        <div class="submit" style="margin-top: 10px; padding: 0;">
            <input type="hidden" name="<?php echo rw_settings()->form_hidden_field_name; ?>" value="Y">
            <input type="hidden" id="rw_options_hidden" name="rw_options" value="" />

            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes', WP_RW__ID) ?>" />
            <?php if (!rw_fs()->is_paying()) : ?>
            <a href="<?php echo rw_fs()->get_upgrade_url() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button-secondary gradient rw-upgrade-button" style="float: right;"><?php _e('Upgrade Now!', WP_RW__ID) ?></a>
            <?php endif; ?>
            <span style="margin: 0 10px; font-size: 1em; float: right; line-height: 30px;"><b style="font-size: 24px;vertical-align: top;color: #999;">&#9829;</b> <?php _e('Like it?', WP_RW__ID) ?>  <a href="http://wordpress.org/support/view/plugin-reviews/rating-widget?rate=5#postform" target="_blank" style="
    font-weight: bold;
"><?php _e('Support the plugin with ★ 5 Stars', WP_RW__ID) ?></a></span>
        </div>
    </div>
</div>
