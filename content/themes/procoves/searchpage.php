<?php
/*
Template Name: Page de Recherche
*/

	$context = Timber::get_context();
	$context['prod_title'] = get_the_title($post->ID);
	Timber::render('searchpage.twig', $context);
