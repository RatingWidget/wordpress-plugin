<?php
/*
 * The content of the Add Ons page.
 * Called from addons_settings_page_render method in rating-widget.php
 */

$addons = ratingwidget()->get_addons();
?>
<div class="wrap rw-dir-ltr">
	<h2 class="entry-title"><?php _e('Add Ons for RatingWidget', WP_RW__ID); ?></h2>
	<form id="rw-addons-page" method="post" action="">
		<div id="poststuff">
			<div class="postbox rw-body">
				<div class="inside rw-ui-content-container rw-no-radius">
					<div class="entry-content">
						<ul class="rw-addon-grid clearfix">
							<?php
							foreach ( $addons as $idx => $addon ) {
								$pricing = $addon['pricing'][0];
								$price = $pricing['monthly_price'];
								$is_free = (NULL === $price);
								?>
								<li class="rw-addon<?php echo $is_free ? ' free' : ''; ?>" data-idx="<?php echo $idx; ?>">
									<div class="rw-addon-inner">
										<a href="#" class="rw-addon-overlay"></a>
										<div class="rw-addon-content-wrapper">
											<div class="rw-addon-content" style="background-image: url('<?php echo $addon['thumbnail_url']; ?>')">
												<ul>
													<li class="rw-addon-banner"></li>
													<li class="rw-addon-title">
														<?php echo $addon['title']; ?>
													</li>
													<span>
														<li class="rw-addon-price">
														  <?php echo $price; ?> <span class="price-per">/ month</span>
														</li>
													</span>
													<li class="rw-addon-rating">
														<span class="rw-addon-rating-reviews">
															<span class="purchase-text"><?php echo $is_free ? __('Free', WP_RW__ID) : __('Purchase', WP_RW__ID); ?></span>
															<input type="submit" class="button button-primary" value="<?php _e('Purchase', WP_RW__ID); ?>"/>
														</span>
														<?php
														// Calculate the number of half stars needed for the star-rating display.
														$avg_rate = $addon['avg_rate'];
														$rating = intval($avg_rate);

														$avg_rate = round($avg_rate, 0);

														$half_stars = $rating * 2;
														
														if ($rating < $avg_rate) {
															// Add one half-star
															$half_stars++;
														}
														?>
														<span class="appcard-rating-star-halves appcard-rating-star-halves-<?php echo $half_stars; ?>"></span>
													</li>
													<li class="rw-addon-description">
														<?php echo $addon['description']; ?>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div id="rw-addons-popup-dialog">
			<p><?php _e("The add-on is still not ready for final use, would you like us to let you know when it's ready? Or just anonymously tell us it's interesting?", WP_RW__ID); ?></p>
		</div>
		<?php
			global $wp_version;
		?>
		<input type="hidden" id="wp-version" value="<?php echo $wp_version; ?>";
	</form>
	<script>
		(function($) {
			$(document).ready(function() {
				var wpVersion = parseFloat($("#wp-version").val(), 10);
				
				var $popupDialog = $("#rw-addons-popup-dialog");
				var dialogOptions = {
					'title'			: '<?php _e('Oops... ', WP_RW__ID); ?>',
					'dialogClass'   : wpVersion > 3 ? 'wp-dialog' : 'wp-custom-dialog',           
					'modal'         : true,
					'autoOpen'      : false, 
					'closeOnEscape' : true,      
					'width'			: 430,
					'buttons'       : { // Add button labels in this way so that it will work with WordPress 3.0 and below
							'<?php _e('Yes - add me to the waiting list', WP_RW__ID); ?>': function() {
								sendData("Purchase Click", {add_user: true});
								$popupDialog.dialog('close');
							},
							'<?php _e("It\'s just interesting", WP_RW__ID); ?>': function() {
								sendData("Purchase Click");
								$popupDialog.dialog('close');
							}
						}
				};
				
				$popupDialog.dialog(dialogOptions);
				
				if ( wpVersion <= 3 ) {
					applyCustomDialogStyles(dialogOptions);
				}
				
				// Not a very nice way of adding classes, but works with all versions of WordPress.
				var $buttons = $('.ui-dialog-buttonset').children();
				$buttons.eq(0).addClass('button-primary');
				$buttons.eq(1).addClass('button-secondary');
				
				$('.rw-addon-grid li.rw-addon').click(function(evt) {
					if ( $('li.rw-addon.active').length ) {
						$('li.rw-addon.active').toggleClass('active');
					}
					
					var $card = $(this);
					$card.addClass('active');
					
					var $target = $(evt.target);
					var elementClass = $target.attr("class");

					var addonAction = ('rw-addon-overlay' === elementClass ? 'Click' : 'Purchase Click');
					if ( 'Purchase Click' === addonAction ) {
						evt.preventDefault();
						$popupDialog.dialog('open');
					} else {
						sendData(addonAction);
					}

					return false;
				});
			});
			
			/**
			 * Manual rendering of some parts of the dialog in WordPress 3.0 and below.
			 * @param {Object} dialogOptions
			 */
			function applyCustomDialogStyles(dialogOptions) {
				$('body').addClass('has-custom-dialog');
				
				var dialog = $('#rw-addons-popup-dialog').parent();
				dialog.find('.ui-dialog-buttonpane').children().wrapAll('<div class="ui-dialog-buttonset" />');
				
				var $buttonSet = dialog.find('.ui-dialog-buttonset');
				for (var idx in dialogOptions.buttons) {
					if ( !dialogOptions.buttons.hasOwnProperty(idx)) {
						continue;
					}
					
					var $button = $buttonSet.children().eq(idx);
					var buttonOption = dialogOptions.buttons[idx];
					
					$button.addClass(buttonOption.class);
					$button.html(buttonOption.text);
				}
			}
			
			/**
			 * Sends the add-on information to the server for further processing.
			 */
			function sendData(addonAction, extraDetails) {
				var data = {
					action: 'rw-addon-request',
					_n: '<?php echo wp_create_nonce('rw_send_addon_request'); ?>',
					addon_key: $('li.rw-addon.active').attr('data-idx'),
					addon_action: addonAction
				};

				if ( extraDetails ) {
					$.extend(data, extraDetails);
				}

				var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
				$.ajax({
					url: ajaxUrl,
					data: data
				});
			}
		})(jQuery);
	</script>
</div>
<?php fs_require_template('powered-by.php') ?>