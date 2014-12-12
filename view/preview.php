<?php
    $options = rw_options();
?>
<div id="rw_wp_preview" class="postbox rw-body">
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
            <div id="rw_preview_star" class="rw-ui-container rw-urid-3" data-sync="false"></div>
            <div id="rw_preview_nero" class="rw-ui-container rw-ui-nero rw-urid-17" data-sync="false" style="display: none;"></div>
        </div>
        <div class="rw-js-container">
            <script type="text/javascript">
                var rwStar, rwNero;
                
                // Initialize ratings.
                function RW_Async_Init(){
					var options = {}, render_count = 0;
					<?php
					// Get the type of settings to retrieve.
					$pClass = trim(rw_settings_rating_type(), 's');

					// Get the custom settings of this type.
					$custom_settings = ratingwidget()->GetCustomSettings($pClass);
					echo $custom_settings."\n";
					?>
							
					// Specifiy the power settings so that we can exclude them later.
					jQuery('#rw_options_hidden_custom').val(encodeURIComponent(RW.JSON.stringify(options)));
				
                    RW.init("cfcd208495d565ef66e7dff9f98764da");
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
                    RW.render(function(ratings) {
                        rwStar = RWM.STAR = ratings[3].getInstances(0);
                        rwNero = RWM.NERO = ratings[17].getInstances(0);
						
						if (++render_count == 2) {
							jQuery.extend(rwNero.options, options);
							jQuery.extend(rwStar.options, options);
						}
                        
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
            <input type="hidden" id="rw_options_hidden_custom" name="rw_options_custom" value="" />

            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes', WP_RW__ID) ?>" />
            <?php if (!ratingwidget()->RW_IsPaying()) : ?>
            <a href="<?php echo ratingwidget()->GetUpgradeUrl() ?>" onclick="_gaq.push(['_trackEvent', 'upgrade', 'wordpress', 'gopro_button', 1, true]); _gaq.push(['_link', this.href]); return false;" class="button-secondary gradient rw-upgrade-button" style="float: right;" target="_blank"><?php _e('Upgrade Now!', WP_RW__ID) ?></a>
            <?php endif; ?>
            <span style="margin: 0 10px; font-size: 1em; float: right; line-height: 30px;"><b style="font-size: 24px;vertical-align: top;color: #999;">&#9829;</b> <?php _e('Like it?', WP_RW__ID) ?>  <a href="http://wordpress.org/support/view/plugin-reviews/rating-widget?rate=5#postform" target="_blank" style="
    font-weight: bold;
"><?php _e('Support the plugin with ★ 5 Stars', WP_RW__ID) ?></a></span>
        </div>
    </div>
</div>
