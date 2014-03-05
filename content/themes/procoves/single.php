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
$context['actu'] = Timber::get_post('category_name=a-la-une');
$context['email'] = get_field('email', 'options');
$context['adresse'] = get_field('adresse', 'options');
$context['sidebar'] = Timber::get_sidebar('sidebar.php');
$context['fiche'] = get_field('fiche_tech');

Timber::render(array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig'), $context);