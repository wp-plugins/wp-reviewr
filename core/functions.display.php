<?php

/*
 * Display
 * Date: November 12, 2014
 */

class WP_REVIEWR{
	public $unique;
	public $general_settings = array();
	function __construct(){
		$this->unique = uniqid();
		$this->general_settings = (array) get_option( 'wpreviewr_general_settings' );
		add_filter( 'the_content', array($this, 'build_content'), 10 );
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue') );
		add_action('init', array($this, 'register_shortcodes'));
	}

	/**
	 * Enqueue Scripts and Styles
	 *
	 * @since 1.0
	 */
	function enqueue(){
		wp_enqueue_style( 'reviewr', plugins_url( 'lib/css/wp-reviewr.css' , dirname(__FILE__) ) , array(), null );
		wp_enqueue_style( 'reviewr-custom', plugins_url( 'lib/css/custom.css' , dirname(__FILE__) ) , array(), null );
		wp_enqueue_style( 'reviewr-ui', plugins_url( 'lib/css/jquery-ui.min.css' , dirname(__FILE__) ) , array(), null );
		wp_register_script(
			'jquery-reviewr',
			plugins_url( 'lib/js/jquery-reviewr.js' , dirname(__FILE__) ),
			array( 'jquery' ),
			'',
			true
		);
		wp_register_script(
			'jquery-wpreviewr',
			plugins_url( 'lib/js/jquery.wp-reviewr.js' , dirname(__FILE__) ),
			array( 'jquery' ),
			'',
			true
		);
		wp_enqueue_style( 'reviewr' );
		wp_enqueue_style( 'reviewr-custom' );
		wp_enqueue_style( 'reviewr-ui' );
		wp_enqueue_script('jquery-reviewr');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-mouse');	
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-wpreviewr');

		$appearance = (array) get_option('wpreviewr_appearance_settings');
		$params = array(
					'ajaxurl' =>  admin_url('admin-ajax.php'),
					'percentbg' => '#A2ECFB',
					'fillbg' => '#39B4CC'
				);
		if(isset($appearance['left']['percent']) && !empty($appearance['left']['percent'])){
			$params['percentbg'] = $appearance['left']['percent'];
		}
		if(isset($appearance['left']['fill']) && !empty($appearance['left']['fill'])){
			$params['fillbg'] = $appearance['left']['fill'];
		}

		wp_localize_script( 'jquery-reviewr', 'reviewr_vars', $params);
	}

	function build_content($content){
		$return = $content;
			if( ( is_single() && in_the_loop() ) || ( is_page() && in_the_loop() ) ){
				global $post;
				if(isset($post->post_type) && !empty($post->post_type) && isset($this->general_settings['post_types']) && in_array($post->post_type, $this->general_settings['post_types'])){
					$metadata = get_post_meta($post->ID, '_reviewr_info', true);
					$percentage = get_post_meta($post->ID, '_reviewr_percentage', true);
					if(!empty($metadata)){
						$metadata = unserialize($metadata);
					}
					if(isset($metadata['position']) && !empty($percentage)){
						switch ($metadata['position']){
							case 'bottom':
								$return .= do_shortcode('[reviewr id="'. $post->ID .'" position="bottom"]');
								break;

							case 'top':
								$return = do_shortcode('[reviewr id="'. $post->ID .'" position="bottom"]') . $return;
								break;
							
							default:
								# code...
								break;
						}
					}
				}
			}
		return $return;
	}

	/**
	 * Build Shortcode
	 *
	 * @since 1.0
	 */
	function build_shortcode($atts, $content = null){
		extract(shortcode_atts(array(
		  'id'				=>	NULL,
		  'position'		=>	'default',
		  'user_review'		=>	false
	   	), $atts));
		global $post;
		if(empty($id)){
			$id = $post->ID;
		}

	   	$html = ''; //initialize return string
	   	$metadata = array();
	   	$percentage = 0;
	   	$criteria = array();
	   	$allow = true;
	   	$text_opts = (array) get_option('wpreviewr_text_settings');
	   	
	   	if(is_user_logged_in()){
	   		$exists = check_user_exists( get_current_user_id(), $id );
	   	}else{
	   		$exists = check_reviewr_exists( reviewr_getip(), $id );
	   	}
	   	if(!empty($id)){
	   		$metadata = get_post_meta($id, '_reviewr_info', true);
			if(!empty($metadata)){
				$metadata = unserialize($metadata);
			}
			$_total = get_reviewr_totals( $id );
			$percentage = get_post_meta($id, '_reviewr_percentage', true);
	   	
			if(isset($metadata['criteria']) && !empty($metadata['criteria'])){
		   		foreach ($metadata['criteria'] as $k => $v){
		   			$totals = get_reviewr_total($id, $v['id']);
		   			$occur = get_reviewr_occurence($id, $v['id']);
		   			$score = intval($totals) + $v['score'];
					$score = $score / ($occur + 1);
					$score = intval($score);

		   			$criteria[$v['id']] = array(
	   										'occurence' => $occur,
	   										'total' => $totals,
	   										'value' => intval($v['score']),
	   										'percentage' => $score
	   									);
		   		}
		   	}
	   	}
	   	$percent = 360 * ($percentage/100);
	   	$pr = 0;
	   	if(!empty($criteria)){
	   		foreach ($criteria as $cr => $crv) {
	   			$pr += $crv['percentage'];
	   		}
	   	}
	   	if($pr > 0){
	   		$percent = $pr / count($criteria);
	   		$percent = intval($percent);
	   		$percent = 360 * ($percent/100);
	   	}

	   	if(!isset($this->general_settings['permission']['users']) && is_user_logged_in() ){
	   		$allow = false;
	   	}
	   	if(!isset($this->general_settings['permission']['guest']) && !is_user_logged_in() ){
	   		$allow = false;
	   	}

	   	ob_start();
		?>
		<!-- Start Review -->
		<!-- Rating Schema for google -->
		<span itemscope itemtype="http://data-vocabulary.org/Review-aggregate" class="wp-reviewr-aggregate"> 
		   <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
		         <span itemprop="worst">0</span> 
		         <span itemprop="average"><?php echo intval($percentage);?></span>
		         <span itemprop="best">100</span> 
		   </span>
		   <span itemprop="count"><?php echo $_total+1;?></span>
		</span> 
		<!-- End Rating Schema for google -->

		<div class="wp-reviewr-container wp-reviewr wp-reviewr-<?php echo $position;?>" id='wp-reviewr-1'>
			<div class="wp-reviewr-inner">
				<div class="wp-reviewr-left">
					<div class="wp-reviewr-prcnt-con">
						<div class="wp-reviewr-border">
					        <div class="wp-reviewr-circle">
					            <span class="wp-reviewr-prcnt" data-percent="<?php echo intval($percent);?>"><?php echo $percentage;?>%</span>
					        </div>
					    </div>
					</div>
					<div class="wp-reviewr-title">
						<?php $num = reset($criteria);
							$count = 1;
							if(isset($num['occurence']) && !empty($num['occurence'])){
								$count += $num['occurence'];
							}
						?>
						<?php echo sprintf( _n( 'Average Rating', 'Average based on %d Ratings', intval($_total) + 1, 'wp-reviewr' ), $_total+1 );?> <br />
						
					</div>
				</div><!-- end reviewr left block -->

				<div class="wp-reviewr-right">
					<div class="wp-reviewr-content-rate">
						<div class="wp-reviewr-description">
							<?php if(isset($metadata['title']) && !empty($metadata['title'])){ echo '<h3>'. $metadata['title'] .'</h3>'; }?>
							<?php if(isset($metadata['summary']) && !empty($metadata['summary'])){ echo '<p>'. $metadata['summary'] .'</p>'; }?>
						</div>
						<?php if(isset($metadata['criteria']) && !empty($metadata['criteria'])) : ?>
							<div class="wp-reviewr-ratings">
								<ul>
							<?php foreach ($metadata['criteria'] as $key => $value) {
							?>
								<li>
									<div class="wp-reviewr-row">
										<div class="wp-reviewr-criteria">
											<?php if(isset($value['title'])){ echo $value['title']; }?>
										</div>
										<div class="wp-reviewr-rating">
											<div class="wp-reviewr-progress">
												<div class="wp-reviewr-bar" data-percent="<?php if(isset($criteria[$value['id']]['percentage'])){ echo intval($criteria[$value['id']]['percentage']); }?>"><span><?php if(isset($criteria[$value['id']]['percentage'])){ echo intval($criteria[$value['id']]['percentage']) . '%'; }?></span></div>
											</div>
										</div>
									</div>
								</li>
							<?php } ?>
								</ul>
							</div>
						<?php endif; ?><!-- end ratings-->
					</div>
				</div><!-- end reviewr right block -->

				<div class="wp-reviewr-clear"></div>
			</div>
		</div>
		<!-- End Review -->
		<?php
		$html = ob_get_clean();
		return $html;
	}
	function register_shortcodes(){
	   add_shortcode('reviewr', array($this, 'build_shortcode'));
	}
}
$wp_reviewr = new WP_REVIEWR();
?>