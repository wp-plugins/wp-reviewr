<?php

/**
 * 
 * Plugin Name:       WP Reviewr
 * Plugin URI:        https://wordpress.org/plugins/wp-reviewr/
 * Description:       Free review plugin that Enable rating on your posts, pages and post types. <strong>If you decide to upgrade to <a href="http://wp-reviewr.com/" target="_blank">WP Reviewr Pro</a>, please deactivate WP Reviewr first</strong>.
 * Version:           1.0
 * Author:            phpbits
 * Author URI:        http://codecanyon.net/user/phpbits?ref=phpbits
 * Text Domain:       wp-reviewr
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('WP_REVIEWR_DIR',dirname(__FILE__));
define('WP_REVIEWR_VERSION','1.0');

/*##################################
	REQUIRE
################################## */
require_once( dirname( __FILE__ ) . '/lib/phpbits/phpbits.functions.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.enqueue.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.settings.php' );


require_once( dirname( __FILE__ ) . '/core/functions.create-table.php' );
require_once( dirname( __FILE__ ) . '/core/functions.display.php' );
require_once( dirname( __FILE__ ) . '/core/functions.metabox.php' );
require_once( dirname( __FILE__ ) . '/core/functions.reviewr.php' );
// require_once( dirname( __FILE__ ) . '/core/functions.actions.php' );

/*##################################
  DEFAULT OPTION
################################## */
function wpreviewr_activate() {
  if(!get_option( 'wpreviewr_general_settings')){
    $general = array();
    $general['post_types'] = array(
            "post"    =>    'post'
      );
    add_option('wpreviewr_general_settings',$general);
  }

  if(!get_option( 'wpreviewr_appearance_settings')){
    $appearance = array();
    $appearance['left']['bg'] = '#3c948b';
    $appearance['left']['text'] = '#ffffff';
    $appearance['left']['fill'] = '#39B4CC';
    $appearance['left']['percent'] = '#A2ECFB';

    $appearance['right']['bg'] = '#f7f7f7';
    $appearance['right']['text'] = '#666666';
    $appearance['right']['fill'] = '#0e90d2';
    $appearance['right']['percent'] = '#efefef';
    add_option('wpreviewr_appearance_settings',$appearance);
  }
}
register_activation_hook( __FILE__, 'wpreviewr_activate' );