<?php

class FacetWP_Facet_Search
{

    function __construct() {
        $this->label = __( 'Search', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = $params['selected_values'];
        $value = is_array( $value ) ? $value[0] : $value;
        $output .= '<input type="text" class="facetwp-search" value="' . esc_attr( $value ) . '" placeholder="' . __( 'Enter keywords', 'fwp' ) . '" />';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        if ( empty( $selected_values ) ) {
            return 'continue';
        }

        // Default WP search
        if ( empty( $facet['search_engine'] ) ) {
            $query = new WP_Query( array(
                's' => $selected_values,
                'posts_per_page' => 200,
                'fields' => 'ids',
            ) );

            return (array) $query->posts;
        }
        // SearchWP
        else {
            $searchwp = new SearchWPSearch( array(
                'engine' => $facet['search_engine'],
                'terms' => $selected_values,
                'posts_per_page' => 200,
                'load_posts' => false,
                'page' => 1,
            ) );

            return (array) $searchwp->postIDs;
        }
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/search', function($this, obj) {
        $this.find('.facet-search-engine').val(obj.search_engine);
    });

    wp.hooks.addFilter('facetwp/save/search', function($this, obj) {
        obj['search_engine'] = $this.find('.facet-search-engine').val();
        return obj;
    });

    wp.hooks.addAction('facetwp/change/search', function($this) {
        $this.closest('.facetwp-facet').find('.name-source').hide();
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
    wp.hooks.addAction('facetwp/refresh/search', function($this, facet_name) {
        var val = $this.find('.facetwp-search').val() || '';
        FWP.facets[facet_name] = val;
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('keyup', '.facetwp-facet .facetwp-search', function(e) {
            if (13 == e.keyCode) {
                FWP.autoload();
            }
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
        $engines = array();
        if ( is_plugin_active( 'searchwp/searchwp.php' ) ) {
            $settings = get_option( SEARCHWP_PREFIX . 'settings' );
            $engines = $settings['engines'];
        }

?>
        <tr class="facetwp-conditional type-search">
            <td><?php _e('Search engine', 'fwp'); ?>:</td>
            <td>
                <select class="facet-search-engine">
                    <option value=""><?php _e( 'WP Default', 'fwp' ); ?></option>
                    <?php foreach ( $engines as $key => $attributes ) : ?>
                    <?php $label = isset( $attributes['label'] ) ? $attributes['label'] : __( 'Default', 'fwp' ); ?>
                    <option value="<?php echo $key; ?>">SearchWP - <?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
<?php
    }
}
