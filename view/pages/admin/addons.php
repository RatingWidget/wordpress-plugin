<?php
	/*
	 * The content of the Add Ons page.
	 * Called from addons_settings_page_render method in rating-widget.php
	 */

	$addons = ratingwidget()->get_addons();

	$admin_notice_classes = 'addons-page-notice update-nag';

	global $wp_version;

// Use additional class for the different versions of WordPress
// in order to have the correct message styles.
	if ( $wp_version < 3 ) {
		$admin_notice_classes .= ' updated';
	} else if ( $wp_version >= 3.8 ) {
		$admin_notice_classes .= ' success';
	}

	$message = __rw( 'addons_thanks-subscribing-mailing-list' );
	ratingwidget()->Notice( $message, $admin_notice_classes );
?>
	<div class="wrap rw-dir-ltr">
		<h2 class="entry-title"><?php _erw( 'addons_title' ) ?></h2>

		<form id="rw-addons-page" method="post" action="">
			<div id="poststuff">
				<div class="postbox rw-body">
					<div class="inside rw-ui-content-container rw-no-radius">
						<div class="entry-content">
							<ul class="rw-addon-grid clearfix">
								<?php
									foreach ( $addons as $idx => $addon ) {
										$pricing = $addon['pricing'][0];
										$price   = $pricing['annual_price'];
										$is_free = ( null === $price );
										?>
										<li class="rw-addon<?php echo $is_free ? ' free' : ''; ?>"
										    data-idx="<?php echo $idx; ?>">
											<div class="rw-addon-inner">
												<a href="#" class="rw-addon-overlay"></a>

												<div class="rw-addon-content-wrapper">
													<div class="rw-addon-content"
													     style="background-image: url('<?php echo $addon['thumbnail_url']; ?>')">
														<ul>
															<li class="rw-addon-banner"></li>
															<li class="rw-addon-title">
																<?php echo $addon['title']; ?>
															</li>
													<span>
														<li class="rw-addon-price">
															<?php echo $price; ?> <span class="price-per">/ year</span>
														</li>
													</span>
															<li class="rw-addon-rating">
														<span class="rw-addon-rating-reviews">
															<span
																class="purchase-text"><?php echo $is_free ? __rw( 'free' ) : __rw( 'purchase' ); ?></span>
															<input type="submit" class="button button-primary"
															       value="<?php _erw( 'purchase' ); ?>"/>
														</span>
																<?php
																	// Calculate the number of half stars needed for the star-rating display.
																	$avg_rate = $addon['avg_rate'];
																	$rating   = intval( $avg_rate );

																	$avg_rate = round( $avg_rate, 0 );

																	$score = $rating * 10;

																	if ( $rating < $avg_rate ) {
																		// Add one half-star
																		$score += 5;
																	}
																?>
																<span
																	class="appcard-rating-star appcard-rating-<?php echo $score; ?>"><span></span></span>
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
				<p><?php _erw( 'addons_not-ready' ) ?></p>
				<input type="hidden" class="addon-action" value=""/>
			</div>
			<input type="hidden" id="wp-version" value="<?php echo $wp_version; ?>"
		</form>
		<script>
			(function ($) {
				$(document).ready(function () {
					var wpVersion = parseFloat($("#wp-version").val(), 10);

					var $popupDialog = $("#rw-addons-popup-dialog");

					var dialogOptions = {
						title        : '<?php _erw( 'oops' ) ?>...',
						dialogClass  : wpVersion > 3 ? 'wp-dialog' : 'wp-custom-dialog',
						modal        : true,
						autoOpen     : false,
						closeOnEscape: true,
						width        : 430,
						buttons      : {
							// Add button labels in this way so that it will work with WordPress 3.0 and below
							<?php echo json_encode( __rw( 'addons_add-to-waiting-list' ) ) ?>: function () {
								$('.addons-page-notice').show();
								sendData({addon_action: $popupDialog.find('.addon-action').val(), add_user: true});
								$popupDialog.dialog('close');
							},
							<?php echo json_encode( __rw( 'addons_silent-report' ) ) ?>: function () {
								sendData({addon_action: $popupDialog.find('.addon-action').val()});
								$popupDialog.dialog('close');
							}
						}
					};

					$popupDialog.dialog(dialogOptions);

					if (wpVersion <= 3) {
						applyCustomDialogStyles(dialogOptions);
					}

					// Not a very nice way of adding classes, but works with all versions of WordPress.
					var $buttons = $('.ui-dialog-buttonset').children();
					$buttons.eq(0).addClass('button-primary');
					$buttons.eq(1).addClass('button-secondary');

					$('.rw-addon-grid li.rw-addon').click(function (evt) {
						if ($('li.rw-addon.active').length) {
							$('li.rw-addon.active').toggleClass('active');
						}

						var $card = $(this);
						$card.addClass('active');

						var $target = $(evt.target);
						var elementClass = $target.attr("class");

						var addonAction = ('rw-addon-overlay' === elementClass ? 'Card Click' : 'Purchase Click');
						$popupDialog.find('.addon-action').val(addonAction);

						evt.preventDefault();
						$('.addons-page-notice').hide();
						$popupDialog.dialog('open');

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
						if (!dialogOptions.buttons.hasOwnProperty(idx)) {
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
				function sendData(extraDetails) {
					var data = {
						action   : 'rw-addon-request',
						_n       : '<?php echo wp_create_nonce('rw_send_addon_request'); ?>',
						addon_key: $('li.rw-addon.active').attr('data-idx')
					};

					if (extraDetails) {
						$.extend(data, extraDetails);
					}

					var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
					$.ajax({
						url : ajaxUrl,
						data: data
					});
				}
			})(jQuery);
		</script>
	</div>
<?php fs_require_template( 'powered-by.php' ) ?>