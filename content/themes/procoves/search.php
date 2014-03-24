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

	$context['title'] = 'Resultats de recherche : '. get_search_query();

	$context['produits'] = Timber::get_posts();
	
	$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	$context['normes'] = Timber::get_terms('normes', array('parent' => 0));

	Timber::render($templates, $context);
