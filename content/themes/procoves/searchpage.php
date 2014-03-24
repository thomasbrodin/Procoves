<?php
/*
Template Name: Page de Recherche
*/

	$context = Timber::get_context();

	$q_nom = isset( $_REQUEST['nom'] ) ? sanitize_text_field( $_REQUEST['nom'] ) : '';
	$q_ref = isset( $_REQUEST['ref'] ) ? sanitize_text_field( $_REQUEST['ref'] ) : '';
	$q_mat = isset( $_REQUEST['mat'] ) ? sanitize_text_field( $_REQUEST['mat'] ) : '';
	$q_act = isset( $_REQUEST['act'] ) ? sanitize_text_field( $_REQUEST['act'] ) : '';	

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
     
    if (( !empty ($q_nom) || !empty ($q_ref) || !empty ($q_mat) || !empty ($q_act))
    	&&  class_exists ( 'SearchWP' )) {
		$context['produits'] = Timber::get_posts($produits);
	}
	$context['title'] = 'Resultats de recherche : '. get_search_query();

	$context['gammes'] = Timber::get_terms('gammes', array('parent' => 0));
	$context['normes'] = Timber::get_terms('normes', array('parent' => 0));

	Timber::render('searchpage.twig', $context);
