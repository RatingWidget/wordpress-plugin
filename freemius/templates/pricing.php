<?php
	$slug = $VARS['slug'];
	$fs = fs($slug);

	fs_enqueue_local_script('jquery-postmessage', 'jquery.ba-postmessage.min.js');

	$timestamp = time();
	$site = $fs->get_site();
	$params = array(
		'context_site' => $site->id,
		's_ctx_ts' => $timestamp,
		's_ctx_secure' => md5($timestamp . $site->id . $site->secret_key . $site->public_key . 'upgrade'),
		'next' => fs_get_admin_plugin_url('account') . '&action=sync_license',
	);
?>
<div>
	<div id="iframe"></div>
	<form action="" method="POST">
		<input type="hidden" name="user_id" />
		<input type="hidden" name="user_email" />
		<input type="hidden" name="site_id" />
		<input type="hidden" name="public_key" />
		<input type="hidden" name="secret_key" />
		<input type="hidden" name="action" value="account" />
	</form>

	<script type="text/javascript">
		(function($) {
			$(function () {

				var
				// Keep track of the iframe height.
					iframe_height = 800,
					domain = '<?php echo WP_RW__LOCALHOST_SCRIPTS ? WP_RW__ADDRESS : WP_RW__SECURE_ADDRESS ?>',
//					domain = 'http://localhost:8080',
				// Pass the parent page URL into the Iframe in a meaningful way (this URL could be
				// passed via query string or hard coded into the child page, it depends on your needs).
					src = domain + '/pricing-internal/wordpress/?<?php echo http_build_query($params) ?>#' + encodeURIComponent(document.location.href),
				// Append the Iframe into the DOM.
					iframe = $('<iframe " src="' + src + '" width="100%" height="' + iframe_height + 'px" scrolling="no" frameborder="0" style="background: transparent;"><\/iframe>')
						.load(function () {
						})
						.appendTo('#iframe');

				// Setup a callback to handle the dispatched MessageEvent event. In cases where
				// window.postMessage is supported, the passed event will have .data, .origin and
				// .source properties. Otherwise, this will only have the .data property.
				$.receiveMessage(function (e) {
					var data = JSON.parse(e.data),
						h = data.height;

					if (!isNaN(h) && h > 0 && h != iframe_height) {
						iframe_height = (h < iframe_height) ? iframe_height : h;
						$("#iframe iframe").height(iframe_height + 'px');
					}

					/*if (null == identity.user_id)
					 return;

					 $(document.body).css({'cursor':'wait'});

					 // Update user values.
					 for (var k in identity)
					 $('#rw_wp_registration form input[name=' + k + ']').val(identity[k]);

					 $('#rw_wp_registration form').submit();*/
				}, domain);
			});
		})(jQuery);
	</script>
</div>
<?php fs_require_template('powered-by.php') ?>