<?php
function wp_insert_reviewr( $reviewdata ) {
	global $wpdb;
	$data = wp_unslash( $reviewdata );
	$general_settings = (array) get_option( 'wpreviewr_general_settings' );

	$review_author       = ! isset( $data['review_author'] )       ? '' : $data['review_author'];
	$review_author_email = ! isset( $data['review_author_email'] ) ? '' : $data['review_author_email'];
	$review_author_url   = ! isset( $data['review_author_url'] )   ? '' : $data['review_author_url'];
	$review_author_IP    = ! isset( $data['review_author_IP'] )    ? '' : $data['review_author_IP'];

	$review_date     = ! isset( $data['review_date'] )     ? current_time( 'mysql' )            : $data['review_date'];
	$review_date_gmt = ! isset( $data['review_date_gmt'] ) ? get_gmt_from_date( $review_date ) : $data['review_date_gmt'];

	$review_post_ID  = ! isset( $data['review_post_ID'] )  ? '' : $data['review_post_ID'];
	$review_content  = ! isset( $data['review_content'] )  ? '' : strip_tags($data['review_content']);
	$review_karma    = ! isset( $data['review_karma'] )    ? 0  : $data['review_karma'];
	$review_approved = ! isset( $data['review_approved'] ) ? 0  : $data['review_approved'];
	$review_agent    = ! isset( $data['review_agent'] )    ? '' : $data['review_agent'];
	$review_type     = ! isset( $data['review_type'] )     ? '' : $data['review_type'];
	$review_status    = ! isset( $data['review_status'] ) ? 'pending' : $data['review_status'];
	$review_parent   = ! isset( $data['review_parent'] )   ? 0  : $data['review_parent'];

	$user_id  = 0;
	if(is_user_logged_in()){
		$user_id = get_current_user_id();
		$review_type = 'loggedin';
	}

	if( !isset($general_settings['appear']) || ( isset($general_settings['appear']) && '1' != $general_settings['appear'] ) ){
		$review_approved = '1';
		$review_status = 'approved';
	}

	$compacted = compact( 'review_post_ID', 'review_author', 'review_author_email', 'review_author_IP', 'review_date', 'review_date_gmt', 'review_content', 'review_approved', 'review_type', 'review_status', 'user_id' );
	if ( ! $wpdb->insert( $wpdb->reviewr_reviews, $compacted ) ) {
		return false;
	}

	$id = (int) $wpdb->insert_id;

	// if ( $review_approved == 1 ) {
	// 	wp_update_review_count( $review_post_ID );
	// }
	// $review = get_review( $id );

	/**
	 * Fires immediately after a review is inserted into the database.
	 *
	 * @since 2.8.0
	 *
	 * @param int $id      The review ID.
	 * @param obj $review review object.
	 */
	// do_action( 'wp_insert_reviewr', $id, $review );
	if(isset($general_settings['email']) && $general_settings['email'] == 1){
		wp_reviewr_notification($id);
	}

	wp_cache_set( 'last_changed', microtime(), 'reviewr' );

	return $id;
}

function wp_reviewr_notification($id){
	if(!empty($id)){
		$url = admin_url( 'admin.php?page=wpreviewr_plugin_action&review_id=' . absint( $id ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );
		$review = get_reviewr_by_id( $id );
		$post = get_post($review['review_post_ID']);
		$body = __('A new review on post "'. $post->post_title .'" is waiting for your approval. <a href="'. $edit_link .'">'. $edit_link .'</a>','wp-reviewr');
		$to = get_option('admin_email');
		$status = wp_mail($to, __('New Review is waiting for your approval'), $body);

		return $status;
	}
} 

function wp_update_reviewr( $reviewarr ) {
	global $wpdb;

	// First, get all of the original fields
	$review = get_reviewr_by_id($reviewarr['review_ID']);
	if ( empty( $review ) ) {
		return 0;
	}
	// Escape data pulled from DB.
	$review = wp_slash($review);

	$old_status = $review['review_approved'];

	// Merge old and new fields with new fields overwriting old ones.
	$reviewarr = array_merge($review, $reviewarr);

	// $reviewarr = wp_filter_comment( $reviewarr );

	// Now extract the merged array.
	$data = wp_unslash( $reviewarr );

	/**
	 * Filter the review content before it is updated in the database.
	 *
	 * @since 1.5.0
	 *
	 * @param string $comment_content The review data.
	 */
	// $data['review_content'] = apply_filters( 'review_save_pre', $data['review_content'] );

	$data['review_date_gmt'] = get_gmt_from_date( $data['review_date'] );

	if ( ! isset( $data['review_approved'] ) ) {
		$data['review_approved'] = 1;
	} else if ( 'hold' == $data['review_approved'] ) {
		$data['review_approved'] = 0;
	} else if ( 'approve' == $data['review_approved'] ) {
		$data['review_approved'] = 1;
	}

	$review_ID = $data['review_ID'];
	$review_post_ID = $data['review_post_ID'];
	$keys = array( 'review_author', 'review_author_email', 'review_content', 'review_approved');
	
	$data = wp_array_slice_assoc( $data, $keys );
	$rval = $wpdb->update( $wpdb->reviewr_reviews, $data, compact( 'review_ID' ) );

	// clean_comment_cache( $review_ID );
	// wp_update_comment_count( $review_post_ID );
	/**
	 * Fires immediately after a review is updated in the database.
	 *
	 * The hook also fires immediately before review status transition hooks are fired.
	 *
	 * @since 1.2.0
	 *
	 * @param int $comment_ID The review ID.
	 */
	// do_action( 'edit_comment', $review_ID );
	// $review = get_comment($review_ID);
	// wp_transition_comment_status($review->comment_approved, $old_status, $review);
	return $rval;
}

function reviewr_getip(){
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function check_reviewr_exists($ip, $id){
	global $wpdb;
	$exists = $wpdb->get_var( $wpdb->prepare( 
		"SELECT review_ID
		FROM $wpdb->reviewr_reviews 
		WHERE review_author_IP = %s
		AND review_post_ID = %d
		AND review_status != 'trash'
		", 
		$ip,
		$id
	) );
	return $exists;
}

function check_user_exists($user_id, $id){
	global $wpdb;
	$exists = $wpdb->get_var( $wpdb->prepare( 
		"SELECT review_ID
		FROM $wpdb->reviewr_reviews 
		WHERE user_id = %d
		AND review_post_ID = %d
		AND review_status != 'trash'
		", 
		$user_id,
		$id
	) );
	return $exists;
}

function add_reviewr_meta($review_id, $meta_key, $meta_value, $unique = false) {
	return add_metadata('reviewr_review', $review_id, $meta_key, $meta_value, $unique);
}

function get_reviewr_meta($review_id, $meta_key, $unique = false) {
	return get_metadata('reviewr_review', $review_id, $meta_key, $unique);
}

function update_reviewr_meta($review_id, $meta_key, $meta_value, $prev_value = '') {
	return update_metadata('reviewr_review', $review_id, $meta_key, $meta_value, $prev_value);
}

function reviewr_update_meta_post($meta_id, $post_id){
	global $wpdb;
	$wpdb->update( 
		$wpdb->reviewr_reviewmeta , 
		array( 
			'post_id' => $post_id	// integer (number) 
		), 
		array( 'meta_id' => $meta_id ), 
		array( 
			'%d'	// value2
		), 
		array( '%d' ) 
	);
}

function get_reviewr_total($post_id, $meta_key){
	global $wpdb;
	$total = $wpdb->get_var($wpdb->prepare("
                                  SELECT sum(reviewmeta.meta_value) 
                                  FROM $wpdb->reviewr_reviewmeta reviewmeta
                                  INNER JOIN  $wpdb->reviewr_reviews reviews 
                                  WHERE reviewmeta.meta_key = %s 
                                  AND reviewmeta.post_id = %d
                                  AND reviews.review_ID = reviewmeta.reviewr_review_id
                                  AND reviews.review_approved = 1
                                  AND reviews.review_status = 'approved'", $meta_key, $post_id));

	return $total;
}
function get_reviewr_occurence($post_id, $meta_key){
	global $wpdb;
	$total = $wpdb->get_var($wpdb->prepare("
                                  SELECT count(reviewmeta.meta_key) 
                                  FROM $wpdb->reviewr_reviewmeta reviewmeta
                                  INNER JOIN  $wpdb->reviewr_reviews reviews 
                                  WHERE reviewmeta.meta_key = %s 
                                  AND reviewmeta.post_id = %d
                                  AND reviews.review_ID = reviewmeta.reviewr_review_id
                                  AND reviews.review_approved = 1
                                  AND reviews.review_status = 'approved'", $meta_key, $post_id));

	return $total;
}
function get_reviewr_totals($post_id){
	global $wpdb;
	$total = $wpdb->query($wpdb->prepare("SELECT * FROM $wpdb->reviewr_reviews WHERE review_post_ID = %d AND review_approved = 1 AND review_status = 'approved'", $post_id));

	return $total;
}
function get_reviewr_by_id($review_id){
	global $wpdb;

	$review = $wpdb->get_row($wpdb->prepare("
                                  SELECT *
                                  FROM $wpdb->reviewr_reviews
                                  WHERE review_ID = %d", $review_id));

	$_review = get_object_vars($review);
	return $_review;
}

function delete_reviewr_by_id($review_id){
	global $wpdb;

	$_trash = $wpdb->update( 
		$wpdb->reviewr_reviews , 
		array( 'review_status' => 'trash' ), 
		array( 
			'review_ID' => $review_id	// integer (number) 
		), 
		array( 
			'%s'	// value2
		), 
		array( '%d' ) 
	);
	return $_trash;
}

function approve_reviewr_by_id($review_id, $action){
	global $wpdb;

	$status = 'pending';
	$approved = 0;

	if('approve' == $action){
		$status = 'approved';
		$approved = 1;
	}

	$_trash = $wpdb->update( 
		$wpdb->reviewr_reviews , 
		array( 'review_status' => $status, 'review_approved' => $approved ), 
		array( 
			'review_ID' => $review_id	// integer (number) 
		), 
		array( 
			'%s'	// value2
		), 
		array( '%d' ) 
	);
	return $_trash;
}

function restore_reviewr_by_id($review_id, $approved){
	global $wpdb;
	$approved = 'pending';
	if('1' == $approved){
		$approved = 'approved';
	}
	$_restore = $wpdb->update( 
		$wpdb->reviewr_reviews , 
		array( 'review_status' => $approved ), 
		array( 
			'review_ID' => $review_id	// integer (number) 
		), 
		array( 
			'%s'	// value2
		), 
		array( '%d' ) 
	);
	return $_restore;
}

function get_reviewr_search_sql( $string, $cols ) {
	global $wpdb;

	if ( method_exists( $wpdb, 'esc_like' ) ) {
		$like = '%' . $wpdb->esc_like( $string ) . '%';
	}else{
		$like = '%' . $wpdb->like_escape( $string ) . '%';
	}
	

	$searches = array();
	foreach ( $cols as $col ) {
		$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
	}

	return ' AND (' . implode(' OR ', $searches) . ')';
}

function get_reviewrs_by_id($post_id, $args = array()){
	global $wpdb;
	$query = "SELECT * FROM $wpdb->reviewr_reviews WHERE review_post_ID = %d AND review_approved = 1 AND review_status = 'approved' ORDER BY review_ID DESC";

	if( isset($args['paged']) && isset($args['per_page']) && !empty($args['paged']) && !empty($args['per_page']) ){
     $offset=( $args['paged'] -1) * $args['per_page'];
     $query.=' LIMIT '.(int)$offset.','.(int)$args['per_page'];
   }
	$reviews = $wpdb->get_results($wpdb->prepare($query, $post_id));

	// $_review = get_object_vars($review);
	return $reviews;
}

function reviewr_is_approve($id){
	global $wpdb;
	$status = $wpdb->get_var( $wpdb->prepare( 
		"SELECT review_status
		FROM $wpdb->reviewr_reviews 
		WHERE review_ID = %d
		", 
		$id
	) );
	return $status;
}
?>