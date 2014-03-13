<?php
    
    class apto_shortcode_reorder 
        {
            function __construct()
                {
                    if(is_admin())
                        return;
                    
                    add_shortcode( 'apto_reorder', array($this, 'start' ) );   
                    
                    //load the js dependencies
                    wp_enqueue_script('jquery-ui-core');
                    wp_enqueue_script('jquery-ui-sortable');
                    wp_enqueue_script('jquery-ui-widget');
                    wp_enqueue_script('jquery-ui-mouse');
                    
                    $myJavascriptFile = CPTURL . '/js/touch-punch.min.js';
                    wp_register_script('touch-punch.min.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'touch-punch.min.js');
                       
                    $myJavascriptFile = CPTURL . '/js/nested-sortable.js';
                    wp_register_script('nested-sortable.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'nested-sortable.js');
                     
                    $myJavascriptFile = CPTURL . '/js/apto-javascript.js';
                    wp_register_script('apto-javascript.js', $myJavascriptFile);
                    wp_enqueue_script( 'apto-javascript.js');
                    
                    //load the style dependencies 
                    wp_register_style('shortcode-reorder.css', CPTURL . '/css/shortcode-reorder.css');
                    wp_enqueue_style( 'shortcode-reorder.css');
                    
                    
                    add_filter('apto_reorder_item', array($this, 'filter_apto_reorder_item'), 10, 2);
                }
            
            function filter_apto_reorder_item($output, $post_data)
                {
                    if(is_admin())
                        return $output;
                        
                    //replace the edit button
                    $regexp = "/<a href=\"([^\"]*)\">EDIT<\/a>/iU";
                    $output = preg_replace($regexp, "", $output);
                    
                    return $output;   
                }
    
                
            function start($atts)
                {
                    extract( shortcode_atts( array(
                                            'capability'    => 'install_plugins',
                                            'post_type'     =>  '',
                                            'taxonomy'      =>  '',
                                            'term_id'       =>  '',
                                            'term_slug'     =>  '',
                                            'hide_archive'  =>  'false'
                                        ), $atts ) );
                    
                    //check for capability
                    if(!is_user_logged_in() || !current_user_can($capability))
                        return '';
                    
                    if (!isset($post_type) || $post_type == '')
                        return '<p>Post Type attribute is required</p>';
                    
                    $this->current_post_type    = get_post_type_object($post_type);
                    if($this->current_post_type == null)
                        return '<p>Invalid Post Type</p>';
                    
                    $this->taxonomy             =  $taxonomy;
                    $this->term_id              =  $term_id;
                    
                    if($this->term_id == '' && $term_slug != '')
                        {
                            $term_data = get_term_by('slug', $term_slug, $this->taxonomy);                    
                            $this->term_id = $term_data->term_id;
                        }
                    
                    $this->capability           =  $capability;
                    $this->hide_archive         =  strtolower($hide_archive);
                    
                    global $post;
                    
                    $this->post                 = $post;
            
                    ob_start();                    
                    $html = $this->reorder_interface();

                    $html = ob_get_contents();
                    ob_end_clean();
                                                
                    return $html;   
                    
                }
               

            function reorder_interface() 
                {
                    global $wpdb, $wp_locale;
                    
                    $options = get_option('cpto_options');

                    $post_type = $this->current_post_type->name;

                    $is_hierarchical = $this->current_post_type->hierarchical;
                    
                    $current_taxonomy   = isset($_GET['current_taxonomy']) ? $_GET['current_taxonomy'] : '';
                    
                    if ($current_taxonomy != "" && $current_taxonomy != "_archive_" && !taxonomy_exists($current_taxonomy))
                        return;
                        
                    if ($current_taxonomy == "_archive_" && $this->hide_archive == 'true')
                        return;
                    
                    if($this->taxonomy != '' && $current_taxonomy  != '' && $current_taxonomy  != '_archive_' && $current_taxonomy != $this->taxonomy)
                        return;
      
                    $cat                = isset($_GET['cat']) ? (int)$_GET['cat'] : -1;
                    
                    if($this->term_id != '')
                        $cat = $this->term_id;
                    
                    $form_messages = '';
                    
                    //check for order reset
                    if (isset($_POST['order_reset']) && $_POST['order_reset'] == '1' && $post_type != '')
                        {
                            if(wp_verify_nonce($_POST['nonce'],  'reorder-interface-reset-' . get_current_user_id()))
                                {                            
                                    $_reset_cat = trim($_POST['cat']);
                                    $cat = $_reset_cat;
                                    $current_taxonomy   = isset($_POST['current_taxonomy']) ? $_POST['current_taxonomy'] : '';
                                    reset_post_order($post_type, $_reset_cat, $current_taxonomy);
                                    $form_messages .= '<div id="message" class="updated"><p>' . __('Reset Order Successfully', 'apto') . '</p></div>'; 
                                }
                                else
                                {
                                    $form_messages .= '<div id="message" class="updated"><p>' . __( 'Invalid Nonce', 'apto' )  . '</p></div>';    
                                }
                        }

                    //check the post taxonomies.
                    $object_taxonomies = get_object_taxonomies($post_type);

                    if($this->taxonomy != '')
                        $object_taxonomies = array($this->taxonomy);
                    
                    if(!is_array($object_taxonomies))
                        $object_taxonomies = array();
                    
                    if($current_taxonomy != '_archive_' && !in_array($current_taxonomy, $object_taxonomies))
                        $current_taxonomy = '';
                    
                    if ($current_taxonomy == '' && count($object_taxonomies) >= 1)
                        {
                            //use categories as default
                            if (in_array('category', $object_taxonomies))
                                {
                                    $current_taxonomy = 'category';   
                                }
                                else
                                    {
                                        reset($object_taxonomies);
                                        $current_taxonomy = current($object_taxonomies);
                                    }
                        }
                    
                    if ($current_taxonomy == '' && count($object_taxonomies) < 1)
                        {
                            $current_taxonomy = '_archive_';
                        }

                    $current_taxonomy_info = get_taxonomy($current_taxonomy);
                    
                    $is_woocommerce = FALSE;
                    if ($post_type == "product" && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                        $is_woocommerce = TRUE;

                    //use _archive_ if still not data
                    if ($current_taxonomy == '' && $this->hide_archive != 'true')
                        $current_taxonomy = '_archive_';
                        
                    if ($current_taxonomy == '' && $this->taxonomy != '')
                        $current_taxonomy = $this->taxonomy;
                                
                    if ($current_taxonomy != '' && $current_taxonomy != '_archive_' && ($cat == '' || $cat <= 0))
                        {
                            $cat = cpto_get_first_term($current_taxonomy);
                        }
                    
                    //check if the restored term_id is available/valid
                    if ($current_taxonomy != '' && $current_taxonomy != '_archive_' && $cat != '' && $cat > 0)
                        {
                            if (get_term_by('id', $cat, $current_taxonomy) === FALSE)
                                $cat = cpto_get_first_term($current_taxonomy);
                        }
                    
                                                                
                    if($current_taxonomy == '')
                        return;
                    
                    //set as default for auto
                    $order_type = (isset($options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_type'])) ? $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_type'] : 'manual'; 
                    
                    //check for order type update
                    if (isset($_GET['order_type']))
                        {
                            $new_order_type = $_GET['order_type'];
                            if ($new_order_type != 'auto' && $new_order_type != 'manual')
                                $new_order_type = '';
                                
                            if ($new_order_type != '')
                                {
                                    $order_type = $new_order_type;
                                    $is_batch_update    = FALSE;
                                    $batch_work_terms   = array();
                                    
                                    //check for batch update    
                                    if(isset($_GET['batch_order_update']) && $_GET['batch_order_update'] == 'yes')
                                        $is_batch_update = TRUE;
                                    if($is_batch_update === TRUE)
                                        {
                                            //get all terms of current taxonomy
                                            $args = array(
                                                            'hide_empty'    => false,
                                                            'fields'        =>  'ids'
                                                            );
                                            $batch_work_terms = get_terms( $current_taxonomy, $args );
                                            
                                        }
                                    
                                    //save the new order
                                    $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_type'] = $order_type;
                                    if($is_batch_update === TRUE)
                                        {
                                            //update the order type for all terms
                                            foreach($batch_work_terms as $batch_work_term)   
                                                {
                                                    $options['taxonomy_settings'][$post_type][$current_taxonomy][$batch_work_term]['order_type'] = $order_type;   
                                                }
                                            
                                        }
                                    

                                    //update the orde_by
                                    if (isset($_GET['auto_order_by']))
                                        {
                                            $new_order_by = $_GET['auto_order_by'];
                                            if ($new_order_by != '')
                                                {
                                                    $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_by'] = $new_order_by;
                                                    
                                                    if($is_batch_update === TRUE)
                                                        {
                                                            //update the order type for all terms
                                                            foreach($batch_work_terms as $batch_work_term)   
                                                                {
                                                                    $options['taxonomy_settings'][$post_type][$current_taxonomy][$batch_work_term]['order_by'] = $new_order_by;   
                                                                }
                                                        }   
                                                }
                                        }
                                        
                                    //update the custom field name
                                    if (isset($_GET['auto_custom_field_name']))
                                        {
                                            $new_custom_field_name = trim(stripslashes($_GET['auto_custom_field_name']));
                                            $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['custom_field_name'] = $new_custom_field_name;
                                            
                                            if($is_batch_update === TRUE)
                                                {
                                                    //update the order type for all terms
                                                    foreach($batch_work_terms as $batch_work_term)   
                                                        {
                                                            $options['taxonomy_settings'][$post_type][$current_taxonomy][$batch_work_term]['custom_field_name'] = $new_custom_field_name;   
                                                        }
                                                }
                                        } 
                                    
                                    //update the orde_by
                                    if (isset($_GET['auto_order']))
                                        {
                                            $new_order = $_GET['auto_order'];
                                            if ($new_order_by != '')
                                                $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order'] = $new_order;
                                                
                                            if($is_batch_update === TRUE)
                                                {
                                                    //update the order type for all terms
                                                    foreach($batch_work_terms as $batch_work_term)   
                                                        {
                                                            $options['taxonomy_settings'][$post_type][$current_taxonomy][$batch_work_term]['order'] = $new_order;   
                                                        }
                                                }
                                        }

                                    update_option('cpto_options', $options);                        
                                }
                        }
                        
                    ?>
                    <div id="apto_reorder">

                        <div id="ajax-response"><?php echo $form_messages ?></div>
                        
                        <noscript>
                            <div class="error message">
                                <p><?php _e( "This plugin can't work without javascript, because it's use drag and drop and AJAX.", 'apto' ) ?></p>
                            </div>
                        </noscript>

                        <div class="clear"></div>
                        
                         
                        <form action="<?php 
                        
                            echo get_permalink($this->post->ID);
                            
                        ?>" method="get" id="apto_form">
                               
                        <?php
                         
                            if($this->hide_archive != 'true')
                                {
                                    ?>
                                    
                                    <h2 class="subtitle"><?php echo $this->current_post_type->labels->singular_name ?> <?php _e( "Archive & Taxonomies", 'apto' ) ?></h2>
                                    <table cellspacing="0" class="wp-list-taxonomy widefat fixed">
                                        <thead>
                                        <tr>
                                            <th style="" class="column-cb check-column" scope="col">&nbsp;</th><th style="" class="col_title" scope="col"><?php _e( "Archive", 'apto' ) ?></th><th style="" class="col_info" scope="col"><?php _e( "Total", 'apto' ) ?> <?php echo $this->current_post_type->labels->singular_name ?> <?php _e( "Archive Posts", 'apto' ) ?></th>    </tr>
                                        </thead>
                                        <tr valign="top" class="">
                                                <th class="check-column" scope="row"><input type="radio" onclick="apto_change_taxonomy(this, true)" value="_archive_" <?php if ($current_taxonomy == '_archive_') {echo 'checked="checked"';} ?> name="current_taxonomy">&nbsp;</th>
                                                <td class="categories column-categories"><?php _e( "Archive", 'apto' ) ?></td>
                                                <td class="categories column-categories"><?php 
                                                    $count_posts = (array)wp_count_posts($post_type);
                                                    echo array_sum($count_posts);
                                                    ?></td>
                                        </tr>
                                    </tbody>
                                    </table>
                                     <?php  
                                } 
                                     
                            if (count($object_taxonomies) > 0 
                                    && !( $this->hide_archive == 'true' && count($object_taxonomies) < 2 && $this->term_id != '')
                                    )
                                {         
                            ?>   
                                    <table cellspacing="0" class="wp-list-taxonomy widefat fixed">
                                        <thead>
                                        <tr>
                                            <th style="" class="column-cb check-column" scope="col">&nbsp;</th><th style="" class="col_title" scope="col"><?php _e( "Taxonomy Title", 'apto' ) ?></th><th style="" class="col_info" scope="col"><?php _e( "Total", 'apto' ) ?> <?php echo $this->current_post_type->labels->singular_name ?> <?php _e( "Posts", 'apto' ) ?></th>    </tr>
                                        </thead>
          
                                        <tbody id="the-list">
                                        <?php
                                            
                                            $alternate = FALSE;
                                            
                                            foreach ($object_taxonomies as $key => $taxonomy)
                                                {
                                                    $alternate = $alternate === TRUE ? FALSE :TRUE;
                                                    $taxonomy_info = get_taxonomy($taxonomy);
                                                    
                                                    $taxonomy_terms = get_terms($taxonomy);
                                                    
                                                    $taxonomy_terms_ids = array();
                                                    foreach ($taxonomy_terms as $taxonomy_term)
                                                        $taxonomy_terms_ids[] = $taxonomy_term->term_id;    
                                                    
                                                    if (count($taxonomy_terms_ids) > 0)
                                                        {
                                                            $term_ids = array_map('intval', $taxonomy_terms_ids );
                                                                                                                          
                                                            $term_ids = "'" . implode( "', '", $term_ids ) . "'";
                                                                                                                                     
                                                            $query = "SELECT COUNT(DISTINCT tr.object_id) as count FROM $wpdb->term_relationships AS tr 
                                                                            INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                                                                            INNER JOIN $wpdb->posts as posts ON tr.object_id = posts.ID
                                                                            WHERE tt.taxonomy IN ('$taxonomy') AND tt.term_id IN ($term_ids) AND  posts.post_type = '$post_type'" ;
                                                            $count = $wpdb->get_var($query);
                                                        }
                                                        else
                                                            {
                                                                $count = 0;   
                                                            }
                                                    
                                                    ?>
                                                        <tr valign="top" class="<?php if ($alternate === TRUE) {echo 'alternate ';} ?>" id="taxonomy-<?php echo $taxonomy  ?>">
                                                                <th class="check-column" scope="row"><input type="radio" onclick="apto_change_taxonomy(this, false)" value="<?php echo $taxonomy ?>" <?php if ($current_taxonomy == $taxonomy) {echo 'checked="checked"';} ?> name="current_taxonomy">&nbsp;</th>
                                                                <td class="categories column-categories"><p><span><?php echo $taxonomy_info->label ?></span>
                                                                
                                                                    <?php
                                                                        if ($current_taxonomy == $taxonomy && $this->term_id == '')
                                                                            {
                                                                                                                            
                                                                            if ( is_object_in_taxonomy($post_type, $current_taxonomy) ) 
                                                                                {
                                                                                    //check if there are any terms in that taxonomy before ouptut the dropdown
                                                                                    $argv = array(
                                                                                                    'hide_empty'    =>   0
                                                                                                    );
                                                                                    $terms = get_terms($current_taxonomy, $argv);
                                                                                    
                                                                                    $dropdown_options = array(
                                                                                                                'echo'              =>  0,
                                                                                                                'hide_empty'        =>  0, 
                                                                                                                'hierarchical'      =>  1,
                                                                                                                'show_count'        =>  1, 
                                                                                                                'orderby'           =>  'name', 
                                                                                                                'taxonomy'          =>  $current_taxonomy,
                                                                                                                'selected'          =>  $cat,
                                                                                                                'class'             =>  'taxonomy_terms'
                                                                                                                );
                                                                                    
                                                                                    if (count($terms) > 0)
                                                                                        {
                                                                                            $select_html = wp_dropdown_categories($dropdown_options);
                                                                                            if(!empty($select_html))
                                                                                                {
                                                                                                    $select_html = str_replace("<select ", "<select onchange=\"jQuery(this).closest('form').submit();\"", $select_html);
                                                                                                    echo $select_html;   
                                                                                                }
                                                                                            
                                                                                            $found_action = TRUE;
                                                                                        }
                                                                                }
                                                                    

                                                                            } ?></p></td>
                                                                <td class="categories column-categories"><?php echo $count ?></td>
                                                        </tr>
                                                    
                                                    <?php
                                                }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }
                        
                        ?></form><?php
                        
                        if (count($object_taxonomies) > 0)
                            {    
                            ?>
                            <br />
                            <?php
                            }
                        ?>

                        <form action="<?php 
                        
                            echo get_permalink($this->post->ID);
                            
                        ?>" method="get" id="apto_form_order">
                             
                             <input type="hidden" name="cat" value="<?php echo $cat ?>" />
                             <input type="hidden" name="current_taxonomy" value="<?php echo $current_taxonomy ?>" />
                                
                            <h2 class="subtitle"><input type="radio" <?php if ($order_type == 'auto') {echo 'checked="checked"';} ?> name="order_type" value="auto" onclick="jQuery(this).closest('form').submit();"> Automatic Order</h2>
                            <?php if ($order_type == 'auto')
                                    {
                                       ?>
                                        <div id="order-post-type">
                                            
                                            <div id="nav-menu-header">
                                                <div class="major-publishing-actions">

                                                    <div class="actions">
                                                        <p class="actions">
                                                            <input type="submit" value="Update" class="button-primary alignright" name="update">
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="clear"></div>

                                                </div><!-- END .major-publishing-actions -->
                                            </div><!-- END #nav-menu-header -->

                                            
                                            <div id="post-body">                    
                                                
                                                <table class="form-table">
                                                    <tbody>
                                                        <tr valign="top">
                                                            <th scope="row"><b><?php _e( "Order By", 'apto' ) ?></b></th>
                                                            <th>
                                                                <?php
                                                                
                                                                    $auto_order_by          = isset($options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_by']) ? $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order_by'] : '_default_';
                                                                    $auto_custom_field_name = isset($options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['custom_field_name']) ? $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['custom_field_name'] : '';
                                                                ?>
                                                                <input type="radio" <?php if ($auto_order_by == '_default_') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="_default_" name="auto_order_by" />
                                                                <label for="blog-public">Default</label><br>
                                                                
                                                                <input type="radio" <?php if ($auto_order_by == 'ID') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="ID" name="auto_order_by" />
                                                                <label for="blog-public">Creation Time / ID</label><br>
                                                                
                                                                <input type="radio" <?php if ($auto_order_by == 'post_title') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="post_title" name="auto_order_by" />
                                                                <label for="blog-norobots">Name</label><br>
                                                                
                                                                  
                                                                <input type="radio" <?php if ($auto_order_by == 'post_name') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="post_name" name="auto_order_by" />
                                                                <label for="blog-norobots">Slug</label><br>
                                                                
                                                                <input type="radio" <?php if ($auto_order_by == '_random_') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="_random_" name="auto_order_by" />
                                                                <label for="blog-norobots">Random</label><br>
                                                                
                                                                <input type="radio" <?php if ($auto_order_by == '_custom_field_') {echo 'checked="checked"'; } ?> onchange="apto_autosort_orderby_field_change(this)" value="_custom_field_" name="auto_order_by" />
                                                                <label for="blog-norobots">Custom Field</label><br>
                                                                <div id="apto_custom_field_area" <?php
                                                                    if ($auto_order_by != '_custom_field_')
                                                                        echo 'style="display: none"';
                                                                ?>>
                                                                    <label for="blog-norobots">Custom Field Name</label><br>
                                                                    <input type="text" class="regular-text" value="<?php echo $auto_custom_field_name ?>" name="auto_custom_field_name">
                                                                </div>
                                                                 
                                                            </th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="form-table">
                                                    <tbody>
                                                        <tr valign="top">
                                                            <th scope="row"><b><?php _e( "Order", 'apto' ) ?></b></th>
                                                            <th>
                                                                <?php
                                                                
                                                                    $auto_order = isset($options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order']) ? $options['taxonomy_settings'][$post_type][$current_taxonomy][$cat]['order'] : 'DESC';

                                                                ?>
                                                                
                                                                <input type="radio" <?php if ($auto_order == 'DESC') {echo 'checked="checked"'; } ?> value="DESC" name="auto_order" />
                                                                <label for="blog-public">Descending</label><br>

                                                                <input type="radio" <?php if ($auto_order == 'ASC') {echo 'checked="checked"'; } ?> value="ASC" name="auto_order" />
                                                                <label for="blog-public">Ascending</label><br>
                                                                
                                                            </th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <br />
                                                <?php if (isset($current_taxonomy_info->label)) { ?>
                                                <br /><br />
                                                <table class="form-table">
                                                    <tbody>
                                                        <tr valign="top">
                                                            <th scope="row"><b><?php _e( "Batch Terms Automatic Update", 'apto' ) ?></b></th>
                                                            <th width="150">
                                                                <input type="radio" checked="checked" value="no" name="batch_order_update" />
                                                                <label for="blog-public">No</label><br>

                                                                <input type="radio" value="yes" name="batch_order_update" />
                                                                <label for="blog-public">Yes</label><br>
                                                                
                                                            </th>
                                                            <th>
                                                                 <p><i><?php _e( "<b>WARNING!</b></i> using this will update all existing", 'apto' ) ?> <?php echo $current_taxonomy_info->label ?> <?php _e( "terms order type with automatic sort type using currrent settings.", 'apto' ) ?> <?php _e( "However all existing manual/custom sort lists will be kept, but order type switched to automatic.", 'apto' ) ?> </p>
                                                                
                                                            </th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <?php } ?>    
                                                <br />
                                                <div class="clear"></div>
                                            </div>
                                            
                                            <div id="nav-menu-footer">
                                                <div class="major-publishing-actions">
                                                        <div class="actions">
                                                            <input type="submit" value="Update" class="button-primary alignright" name="update">
                                                        </div>
                                                        
                                                        <div class="clear"></div>

                                                </div><!-- END .major-publishing-actions -->
                                            </div><!-- END #nav-menu-header -->
                                            
                                        </div>
                                        
                                        <?php
                                    }
                            ?>
                            
                            <h2 class="subtitle"><input type="radio" <?php if ($order_type == 'manual') {echo 'checked="checked"';} ?> name="order_type" value="manual" onclick="jQuery(this).closest('form').submit();"> Manual Order</h2>
                            <?php if ($order_type == 'manual')
                                    {
                                       ?>
                            
                            <div id="order-post-type">
                                
                                <div id="nav-menu-header">
                                    <div class="major-publishing-actions">

                                            
                                            
                                            <div class="actions">
                                                <p class="actions">
                                                    
                                                    <a class="button-secondary alignleft toggle_thumbnails" title="Cancel" href="javascript:;" onclick="toggle_thumbnails(); return false;"><?php _e( "Toggle Thumbnails", 'apto' ) ?></a>

                                                    <a href="javascript:;" class="save-order alignright button-primary"><?php _e( "Update", 'apto' ) ?></a>
                                                    <span class="img_spacer"><img alt="" src="<?php echo CPTURL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading" style="display: none;"></span>
                                                </p>
                                            </div>
                                            
                                            <div class="clear"></div>

                                    </div><!-- END .major-publishing-actions -->
                                </div><!-- END #nav-menu-header -->

                                                        
                                <div id="post-body">                    
                                    <script type="text/javascript">    
                                        var term_id     = '<?php echo $cat ?>';
                                        var post_type   = '<?php echo $post_type ?>';
                                        var taxonomy    = '<?php echo $current_taxonomy ?>';
                                        var lang        = '<?php echo apto_get_blog_language(); ?>';
                                    </script>
                                   
                                    <ul id="sortable"<?php
                                
                                                if (($is_hierarchical === TRUE || $is_woocommerce === TRUE) && $current_taxonomy == '_archive_')
                                                    {
                                                        ?> class="nested_sortable"<?php
                                                    }
                                                    
                                            ?>>
                                        <?php 
                                            $query_string = 'cat='.$cat.'&hide_empty=0&title_li=&post_type='.$this->current_post_type->name;
                                            if ($current_taxonomy != '_archive_')
                                                $query_string .= '&taxonomy='.$current_taxonomy;
                                                else
                                                $query_string .= '&taxonomy=';
                                                
                                            if (($is_hierarchical === TRUE || $is_woocommerce === TRUE) && $current_taxonomy == '_archive_')
                                                {
                                                }
                                                else
                                                $query_string .= '&depth=-1';
                                                
                                            $this->listPostType($query_string);
                                        ?>
                                    </ul>
                                    
                                    <div class="clear"></div>
                                </div>
                                
                                <div id="nav-menu-footer">
                                    <div class="major-publishing-actions">
                                                
                                            <div class="actions">
                                                <p class="submit">
                                                    
                                                    <a href="javascript:;" class="save-order alignright button-primary"><?php _e( "Update", 'apto' ) ?></a>
                                                    <span class="img_spacer"><img alt="" src="<?php echo CPTURL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading" style="display: none;"></span>
                                                </p>
                                            </div>
                                            
                                            <div class="clear"></div>

                                    </div><!-- END .major-publishing-actions -->
                                </div><!-- END #nav-menu-header -->  
                                
                            </div> 

                            
                            <br />
                            <a id="order_Reset" class="button-primary" href="javascript: void(0)" onclick="confirmSubmit()"><?php _e( "Reset Order", 'apto' ) ?></a>
                            
                            <script type="text/javascript">
                                
                                function confirmSubmit()
                                    {
                                        var agree=confirm("<?php _e( "Are you sure you want to reset the order??", 'apto' ) ?>");
                                        if (agree)
                                            {
                                                jQuery('#apto_form_order_reset').submit();   
                                            }
                                            else
                                            {
                                                return false ;
                                            }
                                    }
                                
                                jQuery(document).ready(function() {
                                    
                                    //jQuery( "#sortable" ).sortable();
                                    jQuery('ul#sortable').nestedSortable({
                                            handle:             'div',
                                            tabSize:            30,
                                            listType:           'ul',
                                            items:              'li',
                                            toleranceElement:   '> div',
                                            placeholder:        'ui-sortable-placeholder',
                                            disableNesting:     'no-nesting'
                                            <?php
                                
                                                if (($is_hierarchical === TRUE || $is_woocommerce === TRUE) && $current_taxonomy == '_archive_')
                                                    {
                                                    }
                                                    else
                                                    {
                                                        ?>,disableNesting      :true<?php
                                                    }
                                            ?>
                                        });
                                    
                                    
                                      
                                    jQuery(".save-order").bind( "click", function() {
                                        jQuery(this).parent().find('img').show();
                                        
                                         var queryString = { 
                                                                action:         'update-custom-type-order', 
                                                                order:          jQuery("#sortable").nestedSortable("serialize"), 
                                                                term_id:        term_id, 
                                                                post_type:      post_type, 
                                                                taxonomy:       taxonomy, 
                                                                lang:           lang,
                                                                nonce:          '<?php echo wp_create_nonce( 'reorder-interface-' . get_current_user_id()) ?>'
                                                                    };
                                        //send the data through ajax
                                        jQuery.ajax({
                                          type: 'POST',
                                          url: '<?php echo admin_url(); ?>admin-ajax.php',
                                          data: queryString,
                                          cache: false,
                                          dataType: "html",
                                          success: function(response){
                                                            jQuery("#ajax-response").html('<div class="message updated fade"><p>' + response + '</p></div>');
                                                            jQuery("#ajax-response div").delay(3000).hide("slow");
                                                            jQuery('img.pto_ajax_loading').hide();    
                                          },
                                          error: function(html){

                                              }
                                        });
                                    });
                                });
                            </script>
                            <?php } ?>
                            
                            
                         </form>
                         
                         
                         <form action="" method="post" id="apto_form_order_reset">
                            <input type="hidden" name="order_reset" value="1" />
                            <input type="hidden" name="selected_post_type" value="<?php echo $post_type ?>" />
                            <input type="hidden" name="cat" value="<?php echo $cat ?>" />
                            <input type="hidden" name="current_taxonomy" value="<?php echo $current_taxonomy ?>" />
                            
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'reorder-interface-reset-' . get_current_user_id()) ?>" />
                        </form>
                        
                    </div>
                    <?php
                }

            function listPostType($args = '') 
                {
                    $defaults = array(
                                    'depth' => 0, 'show_date' => '',
                                    'date_format' => get_option('date_format'),
                                    'child_of' => 0, 
                                    'exclude' => '',
                                    'title_li' => __('Pages'), 
                                    'echo' => 1,
                                    'authors' => '', 
                                    'sort_column' => 'menu_order',
                                    'link_before' => '', 
                                    'link_after' => '', 
                                    'walker' => ''
                                );
                    $r = wp_parse_args( $args, $defaults );
                    extract( $r, EXTR_SKIP );

                    $output = '';
                    
                    $is_woocommerce = FALSE;                
                    if ($post_type == "product" && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                        $is_woocommerce = TRUE;
                    
                    // Query pages.
                    $args = array(
                                'post_type'         =>  $post_type,
                                'posts_per_page'    => -1,
                                'orderby'           => 'menu_order',
                                'order'             => 'ASC'

                    );

                    $_filter_posts_where_active = FALSE;
                    
                    //filter a taxonomy term
                    $tax_query = array(); 
                    if ($taxonomy != '')
                        {
                            global $wp_version;
                            
                            if ($cat > 0)
                                {
                                    $tax_query = array(
                                                                array(
                                                                        'taxonomy'  => $taxonomy,
                                                                        'field'     => 'id',
                                                                        'terms'     => $cat
                                                                                )
                                                                );                             
                                    
                                }
                                else
                                {
                                    //retrieve all terms for this taxonomy
                                    $taxonomy_terms = get_terms($taxonomy);
                                    $terms_array = array();
                                    
                                    foreach ($taxonomy_terms as $taxonomy_term)
                                        $terms_array[] = $taxonomy_term->term_id;
                                    
                                    if(count($terms_array) < 1)
                                        $terms_array[] = -1;
                                    
                                    $tax_query = array(
                                                                array(
                                                                        'taxonomy'  => $taxonomy,
                                                                        'field'     => 'id',
                                                                        'terms'     => $terms_array
                                                                                )
                                                                );    
                                }

                        }
                            
                    $args['tax_query'] = $tax_query;
                        
                    //Post status for atatchments
                    if ($post_type == 'attachment')
                        $args['post_status'] = 'any';
                    
                    //limit the returnds only to IDS to prevent memory exhaust
                    $post_type_info = get_post_type_object($post_type);
                    if( $post_type_info->hierarchical === TRUE || ($is_woocommerce === TRUE && $taxonomy == ''))
                        $args['fields'] = 'ids, post_parent';
                        else
                        {
                            $args['fields'] = 'ids';                        
                        }
                        
                    if ($is_woocommerce === TRUE && $taxonomy == '')
                        {
                            $r['depth'] = 0;
                        }
                        else if($is_woocommerce === TRUE)
                        {
                            $args['meta_query'] = array(
                                                            array(
                                                                    'key'       => '_visibility',
                                                                    'value'     => array('visible','catalog'),
                                                                    'compare'   => 'IN'
                                                                )
                                                        );   
                            
                        }
                                                    
                    $custom_query = new WP_Query($args);
                    
                    //remove filters if aplied
                    if ($_filter_posts_where_active === TRUE)
                        remove_filter( 'posts_where', 'apto_filter_posts_where_interval' );
                    
                    $post_types = $custom_query->posts;
                                    
                    if ( !empty($post_types) ) 
                        {
                            //add post_parent attribute
                            $posts_list = array();
                            foreach ($post_types as $post_id)
                                {
                                    
                                }
                            
                            $output = $this->walkTree($post_types, $r['depth'], $r);
                        }
    
                    echo $output;
                }
            
            function walkTree($post_types, $depth, $r) 
                {
                    if ( empty($r['walker']) )
                        {
                            //include the custom walker
                            include_once(CPTPATH . '/include/post_types_walker.php');
                            $walker = new Post_Types_Order_Walker;
                        }
                    else
                        $walker = $r['walker'];

                    $args = array($post_types, $depth, $r);
                    return call_user_func_array(array(&$walker, 'walk'), $args);
                }
        }

    $apto_shortcode_reorder = new apto_shortcode_reorder();

?>