<?php

/**
* Plugin Name: Haru Custom API Functions
* Description: Self-explanatory.
* Author: Pierre Lange
* Version: 1.0
*/


// Show metadata when GET /wp-json/wp/v2/events
// --------------------------------------------
register_rest_field( 'events', 'details', array(
    'get_callback' => function ( $data ) {
		// var_dump($data);
        return get_post_meta( $data['id'] );
    }
));


// Custom endpoints :
// ------------------
// POST /wp-json/haru/v1/events
// GET  /wp-json/haru/v1/organizers/facebook
// GET  /wp-json/haru/v1/venues/facebook
// GET  /wp-json/haru/v1/venues/unwanted
// GET  /wp-json/haru/v1/events/facebook

add_action( 'rest_api_init', 'haru_register_routes' );
function haru_register_routes() {
    register_rest_route( 'haru/v1', 'events', array(
        'methods'  => WP_REST_Server::CREATABLE,
        'callback' => 'haru_insert_events',
    ) );
	register_rest_route( 'haru/v1', 'organizers/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_organizers_facebook'
    ) );
    register_rest_route( 'haru/v1', 'venues/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_venues_facebook'
    ) );
    register_rest_route( 'haru/v1', 'venues/unwanted', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_venues_unwanted'
    ) );
	register_rest_route( 'haru/v1', 'events/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_events_facebook'
    ) );
}

/**
 * Generate results for the /wp-json/haru/v1/events route.
 *
 * @param WP_REST_Request $request Full details about the request.
 *
 * @return WP_REST_Response|WP_Error The response for the request.
 */
function haru_insert_events( WP_REST_Request $request ) {
	// get sent json body
	$item = $request->get_json_params();

	if (
		empty( $item['name'] ) ||
		empty( $item['description'] ) ||
		empty( $item['id'] ) ||
		empty( $item['start_time'] ) ||
		empty( $item['end_time'] ) ||
		// empty( $item['place']['name'] ) ||
		// empty( $item['place']['id'] ) ||
		// empty( $item['place']['location']['country'] ) ||
		// empty( $item['place']['location']['city'] ) ||
		// empty( $item['place']['location']['latitude'] ) ||
		// empty( $item['place']['location']['longitude'] ) ||
		// empty( $item['place']['location']['street'] ) ||
		// empty( $item['place']['location']['zip'] ) ||
		empty( $item['cover']['source'] ) ||
		empty( $item['cover']['height'] ) ||
		empty( $item['cover']['width'] )
	) {
		return new WP_Error( 'missing_data', 'Incomplete json data', array( 'status' => 400 ) );
	}

	// vars
	$my_post = array(
		'post_title'	=> $item['name'],
		'post_type'		=> 'events',
		'post_status'	=> 'pending'
	);
	// insert the post into the database
	$post_id = wp_insert_post( $my_post );

	// save Facebook link
	$field_key = "field_5975ec6ef72b2";
	$value = "https://www.facebook.com/events/".$item['id'];
	update_field( $field_key, $value, $post_id );

	// save description
	$field_key = "field_5964cbd27567d";
	$value = $item['description'];
	update_field( $field_key, $value, $post_id );

	// save Dateheure début
	$field_key = "field_5975ebaaf72b0";
	$myDateTime = new DateTime($item['start_time']);
	$value = $myDateTime->format('Y-m-d H:i:s'); // https://support.advancedcustomfields.com/forums/topic/date-time-picker-and-post-loop/
	// return $value;
	update_field( $field_key, $value, $post_id );

	// save Dateheure fin
	$field_key = "field_5975ec47f72b1";
	$myDateTime = new DateTime($item['end_time']);
	$value = $myDateTime->format('Y-m-d H:i:s');
	update_field( $field_key, $value, $post_id );

	// Id meta
	add_post_meta( $post_id, 'facebook_id', $item['id'] );

	// Place metas
	add_post_meta( $post_id, 'place_name', $item['place']['name'] );
	add_post_meta( $post_id, 'place_id', $item['place']['id'] );
	add_post_meta( $post_id, 'location_country', $item['place']['location']['country'] );
	add_post_meta( $post_id, 'location_city', $item['place']['location']['city'] );
	add_post_meta( $post_id, 'location_latitude', $item['place']['location']['latitude'] );
	add_post_meta( $post_id, 'location_longitude', $item['place']['location']['longitude'] );
	add_post_meta( $post_id, 'location_street', $item['place']['location']['street'] );
	add_post_meta( $post_id, 'location_zip', $item['place']['location']['zip'] );

	// Cover metas
	add_post_meta( $post_id, 'cover_source', $item['cover']['source'] );
	add_post_meta( $post_id, 'cover_height', $item['cover']['height'] );
	add_post_meta( $post_id, 'cover_width', $item['cover']['width'] );

    // Return either a WP_REST_Response or WP_Error object
    // return $post_id;
	return new WP_REST_Response($post_id, 200);
}

function haru_get_organizers_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'organizers'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'facebook_page'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_venues_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'venues'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'facebook_page'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_venues_unwanted() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'venues'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'type'
		AND ($wpdb->postmeta.meta_value LIKE '%public%' OR $wpdb->postmeta.meta_value LIKE '%culturel%')
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_events_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'events'
		AND $wpdb->postmeta.meta_key = 'facebook_event_url'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

?>
