<?php

    function cpt_plugin_options()
    {
        $options = get_option('cpto_options');
        
        if(!apto_licence_key_verify() && !is_multisite())
            {
                if(apto_licence_form());
                return;
            }
            
        if(!apto_licence_key_verify() && is_multisite())
            {
                if(apto_licence_multisite_require_nottice());
                return;
            }
                          
                    ?>
                      <div class="wrap"> 
                        <div id="icon-settings" class="icon32"></div>
                            <h2><?php _e( "General Settings", 'apto' ) ?></h2>
                           
                           <?php  
                                
                                if(!is_multisite())
                                    apto_licence_deactivate_form();  
                           ?>
                           
                            <form id="form_data" name="form" method="post">   
                                <br />
                                <h2 class="subtitle"><?php _e( "Allow reorder", 'apto' ) ?></h2>                              
                                <table class="form-table">
                                    <tbody>
   
                                               <?php
                                                
                                                    //get all defined post types
                                                    $all_post_types =   get_post_types();
                                                    $ignore = array (
                                                                        'revision',
                                                                        'nav_menu_item'
                                                                        );
                                                    
                                                    
                                                    if (apto_is_plugin_active('bbpress/bbpress.php'))
                                                        {
                                                            $ignore = array_merge($ignore, array( 'reply', 'forum'));
                                                        }                    
                                                    
                                                    foreach ($all_post_types as $post_type)
                                                        {
                                                            $post_type_data = get_post_type_object ( $post_type );
                                                            if (in_array($post_type, $ignore))
                                                                continue;
                                                            
                                                            ?>
                                                                <tr valign="top">
                                                                    <th scope="row"></th>
                                                                    <td>
                                                                    <label><input type="checkbox" <?php if (!isset($options['allow_post_types']) 
                                                                    || (is_array($options['allow_post_types']) && in_array($post_type, $options['allow_post_types']))) {echo ' checked="checked"';} ?> value="<?php echo $post_type ?>" name="allow_post_types[]"> <?php echo $post_type_data->label ?></label>          
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                        }
                                                ?>
                                    </tbody>
                                </table>
                                
                                <br />
                                <h2 class="subtitle"><?php _e( "General", 'apto' ) ?></h2>                              
                                <table class="form-table">
                                    <tbody>
                            
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Minimum Capability to use this plugin", 'apto' ) ?></label></th>
                                            <td>
                                                <?php
                                                
                                                    $roles_capability = array(
                                                                                'Subscriber'                =>    array(
                                                                                                                            'title'         =>  __('Subscriber', 'apto'),
                                                                                                                            'capability'    =>  'read'
                                                                                                                            ),
                                                                                'Contributor'               =>    array(
                                                                                                                            'title'         =>  __('Contributor', 'apto'),
                                                                                                                            'capability'    =>  'edit_posts'
                                                                                                                            ),
                                                                                'Author'                    =>    array(
                                                                                                                            'title'         =>  __('Author', 'apto'),
                                                                                                                            'capability'    =>  'publish_posts'
                                                                                                                            ),
                                                                                'Editor'                    =>    array(
                                                                                                                            'title'         =>  __('Editor', 'apto'),
                                                                                                                            'capability'    =>  'publish_pages'
                                                                                                                            ),
                                                                                'Administrator'             =>    array(
                                                                                                                            'title'         =>  __('Administrator', 'apto'),
                                                                                                                            'capability'    =>  'switch_themes'
                                                                                                                            )                                                                                                                                                             
                                                                                );
                                                    $roles_capability = apply_filters('apto_get_roles_capability', $roles_capability);
                                                
                                                ?>
                                                <select id="role" name="capability">
                                                    <?php
                                                    
                                                        foreach($roles_capability as $role_default_name => $role_info)
                                                            {
                                                                ?><option value="<?php echo $role_info['capability'] ?>" <?php 
                                                                    if (
                                                                            (isset($options['capability']) && $options['capability'] == $role_info['capability']) ||
                                                                            //make default select for Administrator when no capability was previously set
                                                                            ((!isset($options['capability']) || empty($options['capability'])) & $role_info['capability'] == 'install_plugins')
                                                                        )
                                                                        echo 'selected="selected"';
                                                                    
                                                                ?>><?php echo $role_info['title'] ?></option><?php
                                                            }
                                                    
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Auto Sort", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                
                                                <input type="radio" <?php if (isset($options['autosort']) && $options['autosort'] == "0") {echo ' checked="checked"';} ?> value="0" name="autosort">
                                                <?php _e("<b>OFF</b> - If checked, you will need to manually update the queries to use the menu_order", 'apto') ?>.</label>
                                                
                                                <p><a href="javascript:;" onclick="jQuery('#example0').slideToggle();;return false;"><?php _e( "Show Example", 'apto' ) ?></a></p>
                                                <div id="example0" style="display: none">
                                                
                                                <p class="example"><br /><?php _e('You must include a \'orderby\' parameter with value as \'menu_order\'', 'apto') ?>:</p>
                                                <pre class="example">
$args = array(
              'post_type' => 'feature',
              'orderby'   => 'menu_order',
              'order'     => 'ASC'
            );

$my_query = new WP_Query($args);
while ($my_query->have_posts())
    {
        $my_query->the_post();
        (..your code..)          
    }
</pre>
                                                
                                                </div>
                                                
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="radio" <?php if (isset($options['autosort']) && $options['autosort'] == "1") {echo ' checked="checked"';} ?> value="1" name="autosort">
                                                <?php _e("<b>ON</b> - If checked, the plug-in will automatically update the wp-queries to use the new order (<b>No code update is necessarily</b>).", 'apto') ?>.</label>
                                                
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"></th>
                                            <td>
                                                <label for="users_can_register">
                                                
                                                <input type="radio" <?php if (isset($options['autosort']) && $options['autosort'] == "2") {echo ' checked="checked"';} ?> value="2" name="autosort">
                                                <?php _e("<b>ON/Custom</b> - If checked, the plug-in will automatically update the wp-queries to use the new order, but if a query already contain a 'orderby' parameter then this will be used instead.", 'apto') ?>.</label>
                                                
                                                <p><a href="javascript:;" onclick="jQuery('#example2').slideToggle();;return false;"><?php _e( "Show Example", 'apto' ) ?></a></p>
                                                <div id="example2" style="display: none">
                                                
                                                <p class="example"><br /><?php _e('The following code will return the posts ordered by title', 'apto') ?>:</p>
                                                <pre class="example">
$args = array(
              'post_type' => 'feature',
              'orderby'   => 'title',
              'order'     => 'ASC'
            );

$my_query = new WP_Query($args);
while ($my_query->have_posts())
    {
        $my_query->the_post();
        (..your code..)          
    }
</pre>
                                                
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Ignore Sticky Posts", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['ignore_sticky_posts']) && $options['ignore_sticky_posts'] == "1") {echo ' checked="checked"';} ?> value="1" name="ignore_sticky_posts">
                                                <?php _e("Ignore Sticky Posts when Auto Sort is ON.", 'apto') ?>.</label>
                                                <p><?php _e( "You can overwrite this from code using the 'ignore_sticky_posts' within your query", 'apto' ) ?> <a href="javascript:;" onclick="jQuery('#example3').slideToggle();;return false;"><?php _e( "Show Example", 'apto' ) ?></a></p>
                                                <div id="example3" style="display: none">
                                                
                                                <p class="example"><br /><?php _e('The following code will return the Stiky posts first even if the Autosort is ON', 'apto') ?>:</p>
                                                <pre class="example">
$args = array(
              'post_type'           => 'feature',
              'ignore_sticky_posts' =>  TRUE
            );

$my_query = new WP_Query($args);
while ($my_query->have_posts())
    {
        $my_query->the_post();
        (..your code..)          
    }
</pre>
                                                
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Admin Sort", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['adminsort']) && $options['adminsort'] == "1") {echo ' checked="checked"';} ?> value="1" name="adminsort">
                                                <?php _e("To update the admin interface and see the post types per your new sort, this need to be checked", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Send new items to bottom", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['new_items_to_bottom']) && $options['new_items_to_bottom'] == "1") {echo ' checked="checked"';} ?> value="1" name="new_items_to_bottom"> 
                                                <?php _e("All new posts / custom types will append at the end instead top. This will apply when manual ordering.", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Feed Sort", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['feedsort']) && $options['feedsort'] == "1") {echo ' checked="checked"';} ?> value="1" name="feedsort">
                                                <?php _e("Use defined order when gernerate a feed. Leave unchecked to use the default date order", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Toggle Thumbnails", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['always_show_thumbnails']) && $options['always_show_thumbnails'] == "1") {echo ' checked="checked"';} ?> value="1" name="always_show_thumbnails">
                                                <?php _e("Always show the Thumbnails within the re-order interface", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "Ignore Suppress Filters", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['ignore_supress_filters']) && $options['ignore_supress_filters'] == "1") {echo ' checked="checked"';} ?> value="1" name="ignore_supress_filters">
                                                <?php _e("Set FALSE the <b>suppress_filters</b> for get_posts() default function. Use this feature if Autosort will not work with you, otherwise you should leave un-checked.", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        
                                        <?php if (is_plugin_active('bbpress/bbpress.php')) { ?>
                                        <tr valign="top">
                                            <th scope="row" style="text-align: right;"><label><?php _e( "bbPress Replies", 'apto' ) ?></label></th>
                                            <td>
                                                <label for="users_can_register">
                                                <input type="checkbox" <?php if (isset($options['bbpress_replies_reverse_order']) && $options['bbpress_replies_reverse_order'] == "1") {echo ' checked="checked"';} ?> value="1" name="bbpress_replies_reverse_order">
                                                <?php _e("Reverse the order of bbPress replies, show newest posts first", 'apto') ?>.</label>
                                            </td>
                                        </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>
                                                   
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Settings', 'apto') ?>">
                               </p>
                            
                                <input type="hidden" name="apto_form_submit" value="true" />
                                
                            </form>
                                                        
                    <?php  
            echo '</div>';   
        
        
    }

?>