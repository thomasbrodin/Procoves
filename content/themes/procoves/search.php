<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */


	$templates = array('search.twig', 'archive.twig', 'index.twig');
	$context = Timber::get_context();

	$context['title'] = __('Resultats de recherche') .' : ' . esc_attr( $_GET['s'] );;
	$args = array(
  		'post_type'=> array('post', 'page'),
  		'numberposts' => -1,
  		's' => $s
	);
	$context['post'] = Timber::get_posts($args);

	$args2 = array(
  		'post_type'=> 'produits',
  		'numberposts' => -1,
  		's' => $s
	);
	$context['produits'] = Timber::get_posts($args2);
	
	Timber::render('search.twig', $context);

