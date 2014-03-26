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
        'capability_type' => 'post',
        'hierarchical' => false,
        'query_var' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 0,
        'has_archive' => true,
        'exclude_from_search' => false,
        'supports' => array( 'title', 'revisions',),
        'rewrite' => array(
                        'slug' => 'produits'
                    ),
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
           'rewrite' => array(
                    'slug' => 'gammes'
                )
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
          'rewrite' => array(
                    'slug' => 'normes'
                )
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
          'rewrite' => array(
                    'slug' => 'activite'
                )  
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
          'rewrite' => array(
                    'slug' => 'matieres'
                )
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
  
 
    function maybe_rewrite_rules() {
     
      $ver = filemtime( __FILE__ ); // Get the file time for this file as the version number
      $defaults = array( 'version' => 0, 'time' => time() );
      $r = wp_parse_args( get_option( __CLASS__ . '_flush', array() ), $defaults );
     
      if ( $r['version'] != $ver || $r['time'] + 172800 < time() ) { // Flush if ver changes or if 48hrs has passed.
        flush_rewrite_rules();
        // trace( 'flushed' );
        $args = array( 'version' => $ver, 'time' => time() );
        if ( ! update_option( __CLASS__ . '_flush', $args ) )
          add_option( __CLASS__ . '_flush', $args );
      }
     
    }
}  