<?php
/*
Plugin Name: FacetWP - WPML
Plugin URI: https://facetwp.com/
Description: WPML support for FacetWP
Version: 1.0.0
Author: Matt Gibbs
Author URI: https://facetwp.com/
GitHub Plugin URI: https://github.com/mgibbs189/facetwp-wpml
GitHub Branch: 1.0.0

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


class FWP_WPML
{
    public $lang;


    function __construct() {
        add_action( 'init' , array( $this, 'init' ) );
    }


    /**
     * Intialize
     */
    function init() {
        add_filter( 'facetwp_indexer_query_args', array( $this, 'facetwp_indexer_query_args' ) );
    }


    /**
     * Index all languages
     */
    function facetwp_indexer_query_args( $args ) {
        $args['suppress_filters'] = true; // query posts in all languages
        return $args;
    }
}


$fwp_wpml = new FWP_WPML();
