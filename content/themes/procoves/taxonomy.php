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

$qobj = get_queried_object();
$term_name = $qobj->name;

if (is_tax('gammes')){
	$term_ID = $qobj->term_id;
	$context['gammes'] = array(get_term_by('id', $term_ID, 'gammes'));
	$context['wp_title'] .= ' - '.$term_name;
	$context['title'] = $term_name;
} else if (is_tax('normes')){
	$context['title'] = __( 'Normes ' ) .$term_name;
	$context['wp_title'] .= __( 'Normes: ' ) .$term_name;
} else if (is_tax('activite')){
	$context['title'] = __( 'Secteur d\'activité ' ) .$term_name;
	$context['wp_title'] .= __( ' - Secteur d\'activité: ' ) . $term_name;
} else if (is_tax('matieres')){
	$context['title'] = __( 'Matieres ' ) .$term_name;
	$context['wp_title'] .= __( ' - Matieres: ' ) . $term_name;
} 

$context['link'] = get_term_link( $qobj );

Timber::render($templates, $context);
