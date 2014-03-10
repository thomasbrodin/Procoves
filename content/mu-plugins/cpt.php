<?php
/*
Plugin Name: HEX Custom Post Types
Description: Custom Post Types for HEX websites.
Author: HEX creative
Author URI: http://www.hexcreativenetwork.com
*/


add_action( 'init', 'hex_cpt' );
add_action( 'init', 'produits_taxonomies' );  
add_action( 'init','maybe_rewrite_rules' );

function hex_cpt() {
  $labels  = array(
            'name' => 'Produits',
            'singular_name' => 'Produit',
            'add_new_item'      => __( 'Ajouter un nouveau produit' ),
            'edit_item'          => __( 'Modifier le produit'),
            'all_items'          => __( 'Tous les produits'),
            'view_item'          => __( 'Voir ce produit'),
            'search_items'       => __( 'Rechercher produits'),
            'not_found'          => __( 'Aucun produit trouvé'),
            'not_found_in_trash' => __( 'Aucun produit trouvé dans le corbeille'),
            'parent_item_colon'  => '',
            'menu_name'          => 'Produits',
            );
  $args = array(
        'labels' => $labels,
        'description' => 'Procoves Industrie',
        'menu_icon'=> 'dashicons-portfolio',
        'public' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 0,
        'has_archive' => true,
        'supports' => array( 'title', 'revisions',),
        'rewrite' => false 
      );
  register_post_type( 'produits', $args);
}

function produits_taxonomies() {  
  register_taxonomy(  
    'gammes',  
    'produits',
      array( 
          'hierarchical' => true,  
          'labels' => array('name' => 'Gammes', 'add_new_item' => __( 'Ajouter une nouvelle gamme' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => false  
      )  
    );  
  register_taxonomy(  
    'normes',  
    'produits',
      array( 
          'hierarchical' => true,  
          'labels' => array('name' => 'Normes', 'add_new_item' => __( 'Ajouter une nouvelle norme' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => false 
      )  
    );  
  register_taxonomy(  
    'activite',  
    'produits',
      array( 
          'hierarchical' => true,  
          'labels' => array('name' => 'Secteurs d\'activité', 'add_new_item' => __( 'Ajouter une nouveau secteur d\'activité' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => false  
      )  
    );  
  register_taxonomy(  
    'matieres',  
    'produits',
      array( 
          'hierarchical' => true,
          'labels' => array('name' => 'Matieres', 'add_new_item' => __( 'Ajouter une nouvelle matiere' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => false  
      )  
    ); 
  register_taxonomy(  
    'mots_cles',  
    'produits',
      array( 
          'hierarchical' => false,
          'labels' => array('name' => 'Mots Clés', 'add_new_item' => __( 'Ajouter un nouveau Mot-clé' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => array('slug' => 'tag')  
      )  
    ); 
}  