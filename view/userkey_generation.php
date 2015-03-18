<?php
    $current_user = wp_get_current_user();
 ?>
<div id="rw_wp_registration" class="rw-wp-container rw-dir-ltr wrap">
    <h2><?php _e( 'You are just 30 sec away from boosting your blog with ratings...', WP_RW__ID ); ?></h2>
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
    (function($){
        $(function(){
          
          // Keep track of the iframe height.
          var if_height,
                domain = '<?php echo WP_RW__LOCALHOST_SCRIPTS ? WP_RW__ADDRESS : WP_RW__SECURE_ADDRESS ?>',
                // Pass the parent page URL into the Iframe in a meaningful way (this URL could be
                // passed via query string or hard coded into the child page, it depends on your needs).
                src = domain + '/signup/wordpress/#' + encodeURIComponent(document.location.href),
            
            // Append the Iframe into the DOM.
            iframe = $('<iframe " src="' + src + '" width="100%" height="650" scrolling="no" frameborder="0" style="background: transparent;"><\/iframe>')
                .load(function(){
                      var 
                          address = document.location.href,
                          pos = address.indexOf('/wp-admin');
                      
                      address = address.substring(0, pos);
                      $.postMessage(JSON.stringify({
                          first: '<?php echo addslashes($current_user->user_firstname) ?>',
                          last: '<?php echo addslashes($current_user->user_lastname) ?>',
                          email: '<?php echo addslashes($current_user->user_email) ?>',
                          address: address,
                          title: '<?php echo addslashes(get_option('blogname', '')) ?>'
                      }), src, iframe.get(0).contentWindow);
                })
                .appendTo('#iframe');
          
            // Setup a callback to handle the dispatched MessageEvent event. In cases where
            // window.postMessage is supported, the passed event will have .data, .origin and
            // .source properties. Otherwise, this will only have the .data property.
            $.receiveMessage(function(e){
                var identity = JSON.parse(e.data);

                if (null == identity.user_id)
                    return;
                
                $(document.body).css({'cursor':'wait'});

                // Update user values.
                for (var k in identity)
                    $('#rw_wp_registration form input[name=' + k + ']').val(identity[k]);
                
                $('#rw_wp_registration form').submit();
            }, domain );
        });
    })(jQuery);
    </script>
</div>
<?php fs_require_template('powered-by.php') ?>