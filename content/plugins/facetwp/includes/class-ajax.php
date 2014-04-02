<?php

class FacetWP_Ajax
{

    function __construct() {
        // ajax settings
        add_action( 'wp_ajax_facetwp_load', array( $this, 'load_settings' ) );
        add_action( 'wp_ajax_facetwp_save', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_facetwp_refresh', array( $this, 'refresh' ) );
        add_action( 'wp_ajax_nopriv_facetwp_refresh', array( $this, 'refresh' ) );
        add_action( 'wp_ajax_facetwp_rebuild_index', array( $this, 'rebuild_index' ) );
        add_action( 'wp_ajax_facetwp_heartbeat', array( $this, 'heartbeat' ) );
        add_action( 'wp_ajax_facetwp_license', array( $this, 'license' ) );
        add_action( 'wp_ajax_facetwp_migrate', array( $this, 'migrate' ) );
    }


    /**
     * Load admin settings
     */
    function load_settings() {
        if ( current_user_can( 'manage_options' ) ) {
            echo get_option( 'facetwp_settings' );
        }
        die();
    }


    /**
     * Save admin settings
     */
    function save_settings() {
        if ( current_user_can( 'manage_options' ) ) {
            $settings = stripslashes( $_POST['data'] );
            update_option( 'facetwp_settings', $settings );
            echo __( 'Settings saved', 'fwp' );
        }
        die();
    }


    /**
     * Rebuild the index table
     */
    function rebuild_index() {
        if ( current_user_can( 'manage_options' ) ) {
            $indexer = new FacetWP_Indexer();
            $indexer->index();
        }
        die();
    }


    /**
     * The AJAX facet refresh handler
     */
    function refresh() {

        global $wpdb;

        $data = stripslashes_deep( $_POST['data'] );
        $facets = json_decode( $data['facets'] );
        $extras = isset( $data['extras'] ) ? $data['extras'] : array();

        $params = array(
            'facets'            => array(),
            'template'          => $data['template'],
            'static_facet'      => $data['static_facet'],
            'http_params'       => $data['http_params'],
            'extras'            => $extras,
            'soft_refresh'      => (int) $data['soft_refresh'],
            'paged'             => (int) $data['paged'],
        );

        foreach ( $facets as $facet_name => $selected_values ) {
            $params['facets'][] = array(
                'facet_name'        => $facet_name,
                'selected_values'   => $selected_values,
            );
        }

        $facet_class = new FacetWP_Facet();
        $output = $facet_class->render( $params );

        // Query debugging
        if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
            $queries = array();
            foreach ( $wpdb->queries as $query ) {
                $sql = preg_replace( "/[\s]/", ' ', $query[0] );
                $sql = preg_replace( "/[ ]{2,}/", ' ', $sql );

                $queries[] = array(
                    'sql'   => $sql,
                    'time'  => $query[1],
                    'stack' => $query[2],
                );
            }
            $output['queries'] = $queries;
        }

        $output = json_encode( $output );

        echo apply_filters( 'facetwp_ajax_response', $output, array(
            'data' => $data
        ) );

        exit;
    }


    /**
     * Keep track of indexing progress
     */
    function heartbeat() {
        $indexer = new FacetWP_Indexer();
        echo $indexer->get_progress();
        die();
    }


    /**
     * Import / export functionality
     */
    function migrate() {
        $action_type = $_POST['action_type'];
        $helper = FacetWP_Helper::instance();

        $output = array();

        if ( 'export' == $action_type ) {
            $items = $_POST['items'];

            if ( !empty( $items ) ) {
                foreach ( $items as $item ) {
                    if ( 'facet' == substr( $item, 0, 5 ) ) {
                        $item_name = substr( $item, 6 );
                        $output['facets'][] = $helper->get_facet_by_name( $item_name );
                    }
                    elseif ( 'template' == substr( $item, 0, 8 ) ) {
                        $item_name = substr( $item, 9 );
                        $output['templates'][] = $helper->get_template_by_name( $item_name );
                    }
                }
            }
            echo json_encode( $output );
        }
        elseif ( 'import' == $action_type ) {
            $settings = $helper->settings;
            $import_code = json_decode( stripslashes( $_POST['import_code'] ), true );
            $overwrite = (int) $_POST['overwrite'];

            if ( empty( $import_code ) || !is_array( $import_code ) ) {
                _e( 'Nothing to import', 'fwp' );
                die();
            }

            $response = array(
                'message' => __( 'Import complete', 'fwp' ),
                'imported' => array(),
                'skipped' => array(),
            );

            foreach ( $import_code as $object_type => $object_items ) {
                foreach ( $object_items as $object_item ) {
                    $is_match = false;
                    foreach ( $settings[$object_type] as $key => $settings_item ) {
                        if ( $object_item['name'] == $settings_item['name'] ) {
                            if ( $overwrite ) {
                                $settings[$object_type][$key] = $object_item;
                                $response['imported'][] = $object_item['label'];
                            }
                            else {
                                $response['skipped'][] = $object_item['label'];
                            }
                            $is_match = true;
                            break;
                        }
                    }

                    if ( !$is_match ) {
                        $settings[$object_type][] = $object_item;
                        $response['imported'][] = $object_item['label'];
                    }
                }
            }

            update_option( 'facetwp_settings', json_encode( $settings ) );

            echo $response['message'];
            if ( !empty( $response['imported'] ) ) {
                echo '<div><strong>' . __( 'Imported', 'fwp' ) . ':</strong> ' . implode( ', ', $response['imported'] ) . '</div>';
            }
            if ( !empty( $response['skipped'] ) ) {
                echo '<div><strong>' . __( 'Skipped', 'fwp' ) . ':</strong> ' . implode( ', ', $response['skipped'] ) . '</div>';
            }
        }

        die();
    }


    /**
     * License activation
     */
    function license() {
        $license = $_POST['license'];
        $helper = FacetWP_Helper::instance();

        $request = wp_remote_post( 'https://facetwp.com/updater/', array(
            'body' => array(
                'action'        => 'activate',
                'slug'          => 'facetwp',
                'license'       => $license,
                'host'          => $helper->get_http_host(),
            )
        ) );

        if ( !is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
            update_option( 'facetwp_license', $license );
            update_option( 'facetwp_activation', $request['body'] );
            echo $request['body'];
        }
        else {
            echo json_encode( array(
                'status'    => 'error',
                'message'   => __( 'Unable to connect to activation server', 'fwp' ),
            ) );
        }
        die();
    }
}
