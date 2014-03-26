<?php
/*
Plugin Name: FacetWP
Plugin URI: https://facetwp.com/
Description: Faceted Search and Filtering for WordPress
Version: 1.3.4
Author: Matt Gibbs
Author URI: https://facetwp.com/

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

defined( 'ABSPATH' ) or die();

class FacetWP
{

    function __construct() {

        // setup variables
        define( 'FACETWP_VERSION', '1.3.4' );
        define( 'FACETWP_DIR', dirname( __FILE__ ) );
        define( 'FACETWP_URL', plugins_url( 'facetwp' ) );

        // automatic updates
        include( FACETWP_DIR . '/includes/class-updater.php' );
        $this->updater = new FacetWP_Updater( $this );

        add_action( 'init', array( $this, 'init' ) );
    }


    /**
     * Initialize classes and WP hooks
     */
    function init() {

        // i18n
        $this->load_textdomain();

        // classes
        foreach ( array( 'ajax', 'facet', 'helper', 'indexer', 'display', 'upgrade', 'vendor' ) as $f ) {
            include( FACETWP_DIR . "/includes/class-{$f}.php" );
        }

        $upgrade = new FacetWP_Upgrade();
        $this->ajax = new FacetWP_Ajax();
        $this->helper = FacetWP_Helper::instance();
        $this->indexer = new FacetWP_Indexer();
        $this->display = new FacetWP_Display();
        $this->vendor = new FacetWP_Vendor();

        // hooks
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }


    /**
     * i18n support
     */
    function load_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'fwp' );
        $mofile = WP_LANG_DIR . '/facetwp/facetwp-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            load_textdomain( 'fwp', $mofile );
        }
        else {
            load_plugin_textdomain( 'fwp', false, 'facetwp/languages' );
        }
    }


    /**
     * Register the FacetWP settings page
     */
    function admin_menu() {
        add_options_page( 'FacetWP', 'FacetWP', 'manage_options', 'facetwp', array( $this, 'settings_page' ) );
    }


    /**
     * Enqueue admin tooltips
     */
    function admin_scripts( $hook ) {
        if ( 'settings_page_facetwp' == $hook ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );
        }
    }


    /**
     * Route to the correct edit screen
     */
    function settings_page() {
        if ( isset( $_GET['subpage'] ) && 'migrate' == $_GET['subpage'] ) {
            include( FACETWP_DIR . '/templates/page-migrate.php' );
        }
        else {
            include( FACETWP_DIR . '/templates/page-settings.php' );
        }
    }
}

$facetwp = new FacetWP();
