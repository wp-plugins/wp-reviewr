<?php
/*
 * Creates function calls
 */
class WP_PHPBITS {
	private $domain = 'wp-reviewr';

	/*
	 * create form fields for table
	 * @return fields inside tr
	 */
	function create_fields($type,$params = array()){
		$return = '<tr valign="top">';
		$return .= '<th scope="row"><label for="'. $params['id'] .'">'. __( $params['label'] ,$this->domain) .'</label></th>';
		switch ($type) {
			case 'textbox':
				$return .= '<td>';
				if(isset($params['size'])){
					$attr = 'size="'. $params['size'] .'"';
				}else{
					$attr = 'class="widefat"';
				}
				$return .= '<input type="text" name="'. $params['name'] .'" '. $attr .' id="'. $params['id'] .'"  value="'. $params['value'] .'" />';
				if(isset($params['desc']) && !empty($params['desc'])){
					$return .= '<br /><small>'. __($params['desc'], $this->domain) .'</small>';
				}
				$return .= '</td>';
				break;
			case 'textarea':
				$return .= '<td>';
				$return .= '<textarea name="'. $params['name'] .'" id="'. $params['id'] .'" class="widefat" rows="10" >'. $params['value'] .'</textarea>';
				if(isset($params['desc']) && !empty($params['desc'])){
					$return .= '<br /><small>'. __($params['desc'], $this->domain) .'</small>';
				}
				$return .= '</td>';
				break;
			case 'checkbox':
				$return .= '<td>';
				$return .= '<input type="checkbox" name="'. $params['name'] .'" id="'. $params['id'] .'" class="widefat" value="'. $params['key'] .'" '. ((!empty($params['value'])) ? 'checked="checked"' : '') .' />';
				if(isset($params['desc']) && !empty($params['desc'])){
					$return .= '<small>'. __($params['desc'], $this->domain) .'</small>';
				}
				$return .= '</td>';
				break;
			case 'colorpicker':
				$return .= '<td>';
				$return .= '<input type="text" name="'. $params['name'] .'" id="'. $params['id'] .'" class="'. $this->domain .'-colorpicker" value="'. $params['value'] .'" />';
				if(isset($params['desc']) && !empty($params['desc'])){
					$return .= '<br /><small>'. __($params['desc'], $this->domain) .'</small>';
				}
				$return .= '</td>';
				break;

			case 'slider':
				$return .= '<td>';
				$return .= '<table class="widefat" style="border:0px;">';
				$return .= '<tr>';
				$return .= '<td class="no-padding">
								<div class="reviewr-admin-slider" id="reviewr-score-fld-'. $params['id'] .'" data-target="#'. $params['id'] .'" data-value="'. intval($params['value']) .'"></div>
							</td>
							<td style="width:60px;" class="no-padding">
								<input type="text" id="'. $params['id'] .'" class="reviewr-admin-slider-input" data-target="#reviewr-score-fld-'. $params['id'] .'" name="'. $params['name'] .'" value="'. $params['value'] .'%" size="5" />
							</td>';
				$return .= '</tr>';
				$return .= '</table>';
				if(isset($params['desc']) && !empty($params['desc'])){
					$return .= '<br /><small>'. __($params['desc'], $this->domain) .'</small>';
				}
				$return .= '</td>';
				break;
			
			default:
				# code...
				break;
		}
		$return .= '</tr>';

		return trim($return);
	}

	/*
	 * creates form table
	 * @return form table with fields
	 */
	function create_table($fields = array()){
		$return = '<table class="form-table">';
		$return .= '<tbody>';
			if(!empty($fields)){
				foreach ($fields as $key => $value) {
					$return .= $this->create_fields( $value['type'], $value['params'] );
				}
			}
		$return .= '</tbody>';
		return $return .= '</table>';
	}

	/*
	 * check if the variable is set and not empty
	 * not that elegant but this will work this time
	 * @return value if not empty
	 */
	function is_set($variable, $key1 = null, $key2 = null){
		if(!empty($key2)){
			if(isset($variable[ $key1 ][ $key2 ]) && !empty($variable[ $key1 ][ $key2 ])){
				return $variable[ $key1 ][ $key2 ];
			}
		}else if(!empty($key1)){
			if(isset($variable[ $key1 ]) && !empty($variable[ $key1 ])){
				return $variable[ $key1 ];
			}
		}else{
			return '';
		}
	}
}
?>