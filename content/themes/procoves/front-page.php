<?php
/**
 * The template for Front Page
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */
	global $proc;
	if (!class_exists('Timber')){
		echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';
	}

	$context = Timber::get_context();
	$context['wp_title'] .= ' Industrie - ' . get_bloginfo( 'description' );

	$args = array(
		'post_type' => 'produits', 
		'post_status' => 'publish',
		'numberposts' => 12,
		'orderby' => 'menu_order',
		'order'         => 'ASC',
		'suppress_filters' => false,
		'tax_query' => array(
            array(
                'taxonomy' => 'mots_cles',
                'field' => 'slug',
                'terms' => array( 'vedette')
            ),
        )
	);
	$context['produits']  = Timber::get_posts($args);

	$context['actu'] = Timber::get_post('category_name=a-la-une');
	$context['email'] = get_field('email', 'options');
	$context['adresse'] = get_field('adresse', 'options');

	$frontID = get_option('page_on_front');
	$childs = get_pages( array( 'child_of' => $frontID,'sort_column' => 'menu_order') );

	$context['concep'] = Timber::get_post($childs[0]->ID);
	$context['fab'] = Timber::get_post($childs[1]->ID);
	$context['preco'] = Timber::get_post($childs[2]->ID);
	
	$context['images'] = get_field('home_slide');
	
	Timber::render('front-page.twig', $context);


