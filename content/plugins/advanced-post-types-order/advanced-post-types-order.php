<?php
/*
Plugin Name: Advanced Post Types Order
Plugin URI: http://www.nsp-code.com
Description: Order Post Types Objects using a Drag and Drop Sortable javascript capability
Author: Nsp Code
Author URI: http://www.nsp-code.com 
Version: 2.5.9.9
*/

    define('CPTPATH',   plugin_dir_path(__FILE__));
    define('CPTURL',    plugins_url('', __FILE__));

    define('APTO_VERSION', '2.5.9.9');
    define('APTO_APP_API_URL',      'http://www.nsp-code.com/index.php'); 
    //define('APTO_APP_API_URL',      'http://127.0.0.1/nsp-code/index.php');
    define('APTO_SLUG',      basename(dirname(__FILE__)));
      
    //load language files
    add_action( 'plugins_loaded', 'apto_load_textdomain'); 
    function apto_load_textdomain() 
        {
            load_plugin_textdomain('apto', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang');
        }

    include_once(CPTPATH . '/include/functions.php');
    include_once(CPTPATH . '/include/licence.php'); 
    include_once(CPTPATH . '/include/updater.php'); 
    include_once(CPTPATH . '/include/options.php');
    include_once(CPTPATH . '/include/addons.php');

    register_deactivation_hook(__FILE__, 'CPTO_deactivated');
    register_activation_hook(__FILE__, 'CPTO_activated');

    function CPTO_activated() 
        {
  
        }

    function CPTO_deactivated() 
        {
            
        }
        
    if(is_multisite())
        {
            if(apto_licence_key_verify())
                add_action('admin_menu', 'cpto_plugin_menu', 1);
        }   
        else
        {
            add_action('admin_menu', 'cpto_plugin_menu', 1);
        }
    
    
    function cpto_plugin_menu() 
        {
            add_options_page('Post Types Order', '<img class="menu_pto" src="'. CPTURL .'/images/menu-icon.gif" alt="" />Post Types Order', 'manage_options', 'cpto-options', 'cpt_plugin_options');
        }
        
        
    add_action('admin_print_scripts', 'APTO_admin_scripts');
    function APTO_admin_scripts()
        {
            wp_enqueue_script('jquery'); 
            
            if (!isset($_GET['page']))
                return;
            
            if (isset($_GET['page']) && strpos($_GET['page'], 'order-post-types-') === FALSE)
                return;
                
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
                     
        }
        
    add_action('admin_print_styles', 'APTO_admin_styles');
    function APTO_admin_styles()
        {
                   
            wp_register_style('CPTStyleSheets', CPTURL . '/css/cpt.css');
            wp_enqueue_style( 'CPTStyleSheets');
               
        }
        
    add_action('init', 'APTO_init' );
    function APTO_init()
        {
            //update the default options
            apto_save_default_options();   
            
            if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'cpto-options')
                {
                    if(is_multisite())
                            {
                                //add_action( 'network_admin_menu', 'cpt_optionsUpdate', 1 );
                                add_action( 'wp_loaded', 'cpt_optionsUpdate', 5 );
                            }
                        else
                            {
                                add_action( 'wp_loaded', 'cpt_optionsUpdate', 5 );
                            }
                            
                    add_action( 'admin_head', 'cpt_optionsUpdateMessage', 10 );
                }

        }
        
        
    //WP E-Commerce Order Update
    function apto_posts_orderby_request($orderBy, $query)
        {
            //only for non-admin
            if (is_admin())
                return $orderBy;
                
            //check for WP E-Commerce Taxonomy
            if (!isset($query->query['taxonomy']) || $query->query['taxonomy'] != 'wpsc_product_category')
                return  $orderBy;
            
            //apply only if dragandrop
            $wpec_orderby = get_option( 'wpsc_sort_by' );
            if ($wpec_orderby != "dragndrop")
                return $orderBy;
            
            $options = get_option('cpto_options');
                
            //check if the current post_type is active in the setings
            if (isset($options['allow_post_types']))
                {
                    $_post_type = 'wpsc-product';

                    if (!in_array($_post_type, $options['allow_post_types']))
                        return $orderBy;
                    unset ($_post_type);
                }
             
            $orderby = CPTOrderPosts('', $query);

            return $orderby;
        }       

    function CPTO_pre_get_posts($query)
        {
            //check for the force_no_custom_order param
            if (isset($query->query_vars['force_no_custom_order']) && $query->query_vars['force_no_custom_order'] === TRUE)
                return $query;
                
            $options = get_option('cpto_options');
            if (is_admin() && !defined('DOING_AJAX'))
                {
                    //no need if it's admin interface
                    return $query;   
                }
            //if auto sort    
            if ($options['autosort'] > 0)
                {
                    //check if the current post_type is active in the setings
                    if (isset($options['allow_post_types']) && is_array($options['allow_post_types'])) 
                        {
                            if(isset($query->query_vars['post_type']))
                                {
                                    if (is_array($query->query_vars['post_type']))
                                        {                            
                                            $_post_type = $query->query_vars['post_type'][0];
                                        }
                                        else
                                        {
                                            $_post_type = $query->query_vars['post_type'];
                                        }
                                }
                                else
                                $_post_type = 'post';
                                
                            if($_post_type  == 'any')
                                $_post_type = 'post';

                            if($_post_type == '')
                                {
                                    list($post_type, $taxonomy) = apto_get_query_post_type_taxonomy($query);
                                    
                                    $_post_type = $post_type;
                                    
                                    if($_post_type == '')
                                        $_post_type = 'post';
                                    
                                }
                                
                            if (!in_array($_post_type, $options['allow_post_types']))
                                return $query;
                            
                            unset ($_post_type);
                            
                        }
                    
                    //remove the supresed filters;
                    if (isset($query->query['suppress_filters']))
                        $query->query['suppress_filters'] = FALSE;    
                    
                    //force a suppress filters false, used mainly for get_posts function
                    if (isset($options['ignore_supress_filters']) && $options['ignore_supress_filters'] == "1")
                        $query->query_vars['suppress_filters'] = FALSE;
                        
                    //update the sticky if required or not
                    if (isset($options['ignore_sticky_posts']) && $options['ignore_sticky_posts'] == "1")
                        {
                            if (!isset($query->query_vars['ignore_sticky_posts']))
                                $query->query_vars['ignore_sticky_posts'] = TRUE;
                        }
                }
                
            return $query;
        }

    function CPTOrderPosts($orderBy, $query) 
        {
            //check for the force_no_custom_order param
            if (isset($query->query_vars['force_no_custom_order']) && $query->query_vars['force_no_custom_order'] === TRUE)
                return $orderBy;
                  
            if (apto_is_plugin_active('bbpress/bbpress.php') && isset($query->query_vars['post_type']) && ((is_array($query->query_vars['post_type']) && in_array("reply", $query->query_vars['post_type'])) || ($query->query_vars['post_type'] == "reply")))
                return $orderBy;
            
            
            global $wpdb;
            
            $options = get_option('cpto_options');
                
            //check if it's in the ignore list
            if (isset($options['allow_post_types']) && is_array($options['allow_post_types']))
                {
                    if(isset($query->query_vars['post_type']))
                        {
                            if (is_array($query->query_vars['post_type']))
                                {                            
                                    $_post_type = $query->query_vars['post_type'][0];
                                }
                                else
                                {
                                    $_post_type = $query->query_vars['post_type'];
                                }
                        }
                        else
                        $_post_type = 'post';
                        
                    if(strtolower($_post_type  == 'any'))
                        $_post_type = 'post';

                    if($_post_type == '')
                        {
                            list($post_type, $taxonomy) = apto_get_query_post_type_taxonomy($query);
                            
                            $_post_type = $post_type;
                            
                            if($_post_type == '')
                                $_post_type = 'post';
                            
                        }
                        
                    if (!in_array($_post_type, $options['allow_post_types']))
                        return $orderBy;
                    
                    unset ($_post_type);
                }
            
            
            
            //check if menu_order provided through the query params
            if (isset($query->query['orderby']) && $query->query['orderby'] == 'menu_order')
                {
                    $orderBy = apto_get_orderby($orderBy, $query);
                        
                    return($orderBy);   
                }
            
            $default_orderBy = $orderBy;
            
            if (is_admin() && !defined('DOING_AJAX'))
                    {
                        if (!isset($options['adminsort']) || (isset($options['adminsort']) && $options['adminsort'] == "1"))
                            {
                                //only return custom sort if there is not a column sort
                                if (!isset($_GET['orderby']))
                                    {
                                        //force to use the custom order
                                        $orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date DESC"; 
                                        $orderBy = apto_get_orderby($orderBy, $query);
                                        
                                        if($orderBy == '')
                                            $orderBy = $default_orderBy;
                                    }
                                    
                                return($orderBy);
                            }
                    }
                else
                    {
                        //check if the current post_type is active in the setings
                         if (isset($options['allow_post_types']) && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] != '')
                            {
                                if (is_array($query->query_vars['post_type']))
                                    {
                                        //check if there is at least one post type within the array
                                        if (count($query->query_vars['post_type']) > 0)
                                            {
                                                if(count(array_intersect($options['allow_post_types'], $query->query_vars['post_type'])) < 1)
                                                    return $orderBy;
                                            }
                                    }
                                    else
                                        {
                                            $_post_type = $query->query_vars['post_type']; 
                                            if(strtolower($_post_type)  == 'any')
                                                $_post_type = 'post';
                                            if (!in_array($_post_type, $options['allow_post_types']))
                                                return $orderBy;
                                            unset ($_post_type);
                                        }
                            }
                        
                        //check if is feed
                        if ($query->is_feed())
                            {
                                if (!isset($options['feedsort']) || $options['feedsort'] != "1")
                                    return $orderBy;
                                    
                                //else use the set order
                                $orderBy = apto_get_orderby($orderBy, $query);
                                
                                if($orderBy == '')
                                    $orderBy = $default_orderBy;
                                
                                return($orderBy);
                            }
                        
                        
                        if ($options['autosort'] == "1")
                            {
                                $orderBy = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";  
                                
                                //use the custom order unless there is an auto sort
                                $orderBy = apto_get_orderby($orderBy, $query);
                                
                                if($orderBy == '')
                                    $orderBy = $default_orderBy;
                                    
                                return($orderBy);
                            }
                        if ($options['autosort'] == "2")
                            {
                                //check if the user didn't requested another order
                                if (!isset($query->query['orderby']))
                                    {
                                        //$orderBy = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";  
                                        $orderBy = apto_get_orderby($orderBy, $query);
                                        
                                        if($orderBy == '')
                                            $orderBy = $default_orderBy;   
                                    }
                            }
                    }

            return($orderBy);
        }
    
    function APTO_posts_groupby($groupby, $query) 
        {
            //check for NOT IN taxonomy operator
            if(isset($query->tax_query->queries) && count($query->tax_query->queries) == 1 )
                {
                    if(isset($query->tax_query->queries[0]['operator']) && $query->tax_query->queries[0]['operator'] == 'NOT IN')
                        $groupby = '';
                }
               
            return($groupby);
        }
        
    function APTO_posts_distinct($distinct, $query) 
        {
            //check for NOT IN taxonomy operator
            if(isset($query->tax_query->queries) && count($query->tax_query->queries) == 1 )
                {
                    if(isset($query->tax_query->queries[0]['operator']) && $query->tax_query->queries[0]['operator'] == 'NOT IN')
                        $distinct = 'DISTINCT';
                }
                   
            return($distinct);
        }    

    add_action('wp_loaded', 'init_APTO', 99 );
    function init_APTO() 
        {
	        global $custom_post_type_order, $userdata;
            
            if(!apto_licence_key_verify())
                return;
                
            add_filter('posts_orderby_request', 'apto_posts_orderby_request', 99, 2);
            add_filter('posts_groupby', 'APTO_posts_groupby', 99, 2);
            add_filter('posts_distinct', 'APTO_posts_distinct', 99, 2);
            
            //disabled since 2.5.9.2 for alternative
            //add_action('wp_insert_post', 'apto_wp_insert_post', 10, 2);
            add_filter('apto_get_order_list', 'sticky_posts_apto_get_order_list', 10, 2);
            
            //make sure the vars are set as default
            $options = get_option('cpto_options');

            //compare if the version require update
            if (!isset($options['code_version']) || $options['code_version'] == '')
                {
                    $options['code_version'] = 0.1;
                    if (!isset($options['autosort']))
                        $options['autosort'] = '1';
                        
                    if (!isset($options['adminsort']))
                        $options['adminsort'] = '1';
                        
                    if (!isset($options['capability']))
                        $options['capability'] = 'install_plugins';
                                    
                    update_option('cpto_options', $options);
                }
                
            if (version_compare( strval( APTO_VERSION ), $options['code_version'] , '>' ) === TRUE )
                {
                    //update the tables
                    cpto_create_plugin_tables();
                    
                    //update the plugin version
                    $options['code_version'] = APTO_VERSION;
                    update_option('cpto_options', $options);
                }

            if (is_admin())
                {
                    
                    if(isset($options['capability']) && !empty($options['capability']))
                        {
                            if(current_user_can($options['capability']))
                                {
                                    include(CPTPATH . '/include/reorder-class.php');
                                    $custom_post_type_order = new ACPTO();   
                                }
                        }
                    else if (is_numeric($options['level']))
                        {
                            if (userdata_get_user_level(true) >= $options['level'])
                                {
                                    include(CPTPATH . '/include/reorder-class.php');
                                $custom_post_type_order = new ACPTO();
                                }    
                        }
                        else
                            {
                                include(CPTPATH . '/include/reorder-class.php');
                                $custom_post_type_order = new ACPTO();  
                            }
                                            
                    //backwards compatibility
                    if( !isset($options['apto_tables_created']))
                        {
                            cpto_create_plugin_tables();   
                        }
                }
                else
                {
                    add_filter('wp', 'apto_wp');
                }
                
            //bbpress reverse option check
            if (isset($options['bbpress_replies_reverse_order']) && $options['bbpress_replies_reverse_order'] == "1")
                add_filter('bbp_before_has_replies_parse_args', 'apto_bbp_before_has_replies_parse_args' );
            
                
            if (isset($options['autosort']) &&  $options['autosort'] == '1') 
                {
                    add_filter('get_next_post_where', 'cpto_get_next_post_where', 10, 3);
                    add_filter('get_next_post_sort', 'cpto_get_next_post_sort');

                    add_filter('get_previous_post_where', 'cpto_get_previous_post_where', 10, 3); 
                    add_filter('get_previous_post_sort', 'cpto_get_previous_post_sort');
                }      
        }

?>