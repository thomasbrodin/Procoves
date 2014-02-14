<?php
/*
Plugin Name: HEX Options Page
Description: Create a theme Options Panel
Author: HEX creative
Author URI: http://www.hexcreativenetwork.com
*/


/*

 * Require the framework class before doing anything else, so we can use the defined urls and dirs

 * Also if running on windows you may have url problems, which can be fixed by defining the framework url firs

 */

define('NHP_OPTIONS_URL', site_url('../content/mu-plugins/options/'));

if(!class_exists('NHP_Options')){

	require_once( dirname( __FILE__ ) . '/options/options.php' );

}

add_action('init', 'setup_framework_options', 0);

/*

 * Custom function for filtering the sections array given by theme, good for child themes to override or add to the sections.

 * Simply include this function in the child themes functions.php file.

 * NOTE: the defined constansts for urls, and dir will NOT be available at this point in a child theme, so you must use

 * get_template_directory_uri() if you want to use any of the built in icons

 * Custom function for filtering the args array given by theme, good for child themes to override or add to the args array.

 */

function change_framework_args($args){

	//$args['dev_mode'] = false;
	
	return $args;

}

//function

//add_filter('nhp-opts-args-twenty_eleven', 'change_framework_args');

/*

 * This is the meat of creating the options page

 * Override some of the default values, uncomment the args and change the values

 * - no $args are required, but there there to be over ridden if needed.

 */

function setup_framework_options(){


	$args = array();

	//Set it to dev mode to view the class settings/info in the form - default is false

	$args['dev_mode'] = false;


	//Remove the default stylesheet? make sure you enqueue another one all the page will look whack!

	//$args['stylesheet_override'] = true;


	//Add HTML before the form

	//$args['intro_text'] = __('<p>Don\'t forget to save the settings!</p>', 'nhp-opts');


	//Choose to disable the import/export feature

	$args['show_import_export'] = false;

	//Choose a custom option name for your theme options, the default is the theme name in lowercase with spaces replaced by underscores

	$args['opt_name'] = 'proc';


	//Custom menu icon

	//$args['menu_icon'] = '';

	//Custom menu title for options page - default is "Options"

	$args['menu_title'] = __('Options', 'nhp-opts');

	//Custom Page Title for options page - default is "Options"

	$args['page_title'] = __('Options generales du theme', 'nhp-opts');

	//Custom page slug for options page (wp-admin/themes.php?page=***) - default is "nhp_theme_options"

	$args['page_slug'] = 'proc_options';

	//Custom page capability - default is set to "manage_options"

	//$args['page_cap'] = 'manage_options';

	//page type - "menu" (adds a top menu section) or "submenu" (adds a submenu) - default is set to "menu"

	//$args['page_type'] = 'submenu';

	//parent menu - default is set to "themes.php" (Appearance)

	//the list of available parent menus is available here: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters

	//$args['page_parent'] = 'themes.php';

	//custom page location - default 100 - must be unique or will override other items

	$args['page_position'] = null;

	$args['footer_credit'] = '';

	//Custom page icon class (used to override the page icon next to heading)

	//$args['page_icon'] = 'icon-themes';

	//Want to disable the sections showing as a submenu in the admin? uncomment this line

	//$args['allow_sub_menu'] = false;		

	//Set ANY custom page help tabs - displayed using the new help tab API, show in order of definition		

	// $args['help_tabs'][] = array(

	// 							'id' => 'nhp-opts-1',

	// 							'title' => __('Theme Information 1', 'nhp-opts'),

	// 							'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'nhp-opts')

	// 							);

	// $args['help_tabs'][] = array(

	// 							'id' => 'nhp-opts-2',

	// 							'title' => __('Theme Information 2', 'nhp-opts'),

	// 							'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'nhp-opts')

	// 							);

	//Set the Help Sidebar for the options page - no sidebar by default										

	// $args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'nhp-opts');


	$sections[] = array(

				'title' => __('Images', 'nhp-opts'),

				'desc' => __('<p class="description">Here you can configure the general aspects of the theme.!</p>', 'nhp-opts'),

				//all the glyphicons are included in the options folder, so you can hook into them, or link to your own custom ones.

				//You dont have to though, leave it blank for default.

				'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_062_attach.png',

				'fields' => array(

					array(

						'id' => 'favicon',

						'type' => 'upload',

						'title' => 'Favicon',

						'sub_desc' => 'C\'est le petit icone en tete de votre adresse de navigation'

						),

					array(

						'id' => 'logo',

						'type' => 'upload',

						'title' => 'Logo',

						'sub_desc' => 'Image de marque de l\'entreprise'

						),

					)

				);

	$sections[] = array(

				'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_157_show_lines.png',

				'title' => __('Page d\'accueil', 'nhp-opts'),

				'desc' => __('<p class="description">Texte de presentation de l\'entreprise</p>', 'nhp-opts'),

				'fields' => array(

					array(

						'id' => 'topheader_text',

						'type' => 'textarea',

						'title' => 'Page d\'accueil',

						'sub_desc' => 'Texte court, slogan de presentation de l\'entreprise ',

						'std' => 'Procoves releve le gant de la performance'

						),
			
					)

				);


	$tabs = array();

	global $NHP_Options;

	$NHP_Options = new NHP_Options($sections, $args, $tabs);


}
?>