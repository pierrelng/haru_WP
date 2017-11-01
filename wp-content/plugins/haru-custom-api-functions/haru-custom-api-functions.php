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
  register_rest_route( 'haru/v1', 'events/facebook/flexible/status', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_get_events_facebook_flexible_status'
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
  register_rest_route( 'haru/v1', 'events/acftag/when/update', array(
    'methods'  => WP_REST_Server::EDITABLE,
    'callback' => 'haru_automatic_when_tags'
  ) );
  register_rest_route( 'haru/v1', 'events/acftag/coupdecoeur', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_get_upcoming_coupdecoeur'
  ) );
  register_rest_route( 'haru/v1', 'events/(?P<id>\d+)', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'haru_get_specific_event'
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

  $whitelisted = [857, 903, 973, 1029, 1127, 2178, 3476];
  foreach ($results as $id) {
    if (!in_array($id->ID, $whitelisted)) {
      $refined_results[] = $id;
    }
  }
	return $refined_results;
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
  $selected_day = $request['selected_day'];

  $query_args = array(
    'post_type'	=> 'events',
    'posts_per_page' => 20,
    'offset' => $offset,
    'post_status' => 'publish',
    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key' => 'end_time',
        'value' => current_time( 'mysql' ),
        'compare' => '>='
      )
    ),
    'meta_key' => 'start_time',
    'orderby' => 'meta_value',
    'order'	=> 'ASC'
  );

  if( !empty($selected_day) ) {
    $query_args['meta_query'][] = array(
        'key' => 'start_time',
        'value' => date($selected_day),
        'compare' => '=',
        'type' => 'DATE'
    );
  }

  $posts = get_posts($query_args);

  if ($posts) {
    $controller = new WP_REST_Posts_Controller('events');
    foreach ($posts as $post) {
      $data = $controller->prepare_item_for_response( $post, $request );
      $events[] = $controller->prepare_response_for_collection( $data );
    }
    return new WP_REST_Response($events, 200);
  } else {
    return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
  }
}

function haru_automatic_when_tags() {

  $query_args = array(
    'post_type' => 'events',
    'post_status' => array('pending'),
    // 'post_status' => array('any'),
    'fields' => 'ids',
    'posts_per_page' => -1
    // 'posts_per_page' => 10
  );

  $post_ids = get_posts($query_args);
  // $post_ids = [5333];

  if ( empty($post_ids) || !is_array($post_ids) ) {
    return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
  }

  // if tag_when_week exists = empty string or array fo strings
  // if tag_when_day exists = empty string or array fo strings
  // if tag_when_night exists = empty string or array fo strings
  // if any of those tags don't exist = null

  foreach ( $post_ids as $id ) {
    $fields = [
      'start_time' => new DateTime( get_field( 'start_time', $id, false) ),
      'end_time' => new DateTime( get_field( 'end_time', $id, false ) ),
      'tag_when_week' => get_field( 'tag_when_week', $id ),
      'tag_when_day' => get_field( 'tag_when_day', $id ),
      'tag_when_night' => get_field( 'tag_when_night', $id )
    ];
    foreach ( $fields as $arr_key => $arr_value ) {
      // if ( is_null($arr_value) ) {
      //   return new WP_Error( 'haru_no_acftag', 'Missing ACFtag '.$arr_key.' on event '.$id, array( 'status' => 404 ) );
      // }
      $start_hour = $fields['start_time']->format('H:i');
      $end_hour = $fields['end_time']->format('H:i');
      $interval = new DateInterval('PT1H'); // https://stackoverflow.com/a/35801738/8319316
      $periods = new DatePeriod($fields['start_time'], $interval, $fields['end_time']);
      $duration = iterator_count($periods);
      switch ( $arr_key ) {
        case 'tag_when_week':
          $field_key = 'field_5975ed1cdfd28';
          $start_day = $fields['start_time']->format('l');
          $start_hour = $fields['start_time']->format('H');
          $value = [];
          // Tag SEMAINE
          if ( $start_day === 'Monday' && $start_hour >= '07' ) {
            $value[] = 'semaine';
          }
          if ( in_array( $start_day, ['Tuesday', 'Wednesday', 'Thursday', 'Friday'] ) ) {
            $value[] = 'semaine';
          }
          // Tag WEEKEND
          if ( $start_day === 'Friday' && $start_hour >= '18' ) {
            $value[] = 'weekend';
          }
          if ( in_array( $start_day, ['Saturday', 'Sunday'] ) ) {
            $value[] = 'weekend';
          }
          if ( $start_day === 'Monday' && $start_hour < '07' ) {
            $value[] = 'weekend';
          }
          update_field( $field_key, $value, $id );
          break;
        case 'tag_when_day':
          $field_key = 'field_5975efbca4d3e';
          if ( $duration <= 24 ) { // Si l'event dure moins de 24h
            $value = [];
            // Tag JOURNEE
            if (
              ( $start_hour >= '10:00' && $start_hour <= '16:00' && $end_hour >= '10:00' && $end_hour <= '16:00' ) // Events compris dans la période 10h-16h
              || ( $end_hour >= '11:00' && $end_hour <= '16:00' ) // Events dont la fin chevauche la période 10h-16h avec un minimum d'1h après 10h
              || ( $start_hour >= '10:00' && $start_hour <= '15:00' ) // Events dont le début chevauche la période 10h-16h avec un minimum d'1h avant 16h
              || ( $start_hour < '10:00' && $end_hour > '16:00' ) // Events chevauchant complétement la période 10h-16h
            ) {
              $value[] = 'journee';
            }
            // Tag FINDEJOURNEE
            if (
              ( $start_hour >= '16:00' && $start_hour <= '21:00' && $end_hour >= '16:00' && $end_hour <= '21:00' ) // Events compris dans la période 16h-21h
              || ( $end_hour >= '17:00' && $end_hour <= '21:00' ) // Events dont la fin chevauche la période 16h-21h avec un minimum d'1h après 16h
              || ( $start_hour >= '16:00' && $start_hour <= '20:00' ) // Events dont le début chevauche la période 16h-21h avec un minimum d'1h avant 21h
              || ( $start_hour < '16:00' && $end_hour > '21:00' ) // Events chevauchant complétement la période 16h-21h
            ) {
              $value[] = 'findejournee';
            }
            // Tag BEFORE
            if (
              ( ( $start_hour >= '21:00' || $start_hour == '00:00' ) && ( $end_hour >= '21:00' || $end_hour == '00:00' ) ) // Events compris dans la période 21h-00h
              || ( $end_hour >= '22:00' || $end_hour == '00:00' ) // Events dont la fin chevauche la période 21h-00h avec un minimum d'1h après 21h
              || ( $start_hour >= '21:00' && $start_hour <= '23:00' ) // Events dont le début chevauche la période 21h-00h avec un minimum d'1h avant 00h
              || ( $start_hour >= $end_hour && $start_hour < '21:00') // Events chevauchant complétement la période 21h-00h
            ) {
              $value[] = 'before';
            }
            // Tag SOIR
            if (
              ( ( $start_hour >= '22:00' || $start_hour <= '03:00' ) && ( $end_hour >= '22:00' || $end_hour <= '03:00' ) ) // Events compris dans la période 22h-03h
              || ( $end_hour >= '23:00' || $end_hour <= '03:00' ) // Events dont la fin chevauche la période 22h-03h avec un minimum d'1h après 22h
              || ( $start_hour >= '22:00' || $start_hour <= '02:00' ) // Events dont le début chevauche la période 22h-03h avec un minimum d'1h avant 03h
              || ( $start_hour >= $end_hour && $start_hour < '22:00' && $end_hour > '03:00' ) // Events chevauchant complétement la période 22h-03h
            ) {
              $value[] = 'soir';
            }
            // Tag AFTER
            if ( ( $start_hour >= '04:00' && $start_hour <= '09:59' ) ) {
              $value[] = 'after';
            }
            update_field( $field_key, $value, $id );
          }
          break;
        case 'tag_when_night':
          $field_key = 'field_5976068837420';
          if ( $duration <= 24 ) {
            $value = [];
            // $new_tag_when_day = get_field( 'tag_when_day', $id );
            // if ( empty($new_tag_when_day) || ( is_array($new_tag_when_day) && !in_array( 'after', $new_tag_when_day ) ) ) {
              if ( ( $end_hour >= '22:00' || $end_hour <= '03:00') ) {
                $value[] = 'early';
              }
              if ( ( $end_hour >= '04:00' && $end_hour <= '06:00' ) ) {
                $value[] = 'allnightlong';
              }
              if ( ( $end_hour >= '06:00' && $end_hour <= '10:00' ) ) {
                $value[] = 'late';
              }
            // }
          }
          update_field( $field_key, $value, $id );
          break;
      }
    }
  }

  return new WP_REST_Response($post_ids, 200);
}

function haru_get_upcoming_coupdecoeur() {

  $query_args = array(
    'post_type'	=> 'events',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'fields' => 'ids',
    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key' => 'end_time',
        'value' => current_time( 'mysql' ),
        'compare' => '>='
      ),
      array(
        'key' => 'tag_coupdecoeur',
        'value' => 'coup',
        'compare' => 'LIKE'
      )
    ),
    'meta_key' => 'start_time',
    'orderby' => 'meta_value',
    'order'	=> 'ASC'
  );

  $posts = get_posts($query_args);

  if ($posts) {
    return new WP_REST_Response($posts, 200);
  } else {
    return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
  }
}

function haru_get_events_facebook_flexible_status() {

  $query_args = array(
    'post_type'	=> 'events',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'post_status' => array('publish', 'pending'),
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
  );

  $post_ids = get_posts($query_args);

  if ($post_ids) {
    foreach ($post_ids as $id) {
      $results[] = array(
        'id' => $id,
        'url' => get_field('facebook_event_url', $id),
      );
    }
    return new WP_REST_Response($results, 200);
  } else {
    return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
  }
}

function haru_get_specific_event( WP_REST_Request $request ) {

  $id = $request['id'];

  $post = get_post($id);

  if ($post) {
    $controller = new WP_REST_Posts_Controller('events');
    $data = $controller->prepare_item_for_response( $post, $request );
    $event = $controller->prepare_response_for_collection( $data );
    if ($event['type'] === 'events' && $event['status'] === 'publish') {
      return new WP_REST_Response($event, 200);
    } else {
      return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
    }
  } else {
    return new WP_Error( 'haru_no_events', 'No events found', array( 'status' => 404 ) );
  }
}


?>
