<?php
/*
Plugin Name: FacetWP - Cache
Plugin URI: https://facetwp.com/
Description: Caching support for FacetWP
Version: 1.0.2
Author: Matt Gibbs
Author URI: https://facetwp.com/
GitHub Plugin URI: https://github.com/mgibbs189/facetwp-cache
GitHub Branch: 1.0.2

Copyright 2014 Matt Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


class FWP_Cache
{

    function __construct() {

        // setup variables
        define( 'FACETWP_CACHE_VERSION', '1.0.2' );
        define( 'FACETWP_CACHE_DIR', dirname( __FILE__ ) );

        add_action( 'init' , array( $this, 'init' ) );
    }


    /**
     * Intialize
     */
    function init() {

        // upgrade
        include( FACETWP_CACHE_DIR . '/includes/class-upgrade.php' );
        $upgrade = new FacetWP_Cache_Upgrade();

        add_filter( 'facetwp_ajax_response', array( $this, 'save_cache' ), 10, 2 );
        add_action( 'facetwp_cache_cleanup', array( $this, 'cleanup' ) );

        // Schedule daily cleanup
        if ( !wp_next_scheduled( 'facetwp_cache_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'facetwp_cache_cleanup' );
        }
    }


    /**
     * Cache the AJAX response
     */
    function save_cache( $output, $params ) {
        global $wpdb;

        // Caching support
        if ( defined( 'FACETWP_CACHE' ) && FACETWP_CACHE ) {
            $cache_name = md5( json_encode( $params['data'] ) );
            $cache_lifetime = apply_filters( 'facetwp_cache_lifetime', 3600 );
            $nocache = isset( $_POST['data']['http_params']['get']['nocache'] );

            if ( false === $nocache ) {
                $wpdb->insert( $wpdb->prefix . 'facetwp_cache', array(
                    'name' => $cache_name,
                    'value' => $output,
                    'expire' => date( 'Y-m-d H:i:s', time() + $cache_lifetime )
                ) );
            }
        }

        return $output;
    }


    /**
     * Delete expired cache
     */
    function cleanup() {
        global $wpdb;

        $now = date( 'Y-m-d H:i:s' );
        $wpdb->query( "DELETE FROM {$wpdb->prefix}facetwp_cache WHERE expire < '$now'" );
    }
}


$fwp_cache = new FWP_Cache();
