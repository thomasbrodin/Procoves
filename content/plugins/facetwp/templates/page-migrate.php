<?php

$settings = get_option( 'facetwp_settings' );
$settings = json_decode( $settings, true );

$export = array();

foreach ( $settings['facets'] as $facet ) {
    $export['facet-' . $facet['name']] = 'Facet - ' . $facet['label'];
}

foreach ( $settings['templates'] as $template ) {
    $export['template-' . $template['name']] = 'Template - '. $template['label'];
}

?>
<script src="<?php echo FACETWP_URL; ?>/assets/js/admin.js"></script>
<link href="<?php echo FACETWP_URL; ?>/assets/css/admin.css" rel="stylesheet">
<style type="text/css">
.export-code, .import-code {
    width: 400px;
    height: 100px;
}

.export-code {
    display: none;
}
</style>

<script>
(function($) {
    $(function() {
        $(document).on('click', '.export-submit', function() {
                $('.export-code').show();
                $('.export-code').text('');
                $.post(ajaxurl, {
                    action: 'facetwp_migrate',
                    action_type: 'export',
                    items: $('.export-items').val()
                },
                function(response) {
                    $('.export-code').text(response);
                });
        });
        $(document).on('click', '.import-submit', function() {
            $('.facetwp-response').show();
            $('.facetwp-response').html('Importing...');
            $.post(ajaxurl, {
                action: 'facetwp_migrate',
                action_type: 'import',
                import_code: $('.import-code').val(),
                overwrite: $('.import-overwrite').is(':checked') ? 1 : 0
            },
            function(response) {
                $('.facetwp-response').html(response);
            });
        });
    });
})(jQuery);
</script>

<div class="wrap">
    <div id="icon-facetwp" class="icon32">
        <img src="<?php echo FACETWP_URL; ?>/assets/images/facetwp.png" width="32" height="32" alt="FacetWP" />
    </div>
    <h2>FacetWP <?php _e( 'Migrate', 'fwp' ); ?></h2>

    <a class="button" style="float:right; margin:-25px 20px 0 0" href="options-general.php?page=facetwp">&laquo; <?php _e( 'Settings', 'fwp' ); ?></a>

    <div class="facetwp-response"></div>

    <h3><?php _e( 'Export', 'fwp' ); ?></h3>
    <table>
        <tr>
            <td valign="top">
                <select class="export-items" multiple="multiple" style="width:200px; height:100px">
                    <?php foreach ( $export as $val => $label ) : ?>
                    <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top:5px"><a class="button export-submit"><?php _e( 'Export', 'fwp' ); ?></a></div>
            </td>
            <td valign="top">
                <textarea class="export-code" placeholder="Loading..."></textarea>
            </td>
        </tr>
    </table>
    <h3><?php _e( 'Import', 'fwp' ); ?></h3>
    <div><textarea class="import-code" placeholder="<?php _e( 'Paste the import code here', 'fwp' ); ?>"></textarea></div>
    <div><input type="checkbox" class="import-overwrite" /> <?php _e( 'Overwrite existing items?', 'fwp' ); ?></div>
    <div style="margin-top:5px"><a class="button import-submit"><?php _e( 'Import', 'fwp' ); ?></a></div>
</div>
