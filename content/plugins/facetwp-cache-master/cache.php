<?php

if ( !defined( 'ABSPATH' ) ) exit;

define( 'FACETWP_CACHE', true );

$action = isset( $_POST['action'] ) ? $_POST['action'] : '';
$nocache = isset( $_POST['data']['http_params']['get']['nocache'] );

if ( 'facetwp_refresh' == $action ) {

    global $table_prefix;
    $wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
    $wpdb->prefix = $table_prefix;

    $now = date( 'Y-m-d H:i:s' );
    $cache_name = json_encode( $_POST['data'] );
    $cache_name = md5( $cache_name );

    // Check for a cached version
    $sql = "
    SELECT value
    FROM {$wpdb->prefix}facetwp_cache
    WHERE name = '$cache_name' AND expire >= '$now'
    LIMIT 1";
    $value = $wpdb->get_var( $sql );

    // Return cached version and EXIT
    if ( null !== $value && false === $nocache ) {
        echo $value;
        exit;
    }
}
