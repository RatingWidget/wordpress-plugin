<?php
	/*
	 * The view content of the Affiliate Program page
	 * called from affiliation_settings_page_render method in rating-widget.php
	 */
?>
<div class="wrap rw-dir-ltr">
	<form id="rw-affiliation-page" method="post" action="">
		<div id="poststuff">
			<div class="postbox rw-body">
				<div class="inside rw-ui-content-container rw-no-radius">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e('Affiliate Program', WP_RW__ID); ?></h1>
					</header>
					<div id="thankyou-message" class="updated">
						<p><strong><?php _e("Thank you for applying for our affiliating program, we'll review your details during the next 14 days and will get back to you with further information.", WP_RW__ID); ?></strong></p>
					</div>
					<div class="entry-content">
						<p><?php _e('Like the plugin? You can become our ambassador and make some money in return ;-)', WP_RW__ID); ?></p>
						<p><?php _e("Refer new members to our plugin and receive 25% commission of their purchase! Simply copy and paste your referrer link on your site and when a new user clicks your link and proceeds to sign up an account, you will receive 25% of that person's recurring payments when he upgrades. For example, if a user sign-up through your link and upgrades to the Professional annual plan, you'll be getting $23.97 for the first payment. If the customer continues using the Professional plan for another year, you get another $23.97. You will get commission for that customer as long as he stays on the same plan. Btw. if the user upgrades to our Business Level Lifetime plan, you get $325 USD for that purchase!", WP_RW__ID); ?></p>
						<h3><?php _e('How it works?', WP_RW__ID); ?></h3>
						<ul>
							<li><?php _e('We use cookies to track people who have clicked on your link, so they need to be using cookies for us to track them.', WP_RW__ID); ?></li>
							<li><?php _e('If a person clears their cookies then we can’t track them any more.', WP_RW__ID); ?></li>
							<li><?php _e('If a person clicks on your referral link and then later they click on someone else’s, yours is the one that counts.', WP_RW__ID); ?></li>
							<li><?php _e('If a person clicks a link, they have 30 days before the cookie expires. If they sign-up and upgrade during those 30 days, then you earn commission.', WP_RW__ID); ?></li>
							<li><?php _e('Commissions appear in your account and you get email notifications for each sale.', WP_RW__ID); ?></li>
							<li><?php _e('If purchase was refunded, commission will be voided and subtracted from your account.', WP_RW__ID); ?></li>
							<li><?php _e('Minimum payout is $100', WP_RW__ID); ?></li>
							<li><?php _e('You must have a valid PayPal account to receive commission earnings, this is the only affiliate payout method we currently support.', WP_RW__ID); ?></li>
						</ul>
						<p><a id="apply-affiliate" class="button button-primary" title="<?php _e('Become an affiliate', WP_RW__ID); ?>" href="#"><?php _e('Apply to become an affiliate', WP_RW__ID); ?></a></p>
					</div>
				</div>
			</div>
			<div id="fair-usage-message" class="update-nag">
				<p><?php _e('Use of the referral program is subject to a fair usage policy which gives RatingWidget.com the right to review each and every referral.', WP_RW__ID); ?></p>
			</div>
			<div class="update-nag">
				<p><?php _e('<strong>Tax info</strong>: At the end of the year, We fill out a 1099 form for all USA residents affiliates who earned more than $1,000 in a calendar year. This is when we will need your Tax ID # and W9 form. We report it to the IRS (You probably do too).', WP_RW__ID); ?></p>
			</div>
			<div class="update-nag">
				<p><?php _e("<strong>Legal Info</strong>: Please note that all payouts are at our sole discretion and we reserve the right to refuse any payout. By creating an account with us you are agreeing to our terms and conditions, which strictly prohibit any and all methods of spam and abuse. Any clicks on your link must be through simple hyperlinks directly to our affiliate link around text or an image. And while we love new clients, you unfortunately can't refer yourself.", WP_RW__ID); ?></p>
			</div>
		</div>
	</form>
	<script>
		(function($) {
			var ajaxUrl = '<?php echo admin_url('admin-ajax.php?action=rw-affiliate-apply'); ?>';

			$('#apply-affiliate').on('click', function() {
				$.ajax({
					url: ajaxUrl,
					method: 'POST',
					data: {_n: '<?php echo wp_create_nonce('rw_send_affiliate_application_nonce'); ?>'},
					beforeSend: function() {
						$('#apply-affiliate').text('<?php _e('Loading...', WP_RW__ID); ?>');
					},
					complete: function() {
						$('#rw-affiliation-page .entry-content').remove();
						$('#thankyou-message').show();
						$("html, body").animate({scrollTop: 0}, "fast");
					}
				});
				return false;
			});
		})(jQuery);
	</script>
</div>
<?php fs_require_template('powered-by.php') ?>