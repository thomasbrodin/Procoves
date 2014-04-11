<?php

class FacetWP_Facet_Checkboxes2
{

    function __construct() {
        $this->label = __( 'Checkboxes2', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function render( $params ) {

        global $wpdb;

        $helper = FacetWP_Helper::instance();

        $output = '';
        $facet = $params['facet'];
        $selected_values = (array) $params['selected_values'];
        $where_clause = $params['where_clause'];

        // Orderby
        $orderby = 'counter DESC, f.facet_display_value ASC';
        if ( 'display_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_display_value ASC';
        }
        elseif ( 'raw_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_value ASC';
        }

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 10;

        // Properly handle "OR" facets
        if ( 'or' == $facet['operator'] ) {
            if ( isset( $facetwp->or_values ) && ( 1 < count( $facetwp->or_values ) || !isset( $facetwp->or_values[ $facet['name'] ] ) ) ) {
                $post_ids = array();
                unset( $facetwp->or_values[ $facet['name'] ] );
                foreach ( $facetwp->or_values as $key => $vals ) {
                    $post_ids = ( 0 == $key ) ? $vals : array_intersect( $post_ids, $vals );
                }
            }
            else {
                $post_ids = $facetwp->unfiltered_post_ids;
            }

            $where_clause = ' AND post_id IN (' . implode( ',', $post_ids ) . ')';
        }

        $facet_parent_id = (int) $selected_values[0];

        // Determine the parent_id and depth
        if ( !empty( $selected_values ) && 0 < (int) $selected_values[0] ) {  
            $sql = "
            SELECT f.facet_value, f.facet_display_value, COUNT(*) AS counter
            FROM {$wpdb->prefix}facetwp_index f
            WHERE f.facet_name = '{$facet['name']}' $where_clause 
            GROUP BY f.facet_value
            ORDER BY $orderby
            LIMIT $limit";
        }

        else {
        
            $sql = "
            SELECT f.facet_value, f.facet_display_value, COUNT(*) AS counter
            FROM {$wpdb->prefix}facetwp_index f
            WHERE f.facet_name = '{$facet['name']}' $where_clause AND parent_id = '$facet_parent_id'
            GROUP BY f.facet_value
            ORDER BY $orderby
            LIMIT $limit";
        }

        $results = $wpdb->get_results( $sql );

        $taxonomy = str_replace( 'tax/', '', $facet['source'] );
        $term_object = get_term( $facet_parent_id, $taxonomy );

        // if ( $term_object ) {
        //     foreach ( $obj as $term_object) {  
        //         $selected = in_array( $obj->facet_value, $selected_values ) ? ' checked' : '';         
        //         $output .=  '<div class="facetwp-depth">';
        //         $output .= '<div class="facetwp-checkbox normes button'. $selected . '" data-value="' . $obj->facet_value . '">';
        //         $output .= $obj->facet_display_value . '</div>';
        //         $output .= '</div>';
        //     }
        // } else {
            foreach ( $results as $result ) {
            $selected = in_array( $result->facet_value, $selected_values ) ? ' checked' : '';
            $output .= '<div class="facetwp-checkbox normes button'. $selected . '" data-value="' . $result->facet_value . '">';
            $output .= $result->facet_display_value . '</div>';
            }
        // }
        

        return $output;
    }

    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $output = array();
        $facet = $params['facet'];
        $selected_values = $params['selected_values'];


        $sql = $wpdb->prepare( "SELECT DISTINCT post_id
            FROM {$wpdb->prefix}facetwp_index
            WHERE facet_name = %s",
            $facet['name']
        );

        // Match ALL values
        if ( 'and' == $facet['operator'] ) {
            foreach ( $selected_values as $key => $value ) {
                $results = $wpdb->get_col( $sql . " AND facet_value IN ('$value')" );
                $output = ( $key > 0 ) ? array_intersect( $output, $results ) : $results;

                if ( empty( $output ) ) {
                    break;
                }
            }
        }
        // Match ANY value
        else {
            $selected_values = implode( "','", $selected_values );
            $output = $wpdb->get_col( $sql . " AND facet_value IN ('$selected_values')" );
        }

        return $output;
    }



    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/checkboxes2', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.type-checkboxes2 .facet-orderby').val(obj.orderby);
        $this.find('.type-checkboxes2 .facet-operator').val(obj.operator);
        $this.find('.type-checkboxes2 .facet-count').val(obj.count);
    });

    wp.hooks.addFilter('facetwp/save/checkboxes2', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['operator'] = $this.find('.type-checkboxes2 .facet-operator').val();
        obj['orderby'] = $this.find('.type-checkboxes2 .facet-orderby').val();
        obj['count'] = $this.find('.type-checkboxes2 .facet-count').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>
<script>
(function($) {
     wp.hooks.addAction('facetwp/refresh/checkboxes2', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-checkbox.checked').each(function() {
            selected_values.push($(this).attr('data-value'));
        });
        FWP.facets[facet_name] = selected_values;
    });

    wp.hooks.addAction('facetwp/ready/checkboxes2', function() {
        $(document).on('click', '.facetwp-facet .facetwp-checkbox .normes .button', function() {
            $(this).closest('.facetwp-facet').find('.facetwp-checkbox').removeClass('checked');
            if ('' != $(this).attr('data-value')) {
                $(this).addClass('checked');
            }
            FWP.refresh();
        });

        $(document).on('click', '.facetwp-facet .facetwp-toggle', function() {
            $(this).closest('.facetwp-facet').find('.facetwp-toggle').toggleClass('facetwp-hidden');
            $(this).closest('.facetwp-facet').find('.facetwp-collapsed').toggleClass('facetwp-hidden');
        });
    });
})(jQuery);
</script>
<?php
    }

/**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-checkboxes2">
            <td><?php _e('Sort by', 'fwp'); ?>:</td>
            <td>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Facet Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes2">
            <td>
                <?php _e('Operation', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'How should multiple selections affect the results?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-operator">
                    <option value="and"><?php _e( 'Narrow the result set', 'fwp' ); ?></option>
                    <option value="or"><?php _e( 'Widen the result set', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes2">
            <td>
                <?php _e('Count', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-count" value="10" /></td>
        </tr>
<?php
    }
}
