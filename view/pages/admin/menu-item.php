<style type="text/css">
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?> .wp-menu-image a
	{ background-image: url(<?php echo WP_RW__PLUGIN_URL . 'icons.png' ?>) !important; background-position: -1px -32px; }
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>:hover .wp-menu-image a,
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>.wp-has-current-submenu .wp-menu-image a,
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>.current .wp-menu-image a
	{ background-position: -1px 0; }
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?> .wp-menu-image a img { display: none; }
<?php if (rw_fs()->is_not_paying()) : ?>
	ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?> ul li:last-child a {
		color: yellowgreen;
		font-weight: bold;
		text-transform: uppercase;
	}
<?php endif; ?>
</style>