<?php
/*
Template Name: Page de Recherche
*/

	$context = Timber::get_context();
	$context['wp_title'] = 'Procoves - '.__('Recherche','procoves');
	$context['prod_title'] = get_the_title($post->ID);

	Timber::render('searchpage.twig', $context);
