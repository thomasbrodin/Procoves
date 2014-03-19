<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.2
 */

	$templates = array('archive.twig', 'index.twig');

	$context = Timber::get_context();

	$context['title'] = 'Archive';
	if (is_day()){
		$context['posts'] = Timber::get_posts();
		$context['sidebar'] = Timber::get_sidebar('sidebar.php');
		$context['title'] = 'Archive: '.get_the_date( 'D M Y' );
		$context['wp_title'] .= ' - Archives';
	} else if (is_month()){
		$context['posts'] = Timber::get_posts();
		$context['sidebar'] = Timber::get_sidebar('sidebar.php');
		$context['title'] = 'Archive: '.get_the_date( 'M Y' );
		$context['wp_title'] .= ' - Archives';
	} else if (is_year()){
		$context['posts'] = Timber::get_posts();
		$context['sidebar'] = Timber::get_sidebar('sidebar.php');
		$context['title'] = 'Archive: '.get_the_date( 'Y' );
		$context['wp_title'] .= ' - Archives';
	} else if (is_tag()){
		$context['posts'] = Timber::get_posts();
		$context['sidebar'] = Timber::get_sidebar('sidebar.php');
		$context['title'] = single_tag_title('', false);
		$context['wp_title'] .= ' - Archives';
	} else if (is_category()){
		$context['posts'] = Timber::get_posts();
		$context['sidebar'] = Timber::get_sidebar('sidebar.php');
		$context['title'] = single_cat_title('', false);
		$context['wp_title'] .= ' - Archives';
		array_unshift($templates, 'archive-'.get_query_var('cat').'.twig');
	} else if (is_post_type_archive()){
		$context['title'] = post_type_archive_title('Nos ', false);
		$context['wp_title'] .= ' - ' . post_type_archive_title('Nos ', false);
		$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
		$context['normes'] = Timber::get_terms('normes', array('parent' => 0));
		array_unshift($templates, 'archive-'.get_post_type().'.twig');
	}	

	Timber::render($templates, $context);
