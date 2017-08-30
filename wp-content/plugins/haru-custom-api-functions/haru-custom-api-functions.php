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
  register_rest_route( 'haru/v1', 'acftag/change', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_change_acf_tags'
  ) );
  register_rest_route( 'haru/v1', 'acftag/cut', array(
    'methods'  => WP_REST_Server::CREATABLE,
    'callback' => 'haru_cut_acf_tag_to_new_field'
  ) );
  register_rest_route( 'haru/v1', 'events/tags/post', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_post_events_WPtags'
  ) );
  register_rest_route( 'haru/v1', 'events/cover', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_get_events_cover'
  ) );
  register_rest_route( 'haru/v1', 'events/cover/manual', array(
    'methods'  => WP_REST_Server::CREATABLE,
    'callback' => 'haru_manual_event_add_cover'
  ) );
  register_rest_route( 'haru/v1', 'events/cover/update/manual', array(
    'methods'  => WP_REST_Server::EDITABLE,
    'callback' => 'haru_manual_event_update_cover'
  ) );
  register_rest_route( 'haru/v1', 'events', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_get_events'
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
		AND $wpdb->postmeta.meta_value LIKE '%public%'
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

function haru_change_acf_tags( WP_REST_Request $request ) {

    $post_type = $request['post_type'];
    $tag_request = $request['tag'];
    $field_name = $request['fieldname'];
    $replacement = $request['replacement'];
    if (empty($tag_request) || empty($field_name) || empty($post_type)) {
        return new WP_Error( 'haru_empty_parameters', 'Missing parameters', array( 'status' => 400 ) );
    }
    $post_ids = get_posts(array(
        'post_type' => $post_type,
        'post_status' => array('any', 'trash'), // any = tous les post_status sauf 'trash'
        'fields' => 'ids',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => $field_name,
                'value' => $tag_request,
                'compare' => 'LIKE'
            )
        )
    ));
    $log_ids = [];
    if( $post_ids ) {
        $count = 0;
        $errors = [];
        foreach ($post_ids as $id) {
            $field = get_field_object($field_name, $id);
            $field_key = $field['key'];
            $tags = $field['value'];
            if (($key = array_search($tag_request, $tags)) !== FALSE) {
                $tags[$key] = $replacement;
            }
            $value = update_field($field_key, $tags, $id);
            if ($value) {
                $updated_field = get_field_object($field_name, $id);
                $count++;
                $log_ids[] = $id;
            } else {
                $errors[] = $id;
            }
        }
        return 'Succeeded : '.$count.'. Failed : '.count($errors).'. Error Details : '.json_encode($errors).' Logs : '.json_encode($log_ids);
    } else {
        return new WP_Error( 'haru_no_matches', 'No matches for '.$tag_request.' and '.$field_name, array( 'status' => 400 ) );
    }
}

function haru_post_events_WPtags() {

    $post_ids = get_posts(array(
        'post_type' => 'events',
        'post_status' => 'publish',
        'posts_per_page'=> -1,
        'fields' => 'ids'
    ));
    if ($post_ids) {
        $count = 0;
        foreach ($post_ids as $id) {
            $fields = get_fields($id);
            $tags = [];
            foreach ($fields as $key => $value) {
                if (substr( $key, 0, 4 ) === "tag_" && !empty($value)) {
                    if (is_array($value)) {
                        foreach ($value as $tag) {
                            $tags[] = str_replace("_"," ", $tag);
                        }
                    } else {
                        $tags[] = str_replace("_"," ", $value);
                    }
                }
            }
            $res = wp_set_post_tags($id, $tags);
            if ($res && !empty($res)) {
                $count++;
            } else {
                return new WP_Error( 'haru_error', 'Error on post '.$id, array( 'status' => 400 ) );
            }
        }
        return 'Total posts ids in WP : '.count($post_ids).'. Inserted tags on '.$count.' posts.';
    } else {
        return new WP_Error( 'haru_no_matches', 'No posts ids', array( 'status' => 400 ) );
    }
}

function haru_get_events_cover() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'events'
        AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'cover_source'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_manual_event_add_cover( WP_REST_Request $request ) {

    $post_id = $request['post_id'];
    $source = $request['source'];
    $height = $request['height'];
    $width = $request['width'];

    if (
        empty( $post_id ) ||
        empty( $source ) ||
        empty( $height ) ||
        empty( $width )
    ) {
        return new WP_Error( 'missing_data', 'Incomplete json data', array( 'status' => 400 ) );
    }

    add_post_meta( $post_id, 'cover_source', $source );
	add_post_meta( $post_id, 'cover_height', $height );
	add_post_meta( $post_id, 'cover_width', $width );

    return new WP_REST_Response('done', 200);
}

function haru_manual_event_update_cover( WP_REST_Request $request ) {

    $post_id = $request['post_id'];
    $source = $request['source'];
    $height = $request['height'];
    $width = $request['width'];

    if (
        empty( $post_id ) ||
        empty( $source ) ||
        empty( $height ) ||
        empty( $width )
	) {
		return new WP_Error( 'missing_data', 'Incomplete json data', array( 'status' => 400 ) );
	}

    update_post_meta( $post_id, 'cover_source', $source );
    update_post_meta( $post_id, 'cover_height', $height );
    update_post_meta( $post_id, 'cover_width', $width );

    return new WP_REST_Response('done', 200);
}

function haru_cut_acf_tag_to_new_field( WP_REST_Request $request ) {

    $json = $request->get_json_params();
    $old_field_name = $request['old_field_name'];
    $tag_request = $request['tag_request'];
    $new_field_key = $request['new_field_key'];

    $log_object = (object)[];
    $log_object->old_field_name = $old_field_name;
    $log_object->tag_request = $tag_request;
    $log_object->new_field_key = $new_field_key;

    $post_ids = get_posts(array(
        'post_type' => 'events',
        'post_status' => array('any', 'trash'),
        'fields' => 'ids',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => $old_field_name,
                'value' => $tag_request,
                'compare' => 'LIKE'
            )
        )
    ));

    if ($post_ids) {
        $count_old = 0;
        $count_new = 0;
        foreach ($post_ids as $id) {
            $old_field = get_field_object($old_field_name, $id);
            $old_field_key = $old_field['key'];
            $old_field_tags = $old_field['value'];
            if (!empty($old_field_tags) && ($key = array_search($tag_request, $old_field_tags)) !== FALSE) {
                unset($old_field_tags[$key]);
                $updated_tags = array_values($old_field_tags);
                if (!empty($old_field_tags)) {
                    update_field($old_field_key, $updated_tags, $id);
                    $log_object->old_field->update[] = $id;
                    $count_old++;
                } else {
                    delete_field($old_field_key, $id);
                    $log_object->old_field->delete[] = $id;
                    $count_old++;
                }
                $new_field = get_field_object($new_field_key, $id);
                $new_field_tags = $new_field['value'];
                if (!empty($new_field_tags) && ($key = array_search($tag_request, $new_field_tags)) !== FALSE) {
                    $log_object->new_field->already_present[] = $id;
                    $count_new++;
                } else {
                    $new_field_tags[] = $tag_request;
                    update_field($new_field_key, $new_field_tags, $id);
                    $log_object->new_field->update[] = $id;
                    $count_new++;
                }
            }
        }
        $log_object->affected_posts_old = $count_old;
        $log_object->affected_posts_new = $count_new;
        return new WP_REST_Response($log_object, 200);
    } else {
        return new WP_Error( 'haru_no_matches', 'No matches for '.$tag_request.' and '.$old_field_name, array( 'status' => 400 ) );
    }
}

function haru_get_events( WP_REST_Request $request ) {

  $offset = $request['offset'];

  $posts = get_posts(array(
  	'post_type'	=> 'events',
  	'posts_per_page' => 20,
  	'offset' => $offset,
    'post_status' => 'publish',
    'meta_query' => array(
			array(
				'key' => 'end_time',
				'value' => current_time( 'mysql' ),
				'compare' => '>='
			)
		),
  	'meta_key' => 'start_time',
  	'orderby' => 'meta_value',
  	'order'	=> 'ASC'
  ));

  if ($posts) {
    $controller = new WP_REST_Posts_Controller('events');
    foreach ($posts as $post) {
      $data = $controller->prepare_item_for_response( $post, $request );
      $events[] = $controller->prepare_response_for_collection( $data );
    }
    return new WP_REST_Response($events, 200);
  } else {
    return new WP_Error( 'haru_no_events', 'No events', array( 'status' => 400 ) );
  }
}

?>
