<?php
	add_theme_support('post-formats');
	add_theme_support('post-thumbnails');
	add_theme_support('menus');

	add_filter('get_twig', 'add_to_twig');
	add_filter('timber_context', 'add_to_context');

	add_action('wp_enqueue_scripts', 'load_scripts');
	add_action('wp_enqueue_scripts', 'load_styles');

	add_action('init', 'removeHeadLinks');
    remove_action('wp_head', 'wp_generator');

	define('THEME_URL', get_template_directory_uri());

	$proc = get_option('proc');

	function add_to_context($data){
		/* this is where you can add your own data to Timber's context object */
		$data['qfd'] = 'value set in function';
		$data['menu'] = new TimberMenu();
		return $data;
	}

	function add_to_twig($twig){
		/* this is where you can add your own functions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		$twig->addFilter('myfoo', new Twig_Filter_Function('myfoo'));
		return $twig;
	}

	function myfoo($text){
    	$text .= ' bar!';
    	return $text;
	}

	function load_scripts(){
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(),'1.1', true); 
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap-js', THEME_URL . '/js/bootstrap.min.js', array('jquery'), '3.1.0',true);
		wp_enqueue_script( 'site', THEME_URL . '/js/site.js', array('jquery'), '1.0', true);
	}

	function load_styles() {
		wp_enqueue_style( 'bootstrap-style', THEME_URL . '/css/bootstrap.min.css');
		wp_enqueue_style( 'custom', THEME_URL . '/style.css'); 
	}

	function removeHeadLinks() {
    	remove_action('wp_head', 'rsd_link');
    	remove_action('wp_head', 'wlwmanifest_link');
    }
   