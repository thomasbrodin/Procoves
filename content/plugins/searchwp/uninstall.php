<?php

/**
 * Uninstall SearchWP completely
 */

global $wpdb;

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

include_once( 'searchwp.php' );

if( get_option( SEARCHWP_PREFIX . 'nuke_on_delete' ) ) {

	// delete all plugin options
	delete_option( SEARCHWP_PREFIX . 'settings' );
	delete_option( SEARCHWP_PREFIX . 'version' );
	delete_option( SEARCHWP_PREFIX . 'activated' );
	delete_option( SEARCHWP_PREFIX . 'license_nag' );
	delete_option( SEARCHWP_PREFIX . 'mysql_version_nag' );
	delete_option( SEARCHWP_PREFIX . 'remote' );
	delete_option( SEARCHWP_PREFIX . 'remote_meta' );
	delete_option( SEARCHWP_PREFIX . 'paused' );
	delete_option( SEARCHWP_PREFIX . 'nuke_on_delete' );

	// remove transients
	delete_transient( 'searchwp' );

	// purge the index including all post meta
	$searchwp = new SearchWP();
	$searchwp->purgeIndex();

	// deactivate the license
	$searchwp->deactivateLicense();
	delete_option( SEARCHWP_PREFIX . 'license_key' );

	// drop all custom database tables
	$tables = array( 'cf', 'index', 'log', 'media', 'tax', 'terms' );

	foreach( $tables as $table )
	{
		$tableName = $wpdb->prefix . SEARCHWP_DBPREFIX . $table;

		// make sure the table exists
		if( $wpdb->get_var( "SHOW TABLES LIKE '$tableName'") == $tableName )
		{
			// drop it
			$sql = "DROP TABLE $tableName";
			$wpdb->query( $sql );
		}
	}
}
