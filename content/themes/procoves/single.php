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
$context['breadcrumbs'] = wp_get_object_terms($post->ID, 'gammes');
$context['title'] = $post->title();

$post_id = get_the_ID();
$terms = wp_get_post_terms($post_id, 'gammes');
$term_slugs = array_map(function($item) {
    			return $item->slug;
			}, $terms);
$args = array(
	'post_type' => 'produits', 
	'post_status' => 'publish',
	'posts_per_page' => 4,
	'post__not_in' => array($post_id),
    'tax_query' => array(
        array(
            'taxonomy' => 'gammes',
            'field' => 'slug',
            'terms' => $term_slugs,
            'operator' => 'IN'
        )
    )
);

$context['fiche'] = get_field('fiche_tech');

$context['similaires'] = Timber::get_posts($args);

$context['actu'] = Timber::get_post('category_name=a-la-une');
$context['email'] = get_field('email', 'options');
$context['adresse'] = get_field('adresse', 'options');

$context['sidebar'] = Timber::get_sidebar('sidebar.php');

Timber::render(array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig'), $context);