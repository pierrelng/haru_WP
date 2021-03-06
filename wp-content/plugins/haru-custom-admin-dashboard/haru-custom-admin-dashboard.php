<?php

/**
* Plugin Name: Haru Custom Admin Dashboard
* Description: Display tweaks for the Wordpress dashboard.
* Author: Pierre Lange
* Version: 1.0
*/

// Add custom columns in Events dashboard and sort them ----------------------
// ---------------------------------------------------------------------------
    // http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
    // https://www.ractoon.com/2016/11/wordpress-custom-sortable-admin-columns-for-custom-posts/
    // https://code.tutsplus.com/articles/quick-tip-make-your-custom-column-sortable--wp-25095

add_filter( 'manage_events_posts_columns', 'haru_custom_events_columns' );
function haru_custom_events_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'cover' => __( 'Cover' ),
		'title' => __( 'Nom' ),
    'venue' => __( 'Venue' ),
		'start_time' => __( 'Début de l\'event' ),
		'location' => __( 'Location' ),
		// 'date' => __( 'Date d\'ajout' )
	);
	return $columns;
}

add_action( 'manage_events_posts_custom_column', 'haru_manage_events_columns', 10, 2 );
function haru_manage_events_columns( $column, $post_id ) {
	global $post;
	switch( $column ) {
		case 'start_time' :
			$start_time = get_post_meta( $post_id, 'start_time', true );
			if (empty($start_time)) {
				echo __('Inconnu');
			} else {
				$datetime = new DateTime($start_time);
				$value = $datetime->format('D d/m/y - H:i');
				printf($value);
			}
			break;
		case 'venue' :
			$acf = get_post_meta( $post_id, 'place_name', true );
      printf($acf);
			break;
		case 'location' :
			$city = get_post_meta( $post_id, 'location_city', true );
			$country = get_post_meta( $post_id, 'location_country', true );
			if (empty($city)) {
				$city = 'Inconnu';
			}
			if (empty($country)) {
				$country = 'Inconnu';
			}
			printf($city.', '.$country);
			break;
		case 'cover' :
      $cover = get_post_meta( $post_id, 'cover_source', true );
      $facebook_event_url = get_post_meta( $post_id, 'facebook_event_url', true );
      if (empty($cover)) {
        $cover = 'Pas de cover';
        printf($cover);
        break;
      }
      echo '
      <a href="'.$facebook_event_url.'" target="_blank">
        <div style="
          height: 90px;
          width: 160px;
          background-image: url('.$cover.');
          background-repeat: no-repeat;
          background-position: center;
          background-size: cover;
        "></div>
      </a>';
			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-events_sortable_columns', 'haru_set_custom_events_sortable_columns' );
function haru_set_custom_events_sortable_columns( $columns ) {
	$columns['start_time'] = 'start_time';
	$columns['location'] = 'location';
	return $columns;
}

add_action('admin_head', 'haru_admin_column_width'); // https://wordpress.stackexchange.com/a/85045
function haru_admin_column_width() {
    echo '<style type="text/css">
    .column-cover { width:165px !important; }
    .column-title { text-align: left; width:400px !important; overflow:hidden }
    .column-start_time { text-align: left; width:200px !important; overflow:hidden }
		.column-location { text-align: left; width:125px !important; overflow:hidden }
    </style>';
}

add_action( 'pre_get_posts', 'haru_events_custom_orderby' ); // https://wordpress.stackexchange.com/a/85045
function haru_events_custom_orderby( $query ) {
	if ( ! is_admin() )
		return;

	$orderby = $query->get( 'orderby');

	if ( 'start_time' == $orderby ) {
		$query->set( 'meta_key', 'start_time' );
		$query->set( 'orderby', 'meta_value' );
	}

	if ( 'location' == $orderby ) {
		$query->set( 'meta_key', 'location_city' );
		$query->set( 'orderby', 'meta_value' );
	}
}

?>
