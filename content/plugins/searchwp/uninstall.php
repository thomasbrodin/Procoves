<?php

/**
 * Uninstall SearchWP completely
 */

global $wpdb;

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

include_once( 'searchwp.php' );

// maybe nuke all data and settings
$swp_live_settings = get_option( SEARCHWP_PREFIX . 'settings' );
$swp_nuke_on_delete = isset( $swp_live_settings['nuke_on_delete'] ) ? $swp_live_settings['nuke_on_delete'] : false;
if( !empty( $swp_nuke_on_delete ) || get_option( SEARCHWP_PREFIX . 'nuke_on_delete' ) ) {

	// purge the index including all post meta
	$searchwp = new SearchWP();
	$searchwp->purgeIndex();

	// deactivate the license
	$searchwp->deactivateLicense();

	// drop all custom database tables
	$tables = array( 'cf', 'index', 'log', 'media', 'tax', 'terms' );

	foreach( $tables as $table ){
		$tableName = $wpdb->prefix . SEARCHWP_DBPREFIX . $table;

		// make sure the table exists
		if( $wpdb->get_var( "SHOW TABLES LIKE '$tableName'") == $tableName ) {
			// drop it
			$sql = "DROP TABLE $tableName";
			$wpdb->query( $sql );
		}
	}

	// delete all plugin settings
	delete_option( SEARCHWP_PREFIX . 'settings' );
	delete_option( SEARCHWP_PREFIX . 'indexer' );
	delete_option( SEARCHWP_PREFIX . 'purge_queue' );
	delete_option( SEARCHWP_PREFIX . 'version' );
	delete_option( SEARCHWP_PREFIX . 'progress' );
	delete_option( SEARCHWP_PREFIX . 'license_key' );
	delete_option( SEARCHWP_PREFIX . 'paused' );

	// remove transients
	delete_transient( 'searchwp' );
}
