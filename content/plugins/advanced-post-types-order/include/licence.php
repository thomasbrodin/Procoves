<?php   
        
        define('APTO_PRODUCT_ID',           'APTO');
        define('APTO_SECRET_KEY',           '*#ioK@ud8*&#2');
        $protocols = array ("https://" , "http://");
        define('APTO_INSTANCE',             str_replace($protocols, "", network_site_url()));
        
        eval(base64_decode('YWRkX2FjdGlvbignd3BfbG9hZGVkJywgJ2FwdG9fbGljZW5jZV93cF9sb2FkZWQnLCA5OCApOwogICAgICAgIGZ1bmN0aW9uIGFwdG9fbGljZW5jZV93cF9sb2FkZWQoKSAKICAgICAgICAgICAgewogICAgICAgICAgICAgICAgaWYoYXB0b19saWNlbmNlX2tleV92ZXJpZnkoKSkKICAgICAgICAgICAgICAgICAgICBhZGRfZmlsdGVyKCdwcmVfZ2V0X3Bvc3RzJywgJ0NQVE9fcHJlX2dldF9wb3N0cycpOwogICAgICAgICAgICAgICAgaWYoYXB0b19saWNlbmNlX2tleV92ZXJpZnkoKSkKICAgICAgICAgICAgICAgICAgICBhZGRfZmlsdGVyKCdwb3N0c19vcmRlcmJ5JywgJ0NQVE9yZGVyUG9zdHMnLCA5OSwgMik7CgogICAgICAgICAgICAgICAgaWYoIWFwdG9fbGljZW5jZV9rZXlfdmVyaWZ5KCkpCiAgICAgICAgICAgICAgICAgICAgYWRkX2FjdGlvbignYWRtaW5fbm90aWNlcycsICdhcHRvX2FkbWluX25vdGljZXMnKTsgICAKICAgICAgICAgICAgfQ=='));

        function apto_admin_notices()
            {
                if ( !current_user_can('manage_options'))
                    return;
                
                $screen = get_current_screen();
                    
                if(is_multisite())
                    {
                        ?><div class="updated fade"><p><?php _e( "Advanced Post Types Order plugin is inactive, please enter your", 'apto' ) ?> <a href="<?php echo network_admin_url() ?>settings.php?page=cpto-options"><?php _e( "Licence Key", 'apto' ) ?></a></p></div><?php
                    }
                    else
                    {
                        if(isset($screen->id) && $screen->id == 'settings_page_cpto-options')
                            return;
                        
                        ?><div class="updated fade"><p><?php _e( "Advanced Post Types Order plugin is inactive, please enter your", 'apto' ) ?> <a href="options-general.php?page=cpto-options"><?php _e( "Licence Key", 'apto' ) ?></a></p></div><?php
                    }
            }
        
        add_action( 'network_admin_menu', 'apto_network_admin_menu' );
        function apto_network_admin_menu()
            {
                if(!apto_licence_key_verify())
                    add_submenu_page('settings.php', 'Post Types Order', '<img class="menu_pto" src="'. CPTURL .'/images/menu-icon.gif" alt="" />Post Types Order', 'manage_options', 'cpto-options', 'apto_licence_form');
                    else
                    add_submenu_page('settings.php', 'Post Types Order', '<img class="menu_pto" src="'. CPTURL .'/images/menu-icon.gif" alt="" />Post Types Order', 'manage_options', 'cpto-options', 'apto_licence_deactivate_form');
            }
        
        add_action('init', 'apto_licence_deactivation_check');
        function apto_licence_deactivation_check()
            {
                if(!apto_licence_key_verify())
                    return;
                
                $license_data = get_site_option('apto_license');
                
                if(isset($license_data['last_check']))
                    {
                        if(time() < ($license_data['last_check'] + 86400))
                            {
                                return;
                            }
                    }
                
                $license_key = $license_data['kye'];
                $args = array(
                                            'sl_action'         => 'status-check',
                                            'licence_key'       => $license_key,
                                            'product_id'        => APTO_PRODUCT_ID,
                                            'secret_key'        => APTO_SECRET_KEY,
                                            'sl_instance'          => APTO_INSTANCE
                                        );
                $request_uri    = APTO_APP_API_URL . '?' . http_build_query( $args , '', '&');
                $data           = wp_remote_get( $request_uri );
                
                if(is_wp_error( $data ) || $data['response']['code'] != 200)
                    return;   
                
                $data_body = json_decode($data['body']);
                if(isset($data_body->status))
                    {
                        if($data_body->status == 'success')
                            {
                                if($data_body->status_code == 's203' || $data_body->status_code == 's204')
                                    {
                                        $license_data['kye']          = '';
                                    }
                            }
                            
                        if($data_body->status == 'error')
                            {
                                $license_data['kye']          = '';
                            } 
                    }
                
                $license_data['last_check']   = time();    
                update_site_option('apto_license', $license_data);
                
            }
        
        function apto_licence_key_verify()
            {
                $license_data = get_site_option('apto_license');
                         
                if(!isset($license_data['kye']) || $license_data['kye'] == '')
                    return FALSE;
                    
                return TRUE;
            }
            
            
        function apto_licence_form_submit()
            {
                global $apto_form_submit_messages; 
                
                //check for de-activation
                if (isset($_POST['apto_licence_form_submit']) && isset($_POST['apto_licence_deactivate']) && wp_verify_nonce($_POST['apto_license_nonce'],'apto_license'))
                    {
                        global $apto_form_submit_messages;
                        
                        $license_data = get_site_option('apto_license');                        
                        $license_key = $license_data['kye'];

                        //build the request query
                        $args = array(
                                            'sl_action'         => 'deactivate',
                                            'licence_key'       => $license_key,
                                            'product_id'        => APTO_PRODUCT_ID,
                                            'secret_key'        => APTO_SECRET_KEY,
                                            'sl_instance'          => APTO_INSTANCE
                                        );
                        $request_uri    = APTO_APP_API_URL . '?' . http_build_query( $args , '', '&');
                        $data           = wp_remote_get( $request_uri );
                        
                        if(is_wp_error( $data ) || $data['response']['code'] != 200)
                            {
                                $apto_form_submit_messages[] .= __('There was a problem connecting to ', 'apto') . APTO_APP_API_URL;
                                return;  
                            }
                            
                        $data_body = json_decode($data['body']);
                        if(isset($data_body->status))
                            {
                                if($data_body->status == 'success' && $data_body->status_code == 's201')
                                    {
                                        //the license is active and the software is active
                                        $apto_form_submit_messages[] = $data_body->message;
                                        
                                        $license_data = get_site_option('apto_license');
                                        
                                        //save the license
                                        $license_data['kye']          = '';
                                        $license_data['last_check']   = time();
                                        
                                        update_site_option('apto_license', $license_data);
                                    }
                                    else
                                    {
                                        $apto_form_submit_messages[] = __('There was a problem deactivating the licence: ', 'apto') . $data_body->message;
                                        
                                        //if message code is e104  force de-activation
                                        if ($data_body->status_code == 'e102' || $data_body->status_code == 'e104')
                                            {
                                                 $license_data = get_site_option('apto_license');
                                        
                                                //save the license
                                                $license_data['kye']          = '';
                                                $license_data['last_check']   = time();
                                                
                                                update_site_option('apto_license', $license_data);
                                            }
                                        
                                        return;
                                    }   
                            }
                            else
                            {
                                $apto_form_submit_messages[] = __('There was a problem with the data block received from ' . APTO_APP_API_URL, 'apto');
                                return;
                            }
                            
                        return;
                    }   
                
                
                
                if (isset($_POST['apto_licence_form_submit']) && wp_verify_nonce($_POST['apto_license_nonce'],'apto_license'))
                    {
                        
                        $license_key = isset($_POST['license_key'])? trim($_POST['license_key']) : '';

                        if($license_key == '')
                            {
                                $apto_form_submit_messages[] = __('Licence Key can\'t be empty', 'apto');
                                return;
                            }
                            
                        //build the request query
                        $args = array(
                                            'sl_action'         => 'activate',
                                            'licence_key'       => $license_key,
                                            'product_id'        => APTO_PRODUCT_ID,
                                            'secret_key'        => APTO_SECRET_KEY,
                                            'sl_instance'          => APTO_INSTANCE
                                        );
                        $request_uri    = APTO_APP_API_URL . '?' . http_build_query( $args , '', '&');
                        $data           = wp_remote_get( $request_uri );
                        
                        if(is_wp_error( $data ) || $data['response']['code'] != 200)
                            {
                                $apto_form_submit_messages[] .= __('There was a problem connecting to ', 'apto') . APTO_APP_API_URL;
                                return;  
                            }
                            
                        $data_body = json_decode($data['body']);
                        if(isset($data_body->status))
                            {
                                if($data_body->status == 'success' && $data_body->status_code == 's200')
                                    {
                                        //the license is active and the software is active
                                        $apto_form_submit_messages[] = $data_body->message;
                                        
                                        $license_data = get_site_option('apto_license');
                                        
                                        //save the license
                                        $license_data['kye']          = $license_key;
                                        $license_data['last_check']   = time();
                                        
                                        update_site_option('apto_license', $license_data);

                                    }
                                    else
                                    {
                                        $apto_form_submit_messages[] = __('There was a problem activating the licence: ', 'apto') . $data_body->message;
                                        return;
                                    }   
                            }
                            else
                            {
                                $apto_form_submit_messages[] = __('There was a problem with the data block received from ' . APTO_APP_API_URL, 'apto');
                                return;
                            }
                    }   
                
            }
            
        function apto_licence_form()
            {
                ?>
                    <div class="wrap"> 
                        <div id="icon-settings" class="icon32"></div>
                        <h2><?php _e( "General Settings", 'apto' ) ?></h2>
                        
                        
                        <form id="form_data" name="form" method="post">
                            <h2 class="subtitle"><?php _e( "Software License", 'apto' ) ?></h2>
                            <div class="postbox">
                                
                                    <?php wp_nonce_field('apto_license','apto_license_nonce'); ?>
                                    <input type="hidden" name="apto_licence_form_submit" value="true" />
                                       
                                    

                                     <div class="section section-text ">
                                        <h4 class="heading">License Key</h4>
                                        <div class="option">
                                            <div class="controls">
                                                <input type="text" value="" name="license_key" class="text-input">
                                            </div>
                                            <div class="explain"><?php _e( "Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from", 'apto' ) ?> <a href="http://www.nsp-code.com/premium-plugins/my-account/" target="_blank">My Account</a><br />
                                            <?php _e( "More keys can be generate from", 'apto' ) ?> <a href="http://www.nsp-code.com/premium-plugins/my-account/" target="_blank">My Account</a> 
                                            </div>
                                        </div> 
                                    </div>

                                
                            </div>
                            
                            <p class="submit">
                                <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save', 'apto') ?>">
                            </p>
                        </form> 
                    </div> 
                <?php  
 
            }
        
        function apto_licence_deactivate_form()
            {
                $license_data = get_site_option('apto_license');
                
                if(is_multisite())
                    {
                        ?>
                            <div class="wrap"> 
                                <div id="icon-settings" class="icon32"></div>
                                <h2><?php _e( "General Settings", 'apto' ) ?></h2>
                        <?php
                    }
                
                ?>
                    <div id="form_data">
                    <h2 class="subtitle"><?php _e( "Software License", 'apto' ) ?></h2>
                    <div class="postbox">
                        <form id="form_data" name="form" method="post">    
                            <?php wp_nonce_field('apto_license','apto_license_nonce'); ?>
                            <input type="hidden" name="apto_licence_form_submit" value="true" />
                            <input type="hidden" name="apto_licence_deactivate" value="true" />

                             <div class="section section-text ">
                                <h4 class="heading"><?php _e( "License Key", 'apto' ) ?></h4>
                                <div class="option">
                                    <div class="controls">
                                        <p><b><?php echo substr($license_data['kye'], 0, 20) ?>-xxxxxxxx-xxxxxxxx</b> &nbsp;&nbsp;&nbsp;<a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').submit();">Deactivate</a></p>
                                    </div>
                                    <div class="explain"><?php _e( "You can generate more keys from", 'apto' ) ?> <a href="http://www.nsp-code.com/premium-plugins/my-account/" target="_blank">My Account</a> 
                                    </div>
                                </div> 
                            </div>
                         </form>
                    </div>
                    </div> 
                <?php  
 
                if(is_multisite())
                    {
                        ?>
                            </div>
                        <?php
                    }
            }
            
        function apto_licence_multisite_require_nottice()
            {
                ?>
                    <div class="wrap"> 
                        <div id="icon-settings" class="icon32"></div>
                        <h2><?php _e( "General Settings", 'apto' ) ?></h2>

                        <h2 class="subtitle"><?php _e( "Software License", 'apto' ) ?></h2>
                        <div id="form_data">
                            <div class="postbox">
                                <div class="section section-text ">
                                    <h4 class="heading">License Key Required!</h4>
                                    <div class="option">
                                        <h4>cdscsdcsd sd</h4>
                                        <div class="explain"><?php _e( "Enter your License Key that you got when bought this product. If you lost the key, you can always retrieve that from", 'apto' ) ?> <a href="http://www.nsp-code.com/premium-plugins/my-account/" target="_blank">My Account</a><br />
                                        <?php _e( "You can generate more keys from", 'apto' ) ?> <a href="http://www.nsp-code.com/premium-plugins/my-account/" target="_blank">My Account</a> 
                                        </div>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div> 
                <?php
            
            }    

    
?>