<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	if (!class_exists('Timber')){
		echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';
	}
	$context = Timber::get_context();
	$args = array('post_type' => array('post'), 'numberposts' => -1);
	$context['posts'] = Timber::get_posts($args);
	$context['sidebar'] = Timber::get_sidebar('sidebar.php');
	$templates = array('index.twig');
	if (is_home()){
		$context['actu_title'] = 'Actualites';
		$context['wp_title'] = 'Procoves - Actualites';
		array_unshift($templates, 'home.twig');
	}

	Timber::render($templates, $context);


