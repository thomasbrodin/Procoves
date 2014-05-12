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

$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); 
$term_name = $term->name;
$context['taxs']= array($term);

if (is_tax('gammes')){ 
	if ($term->parent == 0) {  
		$context['taxs']= array($term);
	} else {
		$parent = get_term($term->parent, get_query_var('taxonomy') );
		$context['taxs'] = array($parent, $term);
	}
	$context['wp_title'] .= 'Procoves - '.$term_name;
	$context['title'] = $term_name;
} else if (is_tax('normes')){
	$context['title'] = __( 'Norme ' ) .$term_name;
	$context['wp_title'] .= 'Procoves -' .__( 'Norme: ' ) .$term_name;
} else if (is_tax('activite')){
	$context['title'] = __( 'Secteur d\'activité ' ) .$term_name;
	$context['wp_title'] .= 'Procoves -' .__( ' - Secteur d\'activité: ' ) . $term_name;
} else if (is_tax('matieres')){
	$context['title'] = __( 'Matieres ' ) .$term_name;
	$context['wp_title'] .= 'Procoves -' .__( ' - Matieres: ' ) . $term_name;
} 

Timber::render($templates, $context);
