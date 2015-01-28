<?php
/*
 * Create Custom Table on the Database
 */

add_action( 'init', 'wpreviewr_register_reviews_table', 1 );
add_action( 'switch_blog', 'wpreviewr_register_reviews_table' );

function wpreviewr_register_reviews_table(){
	global $wpdb;
    $wpdb->reviewr_reviews = "{$wpdb->prefix}reviewr_reviews";
    $wpdb->reviewr_reviewmeta = "{$wpdb->prefix}reviewr_reviewmeta";
}

function wpreviewr_create_tables(){
    //Create Table Data
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	global $charset_collate;
	$version = get_option('WP_REVIEWR_VERSION');
	wpreviewr_register_reviews_table(); // Call this manually as we may have missed the init hook

	$sql_create_table = "CREATE TABLE {$wpdb->reviewr_reviews} (
          review_ID bigint(20) unsigned NOT NULL auto_increment,
          review_post_ID bigint(20) unsigned NOT NULL default '0',
          review_author varchar(999),
          review_author_email varchar(100) NOT NULL default '',
          review_author_IP varchar(100)  NOT NULL default '',
          review_date datetime NOT NULL default '0000-00-00 00:00:00',
          review_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
          review_content text NOT NULL default '',
          review_approved varchar(20) NOT NULL default '',
          review_type varchar(20) NOT NULL default '',
          review_status varchar(20) NOT NULL default '',
          user_id bigint(20) unsigned NOT NULL default '0',
          PRIMARY KEY  (review_ID),
          KEY user_id (user_id)
     ) $charset_collate; ";

	$sql_create_meta = "CREATE TABLE {$wpdb->reviewr_reviewmeta} (
          meta_id bigint(20) unsigned NOT NULL auto_increment,
          reviewr_review_id bigint(20) unsigned NOT NULL default '0',
          post_id bigint(20) unsigned NOT NULL default '0',
          meta_key varchar(255) NOT NULL default '',
          meta_value longtext  NOT NULL default '',
          PRIMARY KEY  (meta_id)
     ) $charset_collate; ";
 	if($version != WP_REVIEWR_VERSION){
 		dbDelta( $sql_create_table );
 		dbDelta( $sql_create_meta );
 		update_option('WP_REVIEWR_VERSION', WP_REVIEWR_VERSION);
 	}
 	// die();
}
 
// Create tables on plugin activation
register_activation_hook( __FILE__, 'wpreviewr_create_tables' );

function wpreviewr_update_db_check() {
    $version = get_option('WP_REVIEWR_VERSION');
    if ($version  != WP_REVIEWR_VERSION) {
        wpreviewr_create_tables();
    }
}
add_action( 'plugins_loaded', 'wpreviewr_update_db_check' );

?>