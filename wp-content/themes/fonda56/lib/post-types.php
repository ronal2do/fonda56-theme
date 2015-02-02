<?php
//declare here your post types and taxonomies

// Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                => _x( 'Offerte', 'Post Type General Name', 'roots' ),
		'singular_name'       => _x( 'Offerta', 'Post Type Singular Name', 'roots' ),
		'menu_name'           => __( 'Offerte', 'roots' ),
		'parent_item_colon'   => __( 'Genitore:', 'roots' ),
		'all_items'           => __( 'Tutte le offerte', 'roots' ),
		'view_item'           => __( 'Vedi offerta', 'roots' ),
		'add_new_item'        => __( 'Aggiungi offerta', 'roots' ),
		'add_new'             => __( 'Aggiungi nuova offerta', 'roots' ),
		'edit_item'           => __( 'Modifica offerta', 'roots' ),
		'update_item'         => __( 'Aggiorna offerta', 'roots' ),
		'search_items'        => __( 'Cerca offerta', 'roots' ),
		'not_found'           => __( 'Non trovato', 'roots' ),
		'not_found_in_trash'  => __( 'Non trovato nel cestino', 'roots' ),
	);
	$rewrite = array(
		'slug'                => 'offerta',
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	$args = array(
		'label'               => __( 'offerta', 'roots' ),
		'description'         => __( 'Offerte settimanali', 'roots' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => $rewrite,
		'capability_type'     => 'page',
	);
	register_post_type( 'offerta', $args );


	$labels = array(
		'name'                => _x( 'Punti vendita', 'Post Type General Name', 'roots' ),
		'singular_name'       => _x( 'Punto vendita', 'Post Type Singular Name', 'roots' ),
		'menu_name'           => __( 'Punti vendita', 'roots' ),
		'parent_item_colon'   => __( 'Punti vendita:', 'roots' ),
		'all_items'           => __( 'Tutti punti vendita', 'roots' ),
		'view_item'           => __( 'Vedi punto vendita', 'roots' ),
		'add_new_item'        => __( 'Aggiungi punto vendita', 'roots' ),
		'add_new'             => __( 'Aggiungi nuovo punto vendita', 'roots' ),
		'edit_item'           => __( 'Modifica punto vendita', 'roots' ),
		'update_item'         => __( 'Aggiorna punto vendita', 'roots' ),
		'search_items'        => __( 'Cerca punto vendita', 'roots' ),
		'not_found'           => __( 'Non trovato', 'roots' ),
		'not_found_in_trash'  => __( 'Non trovato nel cestino', 'roots' ),
	);
	$rewrite = array(
		'slug'                => 'punto-vendita',
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	$args = array(
		'label'               => __( 'Punto vendita', 'roots' ),
		'description'         => __( 'punti vendita', 'roots' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => $rewrite,
		'capability_type'     => 'page',
	);
	register_post_type( 'punti-vendita', $args );

}

// Hook into the 'init' action
add_action( 'init', 'custom_post_type', 0 );

//query recupero offerte in ordine custom
//http://fonda56:8888/wp-json/posts?type[]=offerta&filter[posts_per_page]=-1&filter[order]=menu_order

//query recupero ultime 5 news
//http://fonda56:8888/wp-json/posts?type[]=post&filter[posts_per_page]=5&filter[order]=DESC

?>