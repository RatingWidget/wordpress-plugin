<?php
	$current_user = wp_get_current_user();

	$first_name = $current_user->user_firstname;
	if (empty($first_name))
		$first_name = $current_user->nickname;

	$site_url = get_site_url();

?>
<p>Hey <?php echo $first_name ?>,<br>
	RatingWidget is a ratings <b>service</b> - the ratings data is stored on our servers. To use the plugin we need to connect your account to <a href="https://rating-widget.com/" target="_blank">rating-widget.com</a> and <a href="https://freemius.com/wordpress/" target="_blank">freemius.com</a>.</p>
<p style="font-size: 12px; margin-top: 10px; text-align: center;">
Please read our <a id="rw_terms_trigger" href="#">Terms of Use and Privacy Policy</a>.</p>
<p id="full_terms" style="display: none; font-size: 12px; margin-top: 10px; text-align: justify; border: 1px solid #ccc; padding: 10px;">The RatingWidget is a SaaS rating system for your blog. It is based on dynamic Html &amp; JavaScript and was intentionally developed as plug &amp; play widget without the need of setting up any DataBase. Therefore, all the ratings and voting data is sent and stored on RatingWidget's servers. Also, limited personal information like your email is sent and stored to stay in touch with you and send you announcements, updates, promotions and more. For the full details, please read our <a href="//rating-widget.com/terms-of-use/" target="_blank" tabindex="-1" style="line-height: 16px;">Terms of Use</a> and <a href="//rating-widget.com/privacy/" target="_blank" tabindex="-1" style="line-height: 16px;">Privacy Policy</a>. Note: The plugin code is licensed under the <a href="http://codex.wordpress.org/GPL" target="_blank">GPL license</a>.</p>
<script>
	(function($){
		$('#rw_terms_trigger').click(function(){
			$('#full_terms').toggle();
		});
	})(jQuery);
</script>