<?php
/**
 * The template for Archive Post type Produits
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	$context = Timber::get_context();

	$args = array('post_type' => 'produits', 'numberposts' => -1);
	$context['produits'] = Timber::get_posts($args);
	
	// $context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	// $context['normes'] = Timber::get_terms('normes');
	// $context['activite'] = Timber::get_terms('activite');
	// $context['matieres'] = Timber::get_terms('matieres');

	$context['title'] = 'Nos Produits';

	Timber::render('archive-produits.twig', $context);


