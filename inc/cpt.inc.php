<?php

if ( ! function_exists('charty') ) {
	// Register Custom Post Type
	function charty() {
		$labels = array(
			'name'                  => _x( 'charty', 'Post Type General Name', 'charty' ),
			'singular_name'         => _x( 'charty', 'Post Type Singular Name', 'charty' ),
			'menu_name'             => __( 'charty', 'charty' ),
			'name_admin_bar'        => __( 'charty', 'charty' ),
			'parent_item_colon'     => __( '', 'charty' ),
			'all_items'             => __( 'All charts', 'charty' ),
			'add_new_item'          => __( 'Add a new chart', 'charty' ),
			'add_new'               => __( 'Add new', 'charty' ),
			'new_item'              => __( 'New chart', 'charty' ),
			'edit_item'             => __( 'Edit chart', 'charty' ),
			'update_item'           => __( 'Update chart', 'charty' ),
			'view_item'             => __( 'View chart', 'charty' ),
			'search_items'          => __( 'Search chart', 'charty' ),
			'not_found'             => __( 'Not found', 'charty' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'charty' ),
			'items_list'            => __( 'chart list', 'charty' ),
			'items_list_navigation' => __( 'chart list navigation', 'charty' ),
			'filter_items_list'     => __( 'Filter chart list', 'charty' ),
		);
		$args = array(
			'label'                 => __( 'charty', 'charty' ),
			'description'           => __( 'Used to manage your charts.', 'charty' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-chart-pie',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'charty', $args );
	}
	add_action( 'init', 'charty', 0 );
}
