<?php
/*
 * Reviewr Admin Settings
 */

class Settings_API_WPREVIEWR {
	
	/*
	 * For easier overriding we declared the keys
	 * here as well as our tabs array which is populated
	 * when registering settings
	 */
	private $general_settings_key = 'wpreviewr_general_settings';
	private $appearance_settings_key = 'wpreviewr_appearance_settings';
	private $text_settings_key = 'wpreviewr_text_settings';
	private $plugin_menu_key = 'options-general.php';
	private $plugin_options_key = 'wpreviewr_plugin_options';
	private $plugin_settings_tabs = array();
	private $phpbits;

	
	/*
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 * current ones speak for themselves.
	 */
	function __construct() {
		$this->phpbits = new WP_PHPBITS();
		add_action( 'init', array( &$this, 'wpreviewr_load_settings' ) );
		add_action( 'admin_init', array( &$this, 'wpreviewr_register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'wpreviewr_register_appearance_settings' ) );
		add_action( 'admin_menu', array( &$this, 'wpreviewr_add_admin_menus' ) );
		add_action( 'wp_footer', array( &$this, 'footer_css' ), 99 );
	}
	
	/*
	 * Loads both the general and advanced settings from
	 * the database into their respective arrays. Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 */
	function wpreviewr_load_settings() {
		$this->general_settings = (array) get_option( $this->general_settings_key );
		$this->appearance_settings = (array) get_option( $this->appearance_settings_key );
		$this->text_settings = (array) get_option( $this->text_settings_key );
	}
	
	/*
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function wpreviewr_register_general_settings() {
		$this->plugin_settings_tabs[$this->general_settings_key] = __('General','wp-reviewr');
		
		register_setting( $this->general_settings_key, $this->general_settings_key );
		add_settings_section( 'general_section', __('General Options', 'wp-reviewr'), array( &$this, 'wpreviewr_general_options_section' ), $this->general_settings_key );
	}

	function wpreviewr_general_options_section(){ 
		//get custom post type
	 	$get_cpt_args = array(
			'public'   => true,
			'_builtin' => false
		); 
		$custom_post_types = get_post_types( $get_cpt_args, 'names', 'and'); 
		$post_types = array( 'post' => __('Posts', 'wp-reviewr'), 'page' => __('Pages', 'wp-reviewr') );
		if(!empty($custom_post_types)):
			foreach ( $custom_post_types  as $custom_post_type ) {
				$custom_post_type_object = get_post_type_object( $custom_post_type );
				$post_types[ $custom_post_type_object->name ] = $custom_post_type_object->label;
			}
		endif;
		// print_r($this->general_settings);
		$_fields = array();
		?>

		<h3><?php _e('Post Types', 'wp-reviewr') ?></h3>
		<p><?php _e('Activate reviews on any post types you want. This will automatically create review metabox on each selected post types.', 'wp-reviewr') ?></p>
		<?php foreach ($post_types as $key => $value) { 
					$_fields[ $key ] = array(
										'type'		=>	'checkbox',
										'params'	=> array(
													'id'	=>	'wpreviewr-type-' .$key,
													'name'	=>	$this->general_settings_key . '[post_types][]',
													'label'	=>	$value,
													'value'	=>	( isset($this->general_settings['post_types']) && in_array($key, $this->general_settings['post_types']) ? 1 : '' ),
													'desc'	=>	__('Allow reviews on '. $value, 'wp-reviewr'),
													'key'	=>	$key
												)
									);
					 }

		echo $this->phpbits->create_table($_fields);
		}

	/*
	 * Registers the animation settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function wpreviewr_register_appearance_settings() {
		$this->plugin_settings_tabs[$this->appearance_settings_key] = __('Appearance', 'wp-reviewr');
		
		register_setting( $this->appearance_settings_key, $this->appearance_settings_key );
		add_settings_section( 'appearance_section', __('', ''), array( &$this, 'wpreviewr_appearance_options_section' ), $this->appearance_settings_key );

	}
	function wpreviewr_appearance_options_section(){ 
		if ( isset( $_GET['settings-updated'] ) ) {
			$this->override_css();
		}
		?>
		<p><?php _e('Change the review color scheme to match your current theme using this options.', 'wp-reviewr') ?></p>
		<h3><?php _e('Left Block Appearance Options', 'wp-reviewr'); ?></h3>
		<?php
		//create form table
		echo $this->phpbits->create_table(
				array(
					'bg' 		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-left-bg',
												'name'	=> $this->appearance_settings_key . '[left][bg]',
												'label' => 'Background Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'left', 'bg')
											)
								),
					'text'		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-left-text',
												'name'	=> $this->appearance_settings_key . '[left][text]',
												'label' => 'Text Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'left', 'text')

											)
								),
					'percent'	=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-left-pr',
												'name'	=> $this->appearance_settings_key . '[left][percent]',
												'label' => 'Percentage Background Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'left', 'percent')

											)
								),
					'fill'		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-left-fill',
												'name'	=> $this->appearance_settings_key . '[left][fill]',
												'label' => 'Percentage Fill Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'left', 'fill')

											)
								),
				)
			); ?>
		<h3><?php _e('Right Block Appearance Options', 'wp-reviewr'); ?></h3>
		<?php
		echo $this->phpbits->create_table(
				array(
					'bg' 		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-right-bg',
												'name'	=> $this->appearance_settings_key . '[right][bg]',
												'label' => 'Background Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'right', 'bg')
											)
								),
					'text' 		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-right-text',
												'name'	=> $this->appearance_settings_key . '[right][text]',
												'label' => 'Text Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'right', 'text')
											)
								),
					'percent'	=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-right-pr',
												'name'	=> $this->appearance_settings_key . '[right][progress]',
												'label' => 'Percentage Bar Background Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'right', 'progress')

											)
								),
					'fill'		=> array(
									'type' => 'colorpicker',
									'params' => array(
												'id' 	=> 'wpreviewr-app-right-fill',
												'name'	=> $this->appearance_settings_key . '[right][fill]',
												'label' => 'Percentage Bar Fill Color',
												'value' => $this->phpbits->is_set($this->appearance_settings, 'right', 'fill')

											)
								),
				)
			);
	}

	function override_css(){
		$path = plugin_dir_path(__FILE__) . '../lib/css/custom.css';
		$options = "/********* Do not edit this file *********/\n\n";
		if(is_writable($path)){
			$options .= $this->custom_css();
			$makecss = file_put_contents( $path , $options);
		}
		
	}

	function footer_css(){
		$path = plugin_dir_path(__FILE__) . '../lib/css/custom.css';
		if(!is_writable($path)){
			echo $this->custom_css();
		}
	}

	/*
	 * Create Separate Function for CSS params
	 * just in case the file is not writable, we can call it again
	 */

	function custom_css(){
		$css = '';
		if( isset($this->appearance_settings['left']['bg']) && !empty($this->appearance_settings['left']['bg'])){
			$css .= 'body .wp-reviewr-container .wp-reviewr-left, body .wp-reviewr-container .wp-reviewr-left .wp-reviewr-circle{ background : '. $this->appearance_settings['left']['bg'] .';}';
		}
		if( isset($this->appearance_settings['left']['text']) && !empty($this->appearance_settings['left']['text']) ){
			$css .= 'body .wp-reviewr-container .wp-reviewr-left .wp-reviewr-title, body .wp-reviewr-container .wp-reviewr-left .wp-reviewr-prcnt{ color : '. $this->appearance_settings['left']['text'] .';}';
		}

		if( isset($this->appearance_settings['left']['fill']) && !empty($this->appearance_settings['left']['fill']) ){
			$css .= 'body .wp-reviewr-container .wp-reviewr-left .wp-reviewr-title a, body .wp-reviewr-container .wp-reviewr-right .wp-reviewr-form input[type="submit"], body .wp-reviewr-container .wp-reviewr-left .wp-reviewr-border{ background : '. $this->appearance_settings['left']['fill'] .';}';
		}
		

		//right block
		if( isset($this->appearance_settings['right']['bg']) && !empty($this->appearance_settings['right']['bg']) ){
			$css .= 'body .wp-reviewr-container .wp-reviewr-right{ background : '. $this->appearance_settings['right']['bg'] .';}';
		}
		if( isset($this->appearance_settings['right']['text']) && !empty($this->appearance_settings['right']['text']) ){
			$css .= 'body .wp-reviewr-container .wp-reviewr-right, body .wp-reviewr-container .wp-reviewr-right h3, body .wp-reviewr-container .wp-reviewr-right p{ color : '. $this->appearance_settings['right']['text'] .';}';
		}
		if( isset($this->appearance_settings['right']['progress']) && !empty($this->appearance_settings['right']['progress']) ){
			$css .= 'body .wp-reviewr-ratings ul li .wp-reviewr-rating .wp-reviewr-progress{ background : '. $this->appearance_settings['right']['progress'] .';}';
			$css .= 'body .wp-reviewr-container .ui-widget-content{ background: '. $this->appearance_settings['right']['progress'] .'; border: 0px; }';
		}
		if( isset($this->appearance_settings['right']['fill']) && !empty($this->appearance_settings['right']['fill']) ){
			$css .= 'body .wp-reviewr-ratings ul li .wp-reviewr-rating .wp-reviewr-progress .wp-reviewr-bar, body .wp-reviewr-container .ui-widget-header{ background : '. $this->appearance_settings['right']['fill'] .';}';
		}

		return $css;
	}


	/*
	 * Called during admin_menu, adds an options
	 * page under Settings called My Settings, rendered
	 * using the wplftr_plugin_options_page method.
	 */
	function wpreviewr_add_admin_menus() {
		// add_menu_page( __('Reviews', 'wp-reviewr'), __('Reviews', 'wp-reviewr'), 'manage_options', $this->plugin_menu_key, array( &$this, 'wpreviewr_add_menu' ), 'dashicons-star-filled' );
		add_submenu_page( $this->plugin_menu_key, __('WP-Reviewr Settings', 'wp-reviewr'), __('WP-Reviewr Settings', 'wp-reviewr'), 'manage_options', $this->plugin_options_key, array( &$this, 'wpreviewr_plugin_options_page' ) );
	}
	
	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the wplftr_plugin_options_tabs method
	 * to render the tabs.
	 */
	function wpreviewr_plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
		?>
		<div class="wrap">
			<?php settings_errors(); ?>
			<?php $this->wpreviewr_plugin_options_tabs(); ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
				<?php 
				if(function_exists('submit_button')) { submit_button(); } else { ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
				<?php }?>
			</form>
		</div>
		<?php
	}
	
	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * wplftr_plugin_options_page method.
	 */
	function wpreviewr_plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}
};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$settings_api_wpreviewr_plugin = new Settings_API_WPREVIEWR;' ) );

?>