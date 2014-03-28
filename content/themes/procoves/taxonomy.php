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

$templates = array('taxonomy.twig', 'archive.twig', 'index.twig');

$context['prod_title'] = __('Nos produits');
$context['prod_url'] = "/produits"; 

$qobj = $wp_query->get_queried_object();
$termname = $qobj->name;
if (is_tax('gammes')){
	$context['title'] = $termname;
	$context['wp_title'] .= ' - '.$termname;
} else if (is_tax('normes')){
	$context['title'] = __( 'Normes: ' ) .$termname;
	$context['wp_title'] .= __( ' - Normes' ) .$termname;
} else if (is_tax('activite')){
	$context['title'] = __( 'Secteur d\'activité: ' ) .$termname;
	$context['wp_title'] .= __( ' - Secteur d\'activité:' ) . $termname;
} else if (is_tax('matieres')){
	$context['title'] = __( 'Matieres:' ) . $termname;
	$context['wp_title'] .= __( ' - Matieres: ' ) . $termname;
} 
$context['link'] = get_term_link( $qobj );

Timber::render($templates, $context);
