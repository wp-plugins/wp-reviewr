<?php

/*
 * Shortcode Display
 * Date: October 18, 2014
 */

class WP_REVIEWR_METABOX{
	// global $settings;
	function __construct(){
		add_action( 'add_meta_boxes', array($this, 'create_metabox') );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );
		add_action('save_post', array($this, 'savemeta'));
	}
	
	/**
	 * Enqueue Scripts and Styles
	 *
	 * @since 1.0
	 */
	function enqueue(){
		wp_enqueue_style( 'reviewr-ui', plugins_url( 'lib/css/jquery-ui.min.css' , dirname(__FILE__) ) , array(), null );
		wp_enqueue_style( 'reviewr-admin', plugins_url( 'lib/css/admin-reviewr.css' , dirname(__FILE__) ) , array(), null );

		wp_register_script(
			'admin-reviewr',
			plugins_url( 'lib/js/jquery.admin-reviewr.js' , dirname(__FILE__) ),
			array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-slider', 'jquery-ui-sortable' ),
			'',
			true
		);
		wp_enqueue_style( 'reviewr-ui' );
		wp_enqueue_style( 'reviewr-admin' );
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-mouse');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('admin-reviewr');
		wp_localize_script( 'admin-reviewr', 'vars', array(
				'ajaxurl'	=>  admin_url('admin-ajax.php'), 
				'title' 	=>	__('Review Criteria', 'wp-reviewr'),
				'score' 	=>	__('Criteria Score', 'wp-reviewr'),
				'delete' 	=>	__('Delete', 'wp-reviewr'),
				'confirm'	=>	__('Are you sure you want to remove this criteria?', 'wp-reviewr'),
			)
		);
	}

	function create_metabox(){
		$general_settings = (array) get_option( 'wpreviewr_general_settings' );
		if(isset($general_settings['post_types']) && !empty($general_settings['post_types'])){
			foreach ($general_settings['post_types'] as $key => $value) {
				add_meta_box('reviewr-metabox', __('Review Options', 'wp-reviewr'), array($this, 'review_metabox'),$value,'normal','high');
			}
		}
	}

	/**
	 * Review Information Metabox Content
	 *
	 * @since 1.0
	 */
	function review_metabox($post){
		$meta = get_post_meta($post->ID, '_reviewr_info', true);
		if(!empty($meta)){
			$meta = unserialize($meta);
		}
		// print_r($meta);
	?>
		<input type="hidden" name="reviewr_nonce" value="<?php _e( wp_create_nonce(basename(__FILE__)) );?>" />
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="reviewr-position-fld"><?php _e('Review Box Position', 'wp-reviewr')?></label>
					</th>
					<td>
						<select name="reviewr[position]" id="reviewr-position-fld">
							<option value="top" <?php if(isset($meta['position']) && $meta['position'] == 'top'){ echo 'selected="selected"'; }?>><?php _e('Top of the Post', 'wp-reviewr')?></option>
							<option value="bottom" <?php if(isset($meta['position']) && $meta['position'] == 'bottom'){ echo 'selected="selected"'; }?>><?php _e('Bottom of the Post', 'wp-reviewr')?></option>
							<option value="manual" <?php if(isset($meta['position']) && $meta['position'] == 'manual'){ echo 'selected="selected"'; }?>><?php _e('Manual via shortcode', 'wp-reviewr')?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="reviewr-title-fld"><?php _e('Review Box Title', 'wp-reviewr')?></label>
					</th>
					<td>
						<input type="text" id="reviewr-title-fld" name="reviewr[title]" class="widefat" value="<?php if(isset($meta['title']) && !empty($meta['title'])){ echo $meta['title']; }?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="reviewr-summary-fld"><?php _e('Review Summary', 'wp-reviewr')?></label>
					</th>
					<td>
						<textarea id="reviewr-summary-fld" name="reviewr[summary]" class="widefat" rows="7"><?php if(isset($meta['summary']) && !empty($meta['summary'])){ echo $meta['summary']; }?></textarea>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php _e('Review Criteria', 'wp-reviewr')?></h2>
		<div class="reviewr-criteria-container">
			<ul class="reviewr-criteria-lists">
				<?php if(isset($meta['criteria']) && !empty($meta['criteria'])) :
					foreach ($meta['criteria'] as $key => $value) {
				?>
					<li class="reviewr-criteria-single" id="reviewr-criteria-<?php echo $key;?>">
						<input type="hidden" name="reviewr[criteria][<?php echo $key;?>][id]" value="criteria<?php echo $key;?>" />
						<table class="form-table reviewr-criteria">
							<tbody>
								<tr>
									<th scope="row">
										<label for="reviewr-title-fld-<?php echo $key;?>"><?php _e('Review Criteria', 'wp-reviewr')?></label>
									</th>
									<td colspan="2">
										<input type="text" id="reviewr-title-fld-<?php echo $key;?>" name="reviewr[criteria][<?php echo $key;?>][title]" class="widefat" value="<?php if(isset($value['title'])){ echo $value['title']; }?>" />
									</td>
									<td class="reviewr-td-last">&nbsp;</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="reviewr-score-fld-<?php echo $key;?>"><?php _e('Criteria Score', 'wp-reviewr')?></label>
									</th>
									<td>
										<div class="reviewr-admin-slider" id="reviewr-score-fld-<?php echo $key;?>" data-target="#reviewr-slider-<?php echo $key;?>" data-value="<?php if(isset($value['score'])){ echo intval($value['score']); }?>"></div>
									</td>
									<td class="reviewr-td-small">
										<input type="text" id="reviewr-slider-<?php echo $key;?>" class="reviewr-admin-slider-input" data-target="#reviewr-score-fld-<?php echo $key;?>" name="reviewr[criteria][<?php echo $key;?>][score]" value="<?php if(isset($value['score'])){ echo intval($value['score']) . '%'; }?>" />
									</td>
									<td class="reviewr-td-last">
										<input type="button" class="button button-primary button-large reviewr-criteria-delete" data-target="#reviewr-criteria-<?php echo $key;?>" value="<?php _e('Delete', 'wp-reviewr')?>">
									</td>
								</tr>
							</tbody>
						</table>
					</li>
				<?php } endif;?>
			</ul>
			<input type="button" class="button button-primary button-large reviewr-add-criteria" value="<?php _e('Add New Criteria', 'wp-reviewr')?>">
		</div>

	<?php	
	}

	/**
	 * Save Review Information
	 *
	 * @since 1.0
	 */
	function savemeta($post_id){
		if(isset( $_POST['reviewr_nonce'] )){
			$reviewr = serialize( $_POST['reviewr'] );
			$reviewr = strip_tags($reviewr);
			update_post_meta($post_id, '_reviewr_info', $reviewr);
			$reviewr = unserialize($reviewr);
			$total = 0;
			$c = 0;
			if(isset($reviewr['criteria']) && !empty($reviewr['criteria'])){
				foreach ($reviewr['criteria'] as $key => $value) {
					$c++;
					$score = intval( $value['score'] );
					if($score > 100){
						$score = 100;
					}else if($score < 0){
						$score = 0;
					}
					$total += $score;
				}

				$percentage = $total/$c;
				update_post_meta($post_id, '_reviewr_percentage', $percentage);
			}
		}
	}
}
$wp_reviewr_metabox = new WP_REVIEWR_METABOX();
?>