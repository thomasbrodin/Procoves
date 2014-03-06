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
	
	$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	$context['normes'] = Timber::get_terms('normes');

	$context['title'] = 'Nos gammes';

	Timber::render('archive-produits.twig', $context);


