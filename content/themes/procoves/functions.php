<?php
	// define( 'ACF_LITE' , true );
	include_once('inc/advanced-custom-fields/acf.php' );
	include_once('inc/acf-options-page/acf-options-page.php');
	include_once('inc/acf-gallery/acf-gallery.php');

	add_theme_support('post-thumbnails');
	add_theme_support('menus');

	add_filter('get_twig', 'add_to_twig');
	add_filter('timber_context', 'add_to_context');
	add_filter('acf/options_page/settings', 'options_page_settings');
	add_filter( 'searchwp_admin_bar', '__return_false' );

	add_action('wp_enqueue_scripts', 'load_scripts');
	add_action('wp_enqueue_scripts', 'load_styles');

	add_action( 'widgets_init', 'hex_widgets_init' );

	add_action('init', 'removeHeadLinks');
    remove_action('wp_head', 'wp_generator');

	define('THEME_URL', get_template_directory_uri());

	function options_page_settings( $settings )
	{
		$settings['title'] = 'Options';
		return $settings;
	}
 
	function add_to_context($data){
		/* IMAGES */
		$main_logo_id = get_field('main_logo', 'options');
		$data['logo_procoves'] = new TimberImage($main_logo_id);
		$tech_logo_id1 = get_field('tech_logo_noir', 'options');
		$data['logo_pro_noir'] = new TimberImage($tech_logo_id1);
		$tech_logo_id2 = get_field('tech_logo_blanc', 'options');
		$data['logo_pro_blanc'] = new TimberImage($tech_logo_id2);
		$logo_afaq = get_field('normes_iso', 'options');
		$data['logo_afaq'] = new TimberImage($logo_afaq);
		$logo_ce = get_field('norme_ce', 'options');
		$data['CE'] = new TimberImage($logo_ce);
		
		$data['languages'] = icl_get_languages('skip_missing=1');
		
		$data['mode_emploi'] = get_field('guide_pratique', 'options');
		$data['menu'] = new TimberMenu('navigation');
		$data['footer'] = new TimberMenu('footer');

		return $data;
	}

	function add_to_twig($twig){
		// // retrieve our search query and pagination if applicable
		// $query = isset( $_REQUEST['swpquery'] ) ? sanitize_text_field( $_REQUEST['swpquery'] ) : '';
	 	// 	$swppg = isset( $_REQUEST['swppg'] ) ? absint( $_REQUEST['swppg'] ) : 1;

		// // begin SearchWP Supplemental Search Engine results retrieval
		// if( class_exists( 'SearchWP' ) ) {
		// 	// instantiate SearchWP
		// 	$engine = SearchWP::instance();
		// 	$nom = 'nom';
		// 	$ref = 'ref';
		// 	$matieres = 'matieres'; // taken from the SearchWP settings screen
		 
		// 	// perform the search
		// 	$posts = $engine->search( $nom, $query ,$swppg );
		// 	$posts = $engine->search( $ref, $query, $swppg );
		// 	$posts = $engine->search( $matieres, $query ,$swppg );
	 // 		return $posts;
		// }

		/* this is where you can add your own functions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		$twig->addFilter('myfoo', new Twig_Filter_Function('myfoo'));
		return $twig;
	}

	function hex_widgets_init() {
		register_sidebar( array(
			'name' => 'Actualites',
			'id' => 'actu-sidebar',
			'before_widget' => '<div class="widget">',
			'after_widget' => '</div>',
			'before_title' => '<h2><span class="redline"></span>',
			'after_title' => '</h2>',
			) );
	}


	function load_scripts(){
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(),'1.1', true); 
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap-js', THEME_URL . '/js/bootstrap.min.js', array('jquery'), '3.1.0',true);
		wp_enqueue_script( 'flexslider', THEME_URL . '/js/jquery.flexslider-min.js', array('jquery'), '2.2',true);
		wp_enqueue_script( 'site', THEME_URL . '/js/site.js', array('jquery'), '1.0', true);
	}

	function load_styles() {
		wp_enqueue_style( 'bootstrap-style', THEME_URL . '/css/bootstrap.min.css');
		wp_enqueue_style( 'custom', THEME_URL . '/style.css'); 
		wp_enqueue_style( 'mobile', THEME_URL . '/css/responsive.css');
	}


	function removeHeadLinks() {
    	remove_action('wp_head', 'rsd_link');
    	remove_action('wp_head', 'wlwmanifest_link');
    }
   