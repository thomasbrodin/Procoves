<?php
/*
Template Name: Page de Recherche
*/

	$context = Timber::get_context();

	$q_nom = isset( $_REQUEST['noms'] ) ? sanitize_text_field( $_REQUEST['noms'] ) : '';
	$q_ref = isset( $_REQUEST['ref'] ) ? sanitize_text_field( $_REQUEST['ref'] ) : '';
	$q_mat = isset( $_REQUEST['mat'] ) ? sanitize_text_field( $_REQUEST['mat'] ) : '';
	$q_act = isset( $_REQUEST['act'] ) ? sanitize_text_field( $_REQUEST['act'] ) : '';	

	if( class_exists( 'SearchWP' ) ) {
	    // instantiate SearchWP
	    $engine = SearchWP::instance();

		if (!empty ($q_nom) && empty ($q_ref) && empty ($q_mat) && empty ($q_act)){
	    	$produits = $engine->search( 'nom', $q_nom );
		}
	   
		elseif (empty ($q_nom) && !empty ($q_ref) && empty ($q_mat) && empty ($q_act)) {
	 	   	$produits = $engine->search( 'ref', $q_ref);
		}

		elseif (empty ($q_nom) && empty ($q_ref) && !empty ($q_mat) && empty ($q_act)) {
	 	    $produits = $engine->search( 'mat', $q_mat);
		}

		elseif (empty ($q_nom) && empty ($q_ref) && empty ($q_mat) && !empty ($q_act)) {
	 	    $produits = $engine->search( 'act', $q_act);
		}
    }  

	$context['produits'] = Timber::get_posts($produits);

	$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	$context['normes'] = Timber::get_terms('normes', array('parent' => 0));

	Timber::render('search.twig', $context);
