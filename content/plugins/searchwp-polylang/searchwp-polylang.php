<?php
/*
Plugin Name: SearchWP Polylang Integration
Plugin URI: https://searchwp.com/
Description: Integrate SearchWP with Polylang
Version: 0.3
Author: Jonathan Christopher
Author URI: https://searchwp.com/

Copyright 2013-2014 Jonathan Christopher

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

class SearchWP_Polylang
{

	function __construct()
	{
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( $this, 'plugin_row' ), 11 );

		add_filter( 'searchwp_include', array( $this, 'include_only_current_language_posts' ), 10, 3 );
	}

	function include_only_current_language_posts( $relevantPostIds, $engine, $terms )
	{
		$post_ids = $relevantPostIds;

		if( function_exists( 'pll_current_language' ) && function_exists( 'pll_default_language' ) ) {

			$currentLanguage = pll_current_language();

			if ( false == $currentLanguage ) {
				$currentLanguage = pll_default_language();
			}

			// get all posts in the current language
			$args = array(
				'nopaging'      => true,
				'post_type'     => 'any',
				'post_status'   => 'any',
				'fields'        => 'ids',
				'tax_query'     => array(
					array(
						'taxonomy'  => 'language',
						'field'     => 'slug',
						'terms'     => $currentLanguage
					)
				)
			);

			// we may need to limit to relevant post IDs
			if ( !empty( $relevantPostIds ) ) {
				$args['post__in'] = $relevantPostIds;
			}

			$query = new WP_Query( $args );
			$post_ids = $query->posts;

		}

		return $post_ids;
	}

	function plugin_row()
	{
		if( !class_exists( 'SearchWP' ) )
		{ ?>
			<tr class="plugin-update-tr searchwp">
				<td colspan="3" class="plugin-update">
					<div class="update-message">
						<?php _e( 'SearchWP must be active to use this Extension' ); ?>
					</div>
				</td>
			</tr>
		<?php }
		else
		{
			$searchwp = SearchWP::instance();
			if( version_compare( $searchwp->version, '1.1', '<' ) )
			{ ?>
				<tr class="plugin-update-tr searchwp">
					<td colspan="3" class="plugin-update">
						<div class="update-message">
							<?php _e( 'SearchWP Polylang Integration requires SearchWP 1.1 or greater', $searchwp->textDomain ); ?>
						</div>
					</td>
				</tr>
			<?php }
		}
	}

}

new SearchWP_Polylang();
