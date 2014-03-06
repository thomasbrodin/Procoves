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
	$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	$context['normes'] = Timber::get_terms('normes');

	$termname = $qobj->name;
	if (is_tax('gammes')){
		$context['title'] = $termname;
	} else if (is_tax('normes')){
		$context['title'] = 'Normes&nbsp;-&nbsp;'.$termname;
	} else if (is_tax('activite')){
		$context['title'] = 'Secteur D\'activite&nbsp;-&nbsp;'.$termname;
	} else if (is_tax('matieres')){
		$context['title'] = 'Matieres&nbsp;-&nbsp;'.$termname;
	} 
	$context['link'] = get_term_link( $qobj );

	Timber::render('taxonomy.twig', $context);
