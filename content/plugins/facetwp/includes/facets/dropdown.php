<?php

class FacetWP_Facet_Dropdown
{

    function __construct() {
        $this->label = __( 'Dropdown', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];

        // Orderby
        $orderby = 'counter DESC, f.facet_display_value ASC';
        if ( 'display_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_display_value ASC';
        }
        elseif ( 'raw_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_value ASC';
        }

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 10;

        $sql = "
        SELECT f.facet_value, f.facet_display_value, COUNT(*) AS counter
        FROM {$wpdb->prefix}facetwp_index f
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        return $wpdb->get_results( $sql );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        $output .= '<select class="facetwp-dropdown">';
        $output .= '<option value="">- ' . __( 'Any', 'fwp' ) . ' -</option>';

        foreach ( $values as $result ) {
            $selected = in_array( $result->facet_value, $selected_values ) ? ' selected' : '';

            // Determine whether to show counts
            $display_value = $result->facet_display_value;
            $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true );
            if ( $show_counts ) {
                $display_value .= " ($result->counter)";
            }

            $output .= '<option value="' . $result->facet_value . '"' . $selected . '>' . $display_value . '</option>';
        }

        $output .= '</select>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value IN ('$selected_values')";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/dropdown', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.facet-parent-term').val(obj.parent_term);
        $this.find('.type-dropdown .facet-orderby').val(obj.orderby);
        $this.find('.type-dropdown .facet-count').val(obj.count);
    });

    wp.hooks.addFilter('facetwp/save/dropdown', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['parent_term'] = $this.find('.type-dropdown .facet-parent-term').val();
        obj['orderby'] = $this.find('.type-dropdown .facet-orderby').val();
        obj['count'] = $this.find('.type-dropdown .facet-count').val();
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
    wp.hooks.addAction('facetwp/refresh/dropdown', function($this, facet_name) {
        FWP.facets[facet_name] = $this.find('.facetwp-dropdown').val() || '';
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('change', '.facetwp-facet .facetwp-dropdown', function() {
            var $facet = $(this).closest('.facetwp-facet');
            if ('' != $facet.find(':selected').val()) {
                FWP.static_facet = $facet.attr('data-name');
            }
            FWP.refresh();
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
        <tr class="facetwp-conditional type-dropdown">
            <td>
                <?php _e('Parent term', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        If <strong>Data source</strong> is a taxonomy, enter the
                        parent term's ID if you want to show child terms.
                        Otherwise, leave blank.
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="facet-parent-term" value="" />
            </td>
        </tr>
        <tr class="facetwp-conditional type-dropdown">
            <td><?php _e('Sort by', 'fwp'); ?>:</td>
            <td>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Facet Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-dropdown">
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
