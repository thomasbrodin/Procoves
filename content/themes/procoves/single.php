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
$context['prod_title'] = __('Nos produits');
$context['prod_url'] = get_post_type_archive_link( 'produits'); 

$context['breadcrumbs'] = wp_get_object_terms($post->ID, 'gammes', array('orderby' => 'parent menu_order', 'order' => 'DESC'));
$context['title'] = $post->title();

$context['fiche'] = get_field('fiche_tech');

$post_id = get_the_ID();

$terms_gammes = wp_get_post_terms($post_id, 'gammes', array('orderby' => 'parent menu_order', 'order' => 'DESC'));
$terms_gammes_values = array_map(function($item) {
                return $item->slug;
            }, $terms_gammes);
$terms_gammes_slugs = implode('+', $terms_gammes_values);

$terms_normes = wp_get_post_terms($post_id, 'normes', array('orderby' => 'parent menu_order', 'order' => 'DESC'));
$terms_normes_values = array_map(function($item) {
                return $item->slug;
            }, $terms_normes);
$terms_normes_slugs = implode('+', $terms_normes_values);

$context['gammes'] = wp_get_object_terms($post->ID, 'gammes', array('orderby' => 'parent menu_order', 'order' => 'DESC'));

$args = array(
    'post_type' => 'produits', 
    'post_status' => 'publish',
    'posts_per_page' => 4,
    'orderby' => 'menu_order',
    'order'         => 'ASC',
    'suppress_filters' => false,
    'post__not_in' => array($post_id),
    'gammes' => $terms_gammes_slugs,
    'normes' => $terms_normes_slugs
);

$context['similaires'] = Timber::get_posts($args);

$context['actu'] = Timber::get_post('category_name=a-la-une');
$context['email'] = get_field('email', 'options');
$context['adresse'] = get_field('adresse', 'options');

$context['sidebar'] = Timber::get_sidebar('sidebar.php');

Timber::render(array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig'), $context);