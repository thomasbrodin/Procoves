<?php
/*
Plugin Name: HEX Custom Post Types
Description: Custom Post Types for HEX websites.
Author: HEX creative
Author URI: http://www.hexcreativenetwork.com
*/


add_action( 'init', 'hex_cpt' );
add_action( 'init', 'produits_taxonomies' );  

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
            'menu_name'          => 'Produits'

            );
  $args = array(
        'labels' => $labels,
        'description' => 'Procoves Industrie',
        'menu_icon'=> 'dashicons-portfolio',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'supports' => array( 'title', 'editor', 'thumbnail', 'revisions',),
        'rewrite' => array( 'slug' => 'portfolio', 'with_front' => false ),
      );
  register_post_type( 'produits', $args);
}

function produits_taxonomies() {  
  register_taxonomy(  
    'gammes',  
    'produits',
      array( 
          'hierarchical' => true,  
          'labels' => array('name' => 'Gammes', 'add_new_item' => __( 'Ajouter une nouvelle Gamme' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => array('slug' => 'gammes')  
      )  
    ); 
  register_taxonomy(  
    'matieres',  
    'produits',
      array( 
          'hierarchical' => true,
          'labels' => array('name' => 'Matieres', 'add_new_item' => __( 'Ajouter une nouvelle Matiere' )),
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => array('slug' => 'matieres')  
      )  
    );  
  register_taxonomy(  
    'produit-tag',  
    'produits',
      array( 
          'hierarchical' => false,  
          'label' => 'Mots-clés', 
          'show_admin_column' => true, 
          'query_var' => true,  
          'rewrite' => array('slug' => 'produit-tag')  
      )  
    );  
}  
