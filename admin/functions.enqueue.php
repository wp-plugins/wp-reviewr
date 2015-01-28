<?php
/*
 * Enqueue Scripts and Style for Admin Settings
 */

function load_wpreviewr_admin_scripts($hook) {
	if( 'settings_page_wpreviewr_plugin_options' != $hook )
        return;

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script('wp-color-picker');

	wp_enqueue_script(
		'reviewr-js',
		plugins_url( 'lib/js/reviewr-admin.js' , dirname(__FILE__) ),
		array( 'jquery' ),
		'',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'load_wpreviewr_admin_scripts' );

?>