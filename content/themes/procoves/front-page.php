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
	
	$args = array(
		'post_type' => 'produits', 
		'numberposts' => 12,
		'tax_query' => array(
            array(
                'taxonomy' => 'mots_cles',
                'field' => 'slug',
                'terms' => array( 'vedette' )
            ),
        )
	);
	$context['produits'] = Timber::get_posts($args);
	
	$context['actu'] = Timber::get_post('category_name=a-la-une');

	$frontID = get_option('page_on_front');
	$childs = get_pages( array( 'child_of' => $frontID,'sort_column' => 'menu_order') );

	$context['concep'] = Timber::get_post($childs[0]->ID);
	$context['fab'] = Timber::get_post($childs[1]->ID);
	$context['preco'] = Timber::get_post($childs[2]->ID);
	
	$context['images'] = get_field('home_slide');
	$context['slogan'] = get_field('accroche');
	
	Timber::render('front-page.twig', $context);


