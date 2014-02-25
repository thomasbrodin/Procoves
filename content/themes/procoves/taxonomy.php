<?php
/**
 * The template for Taxonomy 
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	$context = Timber::get_context();

	$qobj = get_queried_object();
	$args = array(
		'post_type' => 'produits', 
		'numberposts' => -1 ,
		'tax_query' => array(
			        array(
			          'taxonomy' => $qobj->taxonomy,
			          'field' => 'slug', 
			       	  'terms' => $qobj->name
			        )
     	 ),
	);
	$context['produits'] = Timber::get_posts($args);

	$context['title'] = 'Nos Produits';
		if (is_tax('gammes')){
			$context['title'] = 'Gammes ';
		} else if (is_tax('normes')){
			$context['title'] = 'Normes';
		} else if (is_tax('activite')){
			$context['title'] = 'Secteur D\'activite';
		} else if (is_tax('matieres')){
			$context['title'] = 'Matieres';
		} 

	Timber::render('taxonomy.twig', $context);
