<?php
/**
 * The template for displaying all pages.
 *
 * 
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$context ['subpages'] =  get_pages(array( 'child_of' => $post->ID, 'sort_column' => 'menu_order')); 
Timber::render(array('page-' . $post->post_name . '.twig', 'page.twig'), $context);