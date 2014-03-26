<?php

class FacetWP_Vendor
{
    public $search_terms;


    function __construct() {
        add_filter( 'facetwp_query_args', array( $this, 'search_args' ), 10, 2 );
        add_filter( 'facetwp_pre_filtered_post_ids', array( $this, 'searchwp_search' ), 10, 2 );
    }


    /**
     * Prevent the default WP search from running when SearchWP is enabled
     * @since 1.3.2
     */
    function search_args( $args, $class ) {

        if ( isset( $args['s'] ) ) {
            $class->is_search = true;

            if ( is_plugin_active( 'searchwp/searchwp.php' ) ) {
                $this->search_terms = $args['s'];
                unset( $args['s'] );
            }
        }

        return $args;
    }


    /**
     * Use the SearchWP API to retrieve matching post IDs
     * @since 1.3.2
     */
    function searchwp_search( $post_ids, $class ) {

        if ( !empty( $this->search_terms ) ) {
            $searchwp = new SearchWPSearch( array(
                'engine' => 'default',
                'terms' => $this->search_terms,
                'posts_per_page' => 200,
                'load_posts' => false,
                'page' => 1,
            ) );

            // Preserve post ID order
            $intersected_ids = array();
            foreach ( $searchwp->postIDs as $post_id ) {
                if ( in_array( $post_id, $post_ids ) ) {
                    $intersected_ids[] = $post_id;
                }
            }
            $post_ids = $intersected_ids;
        }

        return $post_ids;
    }
}
