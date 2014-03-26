<?php

class FacetWP_Facet_Slider
{

    function __construct() {
        $this->label = __( 'Slider', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = $params['selected_values'];
        $output .= '<div class="facetwp-slider-wrap">';
        $output .= '<div class="facetwp-slider"></div>';
        $output .= '</div>';
        $output .= '<span class="facetwp-slider-label"></span>';
        $output .= '<div><input type="button" class="facetwp-slider-reset" value="' . __( 'Reset', 'fwp' ) . '" /></div>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $values = $params['selected_values'];
        $where = '';

        if ( !empty( $values[0] ) ) {
            $where .= " AND CAST(facet_value AS DECIMAL(10,2)) >= '{$values[0]}'";
        }
        if ( !empty( $values[1] ) ) {
            $where .= " AND CAST(facet_value AS DECIMAL(10,2)) <= '{$values[1]}'";
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return $wpdb->get_col( $sql );
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];
        $selected_values = $params['selected_values'];

        $min = $wpdb->get_var( "SELECT facet_value FROM {$wpdb->prefix}facetwp_index WHERE facet_name = '{$facet['name']}' $where_clause ORDER BY CAST(facet_value AS SIGNED) ASC" );
        $max = $wpdb->get_var( "SELECT facet_value FROM {$wpdb->prefix}facetwp_index WHERE facet_name = '{$facet['name']}' $where_clause ORDER BY CAST(facet_value AS SIGNED) DESC" );

        $selected_min = isset( $selected_values[0] ) ? $selected_values[0] : $min;
        $selected_max = isset( $selected_values[1] ) ? $selected_values[1] : $max;

        return array(
            'range' => array($selected_min, $selected_max),
            'start' => array($min, $max),
            'step' => $facet['step'],
            'resolution' => 1
        );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/slider', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.type-slider .facet-step').val(obj.step);
    });

    wp.hooks.addFilter('facetwp/save/slider', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['step'] = $this.find('.type-slider .facet-step').val();
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
<link href="<?php echo FACETWP_URL; ?>/assets/js/noUiSlider/jquery.nouislider.css" rel="stylesheet">
<script src="<?php echo FACETWP_URL; ?>/assets/js/noUiSlider/jquery.nouislider.js"></script>
<script>


FWP.used_facets = {};


(function($) {
    wp.hooks.addAction('facetwp/refresh/slider', function($this, facet_name) {
        FWP.facets[facet_name] = [];
        // The settings have already been loaded
        if ('undefined' !== typeof FWP.used_facets[facet_name]) {
            FWP.facets[facet_name] = $this.find('.facetwp-slider').val();
        }
    });

    wp.hooks.addAction('facetwp/set_label/slider', function($this) {
        var facet_name = $this.attr('data-name');
        var label = FWP.settings[facet_name]['lower'];
        label += ' &mdash; ';
        label += FWP.settings[facet_name]['upper'];
        $this.find('.facetwp-slider-label').html(label);
    });

    $(document).on('facetwp-loaded', function() {
        $('.facetwp-slider').each(function() {
            var $parent = $(this).closest('.facetwp-facet');
            var facet_name = $parent.attr('data-name');
            var opts = FWP.settings[facet_name];

            // Fail on slider already initialized
            if ('undefined' != typeof $(this).data('options')) {
                return false;
            }

            // Fail on invalid ranges
            if (opts.range[0] >= opts.range[1]) {
                return false;
            }

            $(this).noUiSlider({
                range: opts.range,
                start: opts.start,
                step: opts.step,
                behavior: 'extend',
                connect: true,
                set: function() {
                    FWP.used_facets[facet_name] = true;
                    FWP.static_facet = facet_name;
                    FWP.refresh();
                },
                serialization: {
                    to: [
                        function(val) {
                            FWP.settings[facet_name]['lower'] = val;
                            wp.hooks.doAction('facetwp/set_label/slider', $parent);
                        },
                        function(val) {
                            FWP.settings[facet_name]['upper'] = val;
                            wp.hooks.doAction('facetwp/set_label/slider', $parent);
                        }
                    ],
                    resolution: opts.resolution
                }
            });
        });
    });

    $(document).on('click', '.facetwp-slider-reset', function() {
        var facet_name = $(this).closest('.facetwp-facet').attr('data-name');
        delete FWP.used_facets[facet_name];
        FWP.refresh();
    });
})(jQuery);
</script>
<?php
    }


    /**
     * (Admin) Output settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-slider">
            <td>
                <?php _e('Step', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Snap the slider at this interval', 'fwp' ); ?> (default = 1)</div>
                </div>
            </td>
            <td><input type="text" class="facet-step" value="1" /></td>
        </tr>
<?php
    }
}
