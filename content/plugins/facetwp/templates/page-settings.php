<?php

global $wpdb;


$helper = FacetWP_Helper::instance();


// custom fields
$custom_fields = array();
$excluded_fields = apply_filters( 'facetwp_excluded_custom_fields', array(
    '_edit_last',
    '_edit_lock',
) );


$results = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} ORDER BY meta_key" );
foreach ( $results as $result ) {
    if ( !in_array( $result->meta_key, $excluded_fields ) ) {
        $custom_fields[] = $result->meta_key;
    }
}

// taxonomies
$taxonomies = get_taxonomies( array(), 'object' );

// activation status
$message = __( 'Not yet activated', 'fwp' );
$activation = get_option( 'facetwp_activation' );
if ( ! empty( $activation ) ) {
    $activation = json_decode( $activation );
    if ( 'success' == $activation->status ) {
        $message = __( 'License active', 'fwp' );
        $message .= ' (' . __( 'expires', 'fwp' ) . ' ' . date( 'M j, Y', strtotime( $activation->expiration ) ) . ')';
    }
    else {
        $message = $activation->message;
    }
}

?>

<script src="<?php echo FACETWP_URL; ?>/assets/js/event-manager.js"></script>
<script>
(function($) {
    $(function() {
        $(document).tooltip({
            items: '.facetwp-tooltip',
            content: function() {
                return $(this).find('.facetwp-tooltip-content').html();
            }
        });
    });
})(jQuery);
</script>
<?php
foreach ( $helper->facet_types as $class ) {
    $class->admin_scripts();
}
?>
<script src="<?php echo FACETWP_URL; ?>/assets/js/admin.js"></script>
<link href="<?php echo FACETWP_URL; ?>/assets/css/admin.css" rel="stylesheet">


<div class="wrap">
    <div id="icon-facetwp">
        <img src="<?php echo FACETWP_URL; ?>/assets/images/facetwp.png" width="32" height="32" title="FacetWP" alt="FacetWP" />
    </div>
    <h2 class="nav-tab-wrapper">
        <a class="nav-tab" rel="facets"><?php _e( 'Facets', 'fwp' ); ?></a>
        <a class="nav-tab" rel="templates"><?php _e( 'Templates', 'fwp' ); ?></a>
        <a class="nav-tab" rel="settings"><?php _e( 'Settings', 'fwp' ); ?></a>
    </h2>

    <a class="button facetwp-migrate" href="options-general.php?page=facetwp&subpage=migrate"><?php _e( 'Migrate', 'fwp' ); ?></a>

    <div class="facetwp-response"></div>

    <div class="facetwp-action-buttons">
        <div style="float:right">
            <a class="button-primary facetwp-rebuild"><?php _e( 'Rebuild Index', 'fwp' ); ?></a>
            <a class="button-primary facetwp-save"><?php _e( 'Save Changes', 'fwp' ); ?></a>
        </div>
        <a class="button add-facet"><?php _e( 'Add Facet', 'fwp' ); ?></a>
        <a class="button add-template"><?php _e( 'Add Template', 'fwp' ); ?></a>
        <div class="clear"></div>
    </div>

    <div class="facetwp-content facetwp-content-facets">
        <div class="facetwp-tabs">
            <ul></ul>
        </div>
        <div class="facetwp-facets"></div>
        <div class="clear"></div>
    </div>
    <div class="facetwp-content facetwp-content-templates">
        <div class="facetwp-tabs">
            <ul></ul>
        </div>
        <div class="facetwp-templates"></div>
        <div class="clear"></div>
    </div>
    <div class="facetwp-content facetwp-content-settings">
        <table>
            <tr>
                <td style="width:175px"><?php _e( 'License Key', 'fwp' ); ?></td>
                <td>
                    <input type="text" class="facetwp-license" style="width:280px" value="<?php echo get_option( 'facetwp_license' ); ?>" />
                    <input type="button" class="button facetwp-activate" value="<?php _e( 'Activate', 'fwp' ); ?>" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="facetwp-activation-status">
                    <?php echo $message; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- clone settings -->

    <div class="facets-hidden">
        <div class="facetwp-facet">
            <table class="facetwp-table">
                <tr>
                    <td style="width:175px"><?php _e( 'Label', 'fwp' ); ?>:</td>
                    <td>
                        <input type="text" class="facet-label" value="" />
                        <input type="text" class="facet-name" value="" />
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Facet type', 'fwp' ); ?>:</td>
                    <td>
                        <select class="facet-type">
                            <?php foreach ( $helper->facet_types as $name => $class ) : ?>
                            <option value="<?php echo $name; ?>"><?php echo $class->label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="facetwp-show name-source">
                    <td>
                        <?php _e( 'Data source', 'fwp' ); ?>:
                    </td>
                    <td>
                        <select class="facet-source">
                            <optgroup label="<?php _e( 'Posts', 'fwp' ); ?>">
                                <option value="post_type"><?php _e( 'Post Type', 'fwp' ); ?></option>
                                <option value="post_date"><?php _e( 'Post Date', 'fwp' ); ?></option>
                                <option value="post_modified"><?php _e( 'Post Modified', 'fwp' ); ?></option>
                                <option value="post_author"><?php _e( 'Post Author', 'fwp' ); ?></option>
                            </optgroup>
                            <optgroup label="<?php _e( 'Taxonomies', 'fwp' ); ?>">
                                <?php foreach ( $taxonomies as $tax ) : ?>
                                <option value="tax/<?php echo $tax->name; ?>"><?php echo $tax->labels->name; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="<?php _e( 'Custom Fields', 'fwp' ); ?>">
                                <?php foreach ( $custom_fields as $cf ) : ?>
                                <option value="cf/<?php echo $cf; ?>"><?php echo $cf; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </td>
                </tr>
<?php
foreach ( $helper->facet_types as $class ) {
    $class->settings_html();
}
?>
            </table>
            <a class="remove-facet"><?php _e( 'Delete Facet', 'fwp' ); ?></a>
        </div>
    </div>

    <div class="templates-hidden">
        <div class="facetwp-template">
            <table class="facetwp-table">
                <tr>
                    <td style="width:175px"><?php _e( 'Label', 'fwp' ); ?>:</td>
                    <td>
                        <input type="text" class="template-label" value="" />
                        <input type="text" class="template-name" value="" />
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Query Arguments', 'fwp' ); ?>:</td>
                    <td><textarea class="template-query"></textarea>
                </tr>
                <tr>
                    <td><?php _e( 'Display Code', 'fwp' ); ?>:</td>
                    <td><textarea class="template-template"></textarea>
                </tr>
            </table>
            <a class="remove-template"><?php _e( 'Delete Template', 'fwp' ); ?></a>
        </div>
    </div>
</div>
