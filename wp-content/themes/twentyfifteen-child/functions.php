<?php

add_action( 'wp_enqueue_scripts', 'twentyfifteen_child_enqueue_styles' );
function twentyfifteen_child_enqueue_styles() {
    $parent_style = 'twentyfifteen-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

add_action( 'pre_get_posts', 'change_home_post_type' );
function change_home_post_type( $query ) {
    if ( $query->is_main_query() && $query->is_home() ) {
        $query->set( 'post_type', array( 'post', 'events' ) );
        $query->set( 'meta_key', 'start_time' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );
    }
}
