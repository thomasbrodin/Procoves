<?php
/*
Plugin Name: SearchWP LIKE Terms
Plugin URI: https://searchwp.com/
Description: Add %LIKE% to search queries (more restrictive than Fuzzy Matches)
Version: 1.1
Author: Jonathan Christopher
Author URI: https://searchwp.com/

Copyright 2013 Jonathan Christopher

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
if( !defined( 'ABSPATH' ) ) exit;

class SearchWPLike {

	function __construct() {
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( $this, 'plugin_row' ), 11 );

		add_filter( 'searchwp_pre_search_terms', array( $this, 'findLikeTerms' ), 10, 2 );

		// prevent AND logic since we're using LIKE results
		add_filter( 'searchwp_and_logic', '__return_false' );
	}

	function findLikeTerms( $terms, $engine ) {
		global $wpdb, $searchwp, $wp_query;

		$prefix = $wpdb->prefix;

		if( ! is_array( $terms ) ) {
			return $terms;
		}

		// check against the regex pattern whitelist
		$terms = ' ' . implode( ' ', $terms ) . ' ';
		$whitelisted_terms = array();
		if( method_exists( $searchwp, 'extract_terms_using_pattern_whitelist' ) ) { // added in SearchWP 1.9.5
			// extract terms based on whitelist pattern, allowing for approved indexing of terms with punctuation
			$whitelisted_terms = $searchwp->extract_terms_using_pattern_whitelist( $terms );

			// add the buffer so we can whole-word replace
			$terms = str_replace( ' ', '  ', $terms );

			// remove the matches
			if( ! empty( $whitelisted_terms ) ) {
				$terms = str_ireplace( $whitelisted_terms, '', $terms );
			}

			// clean up the double space flag we used
			$terms = str_replace( '  ', ' ', $terms );
		}

		// rebuild our terms array
		$terms = explode( ' ', $terms );

		// maybe append our whitelist
		if( is_array( $whitelisted_terms ) && ! empty( $whitelisted_terms ) ) {
			$whitelisted_terms = array_map( 'trim', $whitelisted_terms );
			$terms = array_merge( $terms, $whitelisted_terms );
		}

		$terms = array_filter( array_map( 'trim', $terms ), 'strlen' );

		// dynamic minimum character length
		$minCharLength = absint( apply_filters( 'searchwp_like_min_length', 5 ) ) - 1;

		$sql = "SELECT term FROM {$prefix}swp_terms WHERE CHAR_LENGTH(term) > {$minCharLength} AND (";

		// need to query for fuzzy matches in terms table and append them
		$count = 0;
		foreach( $terms as $term ) {
			$term = str_replace( "'", '', $wpdb->prepare( "%s", $term ) );
			if( $count > 0 ) $sql .= " OR ";
			$sql .= " ( term LIKE '%{$term}%' ) ";
			$count++;
		}
		$sql .= ")";

		$likeTerms = $wpdb->get_col( $sql );

		$terms = array_values( array_unique( array_merge( $likeTerms, $terms ) ) );

		// respect the max number of terms so we don't overload MySQL
		$maxSearchTerms = intval( apply_filters( 'searchwp_max_search_terms', 6, $engine ) );
		$maxSearchTerms = intval( apply_filters( 'searchwp_like_max_search_terms', $maxSearchTerms, $engine ) );

		if ( count( $terms ) > $maxSearchTerms ) {
			$terms = array_slice( $terms, 0, $maxSearchTerms );
			// need to tell $wp_query that we hijacked this
			$wp_query->query['s'] = $wp_query->query_vars['s'] = sanitize_text_field( implode( ' ', $terms ) );
		}

		return $terms;
	}

	function plugin_row() {
		if( !class_exists( 'SearchWP' ) ) {
			return;
		}

		$searchwp = SearchWP::instance();
		if( version_compare( $searchwp->version, '1.0.8', '<' ) ) { ?>
			<tr class="plugin-update-tr searchwp">
				<td colspan="3" class="plugin-update">
					<div class="update-message">
						<?php _e( 'SearchWP LIKE Terms requires SearchWP 1.0.8 or greater', $searchwp->textDomain ); ?>
					</div>
				</td>
			</tr>
		<?php }
	}

}

new SearchWPLike();
