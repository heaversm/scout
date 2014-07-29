<?php
class Helper_FormModel {

	private static function writeFreeTextElement($element_type, $attributes) {
		$element = '<'.$element_type;
		foreach($attributes as $attribute => $value) {
			if($attribute == 'value' && $element_type == 'textarea') {
				continue;
			}
			$element .= ' '.$attribute.'="' . str_replace('"', '&quot;', $value) . '"';
		}
		$element .= $element_type == 'textarea' ? ' >'.$attributes['value'].'</textarea>' : ' />' ;
		return $element;
	}

	private static function writeSelectElement($attributes) {
		$element = '<select';

		foreach($attributes as $attribute => $value) {
			if(!is_array($value) && !in_array($attribute, FormSchema::$excluded_select_attributes)) {
				$element .= ' '.$attribute.'="'.$value.'"';
			}
		}

		$element .= '>';

		if(isset($attributes['empty_default'])) {
			$element .= '<option value="">Please select</option>';
		}

		$data_set = call_user_func($attributes['options']);
		foreach($data_set as $key => $value) {
			$option_value = (isset($attributes['use_value'])) ? $value : $key ;
			$element .= '<option value="'.$option_value.'">'.$value.'</option>';
			if(!empty($attributes['value']) && $attributes['value'] !== '' && $attributes['value'] == $option_value) {
				$element = str_replace('value="'.$option_value, 'selected="selected" value="'.$option_value, $element);
			}
		}


		$element .= '</select>';
		return $element;
	}

	private static function writeMultiSelectElement($attributes) {
		$name = $attributes['name'];
		$element = array();
		for($i = 0; $i < $attributes['multi_select']; $i++) {
			$element[$i] = '<select name="'.$name.'['.$i.']" class="'.$attributes['class'].'-'.$i.'">';

			if(isset($attributes['empty_default'])) {
				$element[$i] .= '<option value="">Please select</option>';
			}

			$data_set = call_user_func($attributes['options']);
			foreach($data_set as $key => $value) {
				if(is_array($value)) {
					$element[$i] .= '<optgroup label="'.$key.'">';
					foreach($value as $group_value) {
						$option_value = (isset($attributes['use_value'])) ? $group_value : $key ;
						$element[$i] .= '<option value="'.$option_value.'">'.$group_value.'</option>';

						if(isset($attributes['value'][$i]) && $attributes['value'][$i] !== '' && $attributes['value'][$i] == $group_value) {
							$element[$i] = str_replace('value="'.$group_value, 'selected="selected" value="'.$group_value, $element[$i]);
						}
					}
					$element[$i] .= '</optgroup>';
				} else {
					$option_value = (isset($attributes['use_value'])) ? $value : $key ;
					$element[$i] .= '<option value="'.$option_value.'">'.$value.'</option>';
					if(isset($attributes['value'][$i]) && $attributes['value'][$i] !== '' && $attributes['value'][$i] == $value) {
						$element[$i] = str_replace('value="'.$value, 'selected="selected" value="'.$value, $element[$i]);
					}
				}
			}

			$element[$i] .= '</select>';
		}
		return $element;
	}

	private static function writeDateElement($attributes) {
		if($attributes['value'] != '' && is_array($attributes['value'])) {
			$values = array(
				'month' => $attributes['value']['month'],
				'day' => $attributes['value']['day'],
				'year' => $attributes['value']['year'],
			);
		} else {
			$date = getdate();
			$values = array(
				'month' => $date['mon'],
				'day' => $date['mday'],
				'year' => $date['year'],
			);
		}
		$name = $attributes['name'];
		$required = isset($attributes['required']) ? 'required="1"' : '' ;
		$element = array();
		foreach($attributes['options'] as $part => $callback) {
			$element[$part] = '<select name="'.$name.'['.$part.']" class="'.$attributes['class'].'-'.$part.'" '.$required.'>';

			$data_set = call_user_func($callback);
			foreach($data_set as $key => $value) {
				$element[$part] .= '<option value="'.$key.'">'.$value.'</option>';
			}

			if(!empty($values[$part])) {
				$element[$part] = str_replace('value="'.$values[$part], 'selected="selected" value="'.$values[$part], $element[$part]);
			}

			$element[$part] .= '</select>';
		}
		return $element;
	}

	public static function writeResponse(array $response) {
		if(empty($response)) {
			return '';
		}

		$class = $response['code'] === Error::CODE_ERROR ? 'error' : 'success' ;
		return '<div class="form-response '. $class .'"><p>'.$response['message'].'</p></div>';
	}

	public static function writeInput($collection, $name, $value) {
		$attributes = FormSchema::getSchemaProperties($collection, $name);
		$attributes['name'] = $name;
		$attributes['id'] = $name;
		$attributes['type'] = (strpos($name, 'password') !== false) ? 'password' : 'text' ;
		$attributes['value'] = $value;
		$attributes['class'] = Helper_Format::slug($name);
		return self::writeFreeTextElement('input', $attributes);
	}

	public static function writeFileInput($collection, $name, $value) {
		$attributes = FormSchema::getSchemaProperties($collection, $name);
		$attributes['name'] = $name;
		$attributes['id'] = $name;
		$attributes['type'] = 'file';
		$attributes['value'] = $value;
		$attributes['class'] = Helper_Format::slug($name);
		return self::writeFreeTextElement('input', $attributes);
	}

	public static function writeHiddenInput($collection, $name, $value) {
		$attributes = FormSchema::getSchemaProperties($collection, $name);
		$attributes['name'] = $name;
		$attributes['id'] = $name;
		$attributes['type'] = 'hidden';
		$attributes['value'] = $value;
		$attributes['class'] = Helper_Format::slug($name);
		return self::writeFreeTextElement('input', $attributes);
	}

	public static function writeTextarea($collection, $name, $value) {
		$attributes = FormSchema::getSchemaProperties($collection, $name);
		$attributes['name'] = $name;
		$attributes['id'] = $name;
		$attributes['value'] = $value;
		$attributes['class'] = Helper_Format::slug($name);
		return self::writeFreeTextElement('textarea', $attributes);
	}

	public static function writeSelect($collection, $name, $value) {
		$attributes = FormSchema::getSchemaProperties($collection, $name);
		$attributes['name'] = $name;
		$attributes['id'] = $name;
		$attributes['value'] = $value;
		$attributes['class'] = Helper_Format::slug($name);

		if(isset($attributes['multi_select'])) {
			return self::writeMultiSelectElement($attributes);
		}

		return (isset($attributes['date']) && $attributes['date'] === true)
			? self::writeDateElement($attributes)
			: self::writeSelectElement($attributes);
	}
}