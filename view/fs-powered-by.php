<?php
    $page = str_replace( WP_RW__ADMIN_MENU_SLUG, '', $_GET['page'] );

    $params = array(
        'page'           => 'rw' . ( empty( $page ) ? '-settings' : $page ),
        'module_id'      => rw_fs()->get_id(),
        'module_slug'    => rw_fs()->get_slug(),
        'module_version' => rw_fs()->get_plugin_version(),
    );
?>
<?php fs_require_template( 'powered-by.php', $params ) ?>
