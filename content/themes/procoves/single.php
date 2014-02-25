<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
if (isset($post->img_prod) && strlen($post->img_prod)){
	$post->img_prod = new TimberImage($post->img_prod);
}
$context['post'] = $post;
$context['wp_title'] .= ' - ' . $post->title();

// $context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
// $context['normes'] = Timber::get_terms('normes');
// $context['activite'] = Timber::get_terms('activite');
// $context['matieres'] = Timber::get_terms('matieres');

Timber::render(array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig'), $context);