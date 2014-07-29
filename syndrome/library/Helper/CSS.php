<?php
/**
 * Helper_CSS - Class
 *
 * @author Man Hoang
 * @name Helper_CSS
 */
class Helper_CSS {

	/**
	 * An list of properties that the preprocessor should process
	 * @var array
	 */
	protected static $css3_scanned_properties = array(
		'opacity',
		'box-shadow',
		'box-sizing',
		'border-radius',
		'transform',
		'border-top-left-radius',
		'border-top-right-radius',
		'border-bottom-left-radius',
		'border-bottom-right-radius',
		'transform',
		'transform-origin',
		'transition',
		'transition-delay',
		'transition-duration',
		'transition-property',
		'transition-timing-function',
		'background-image'
	);

	/**
	 * Constructor initializes parent vars
	 *
	 * @return CSS
	 */
	public function __construct($files = array()) {
	}

	/**
	 * Preprocessor function that handles the logic to modify a file before being minified or outputted
	 *
	 * @param string $content
	 * @return string
	 */
	protected function preProcessor($content) {
		// If we disabled the preprocessor, then we don't want to do anything
		return preg_replace_callback('/((?:-moz-|-webkit-|-o-|-ms-)?(?:' . implode('|', self::$css3_scanned_properties) . '))\s*:\s*(.+?)\s*(!important)?\s*(?:[;\n\r}])/', array(
			$this,
			'css3Preprocessor'
		), $content);
	}

	/**
	 * Css 3 preprocessor for adding the correct vendor prefixed properties for css 2 & css 3
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function css3Preprocessor($matches) {
		// normalize the matched items
		$matches['property'] = $matches[1];
		$matches['value'] = $matches[2];
		$matches['important'] = isset($matches[3]) ? $matches[3] : '';
		if(strpos($matches[1], '-') === 0) {
			return $matches[0];
		}
		$out = '';
		switch($matches[1]) {
			case 'opacity':
				$out .= 'filter:Alpha(Opacity=' . round($matches['value'] * 100) . ');';
				$out .= $matches[0];
				break;
			case 'box-shadow':
			case 'box-sizing':
				$out .= '-moz-' . $matches[0];
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'border-radius':
				$out .= '-moz-' . $matches[0];
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'border-top-left-radius':
				$out .= '-moz-border-radius-topleft:' . $matches['value'] . ';';
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'border-top-right-radius':
				$out .= '-moz-border-radius-topright:' . $matches['value'] . ';';
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'border-bottom-left-radius':
				$out .= '-moz-border-radius-bottomleft:' . $matches['value'] . ';';
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'border-bottom-right-radius':
				$out .= '-moz-border-radius-bottomright:' . $matches['value'] . ';';
				$out .= '-webkit-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'transform':
			case 'transform-origin':
			case 'transition':
			case 'transition-delay':
			case 'transition-duration':
			case 'transition-property':
			case 'transition-timing-function':
				$out .= '-moz-' . $matches[0];
				$out .= '-webkit-' . $matches[0];
				$out .= '-o-' . $matches[0];
				$out .= '-ms-' . $matches[0];
				$out .= $matches[0];
				break;
			case 'background-image':
				if(!preg_match('/^(?:repeating-)?(?:linear|radial)-gradient/', $matches['value'])) {
					break;
				}
				// additional processing
				if(strpos($matches['value'], 'linear-gradient') === 0) {
					$out .= $this->processCss3LinearGradient($matches);
				}
				$out .= $matches['property'] . ':-webkit-' . $matches['value'] . $matches['important'] . ';';
				$out .= $matches['property'] . ':-moz-' . $matches['value'] . $matches['important'] . ';';
				$out .= $matches['property'] . ':-ms-' . $matches['value'] . $matches['important'] . ';';
				$out .= $matches['property'] . ':-o-' . $matches['value'] . $matches['important'] . ';';
				$out .= $matches['property'] . ':' . $matches['value'] . $matches['important'] . ';';
				break;
		}
		return $out ? $out : $matches[0];
	}

	/**
	 * Generates a cross browser css gradient declarations
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function processCss3LinearGradient(array $matches) {
		$out = '';

		// break up the gradient
		$parts = array_map('trim', explode(',', preg_replace('/^\w+-\w+\(|\)$/', '', $matches['value'])));
		// for parsing ms filter syntax (IE5.5 - IE8)
		$ms_filter = $this->generateMsFilterGradientFromLinearGradient($matches, $parts);
		if($ms_filter) {
			$out .= $ms_filter;
		}
		// for parsing webkit-gradient syntax (Chrome 7+ & Safari 5.03+ - Safari 5.1-)
		$webkit_gradient = $this->generateWebkitGradientFromLinearGradient($matches, $parts);
		if($webkit_gradient) {
			$out .= $webkit_gradient;
		}
		return $out;
	}

	/**
	 * Generates the complete -webkit-gradient css value for a gradient based on the linear gradient syntax
	 *
	 * @param array $matches
	 * @param array $gradient_parts
	 * @return string|bool
	 */
	protected function generateWebkitGradientFromLinearGradient(array $matches, array $gradient_parts) {
		$webkit_gradient = array();
		// linear gradient
		$webkit_gradient['type'] = 'linear';
		// get the starting point
		$starting_point = array_shift($gradient_parts);
		// we cannot do angles
		if(preg_match('/deg|rad|grad|turn/', $starting_point)) {
			return false;
		}
		$points = $this->determineWebkitLinearGradientPoints($starting_point);
		$webkit_gradient['point'] = $points['left'] . ' ' . $points['top'] . ', ' . $points['right'] . ' ' . $points['bottom'];
		// get the color stops
		$webkit_gradient['colors'] = $this->determineWebkitLinearGradientColors(implode(',', $gradient_parts));
		// if we cannot accurately generate a color map, then stop processing this gradient
		if(!$webkit_gradient['colors']) {
			return false;
		}
		return $matches['property'] . ':-webkit-gradient(' . implode(', ', $webkit_gradient) . ')' . $matches['important'] . ';';
	}

	/**
	 * Generates the complete ms filter css property for a gradient based on the linear gradient syntax
	 *
	 * @param array $matches
	 * @param array $gradient_parts
	 * @return string|bool
	 */
	protected function generateMsFilterGradientFromLinearGradient(array $matches, array $gradient_parts) {
		$ms_filter = array();
		// linear gradient
		$ms_filter['type'] = 'linear';
		// get the starting point
		$starting_point = array_shift($gradient_parts);
		switch($starting_point) {
			case 'left':
			case 'right':
				$ms_filter['type'] = 1;
				break;
			case 'top':
			case 'bottom':
			case 'center':
				$ms_filter['type'] = 0;
				break;
			default:
				// we couldn't determine a gradient direction
				return false;
		}
		// get the color stops
		$ms_filter['colors'] = $this->determineMsFilterGradientColors(implode(',', $gradient_parts), $starting_point);
		// if we cannot accurately generate a color map, then stop processing this gradient
		if(empty($ms_filter['colors'])) {
			return false;
		}
		return 'filter:progid:DXImageTransform.Microsoft.gradient(StartColorStr=\'#' . $ms_filter['colors'][0] . '\', EndColorStr=\'#' . $ms_filter['colors'][1] . '\' GradientType=' . $ms_filter['type'] . ')' . $matches['important'] . ';';
	}

	/**
	 * Determind the start and end positions for -webkit-gradient based in linear-gradient syntax
	 *
	 * @param string $point
	 * @return string
	 */
	protected function determineWebkitLinearGradientPoints($point) {
		$starting_point = preg_split('/\s+/', $point);
		// only starting from one side
		$points = array(
			'left' => 0,
			'top' => 0,
			'right' => 0,
			'bottom' => 0
		);
		foreach($starting_point as $point) {
			switch($point) {
				case 'top':
				case 'center':
					$points['top'] = 0;
					$points['bottom'] = '100%';
					break;
				case 'left':
					$points['left'] = 0;
					$points['right'] = '100%';
					break;
				case 'bottom':
					$points['bottom'] = 0;
					$points['top'] = '100%';
					break;
				case 'right':
					$points['right'] = 0;
					$points['left'] = '100%';
					break;
			}
		}
		return $points;
	}

	/**
	 * Determine the colors and color stops for the linear gradient
	 *
	 * @param string $colors
	 * @return array
	 */
	protected function determineLinearGradientColorsAndStops($colors) {
		$matches = array();
		preg_match_all('/((?:rgb|rgba)(?:\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(?:,\s*[0-9.]+\s*)?\))|#[0-9a-f]+)(?:\s+(\d+)%)?\s*(?:,\s*|$)/i', $colors, $matches);
		$colors = array();
		$l = count($matches[1]) - 1;
		foreach($matches[1] as $index => $color) {
			if($color === 'px') {
				return array();
			}
			$colors[] = array(
				'color' => $color,
				'stop' => intval($matches[2][$index] === '' ? ($index === 0 ? 0 : ($index === $l ? 100 : -1)) : $matches[2][$index])
			);
		}
		// fill in the gaps in the color stops
		foreach($colors as $index => &$color) {
			if($color['stop'] == -1) {
				$end_pos = 100;
				$gap = 1;
				// find out how many empty variables there are after this
				for($i = $index + 1; $i <= $l; $i++) {
					if($colors[$i]['stop'] > -1) {
						$end_pos = $colors[$i]['stop'];
						break;
					}
					$gap++;
				}
				$start_pos = $colors[$index - 1]['stop'];
				$range = $end_pos - $start_pos;
				$per_stop = round($range / ($gap + 1));
				$iterator = 1;

				// fill in the blanks
				for($i = $index; $i < $gap + $index; $i++) {
					$colors[$i]['stop'] = $start_pos + ($per_stop * $iterator);
					$iterator++;
				}
			}
		}
		return $colors;
	}

	/**
	 * Determine the color groups for a -webkit-gradient based on the linear-gradient syntax color groups
	 *
	 * @param string $colors
	 * @return string
	 */
	protected function determineWebkitLinearGradientColors($colors) {
		$groups = $this->determineLinearGradientColorsAndStops($colors);
		if(empty($groups)) {
			return '';
		}
		$pieces = array();
		foreach($groups as $group) {
			if($group['stop'] === 0) {
				$pieces[] = 'from(' . $group['color'] . ')';
			} elseif($group['stop'] === 100) {
				$pieces[] = 'to(' . $group['color'] . ')';
			} else {
				$pieces[] = 'color-stop(' . ($group['stop'] / 100) . ', ' . $group['color'] . ')';
			}
		}
		return implode(', ', $pieces);
	}

	/**
	 * Determine the color groups for a ms filter gradient based on the linear-gradient syntax color groups
	 *
	 * @param string $colors
	 * @param string $direction
	 * @return string
	 */
	protected function determineMsFilterGradientColors($colors, $direction) {
		$groups = $this->determineLinearGradientColorsAndStops($colors);
		if(empty($groups) || count($groups) !== 2 || $groups[0]['stop'] !== 0 || $groups[1]['stop'] !== 100) {
			return array();
		}
		$pieces = array();

		foreach($groups as $group) {
			$pieces[] = $this->convertCssColortoHex($group['color']);
		}

		if($direction == 'bottom' || $direction == 'right') {
			$pieces = array_reverse($pieces);
		}
		return $pieces ? array_filter($pieces) : array();
	}

	/**
	 * Converts a css color declaration to a hex value aaRRGGBB
	 *
	 * @param string $color
	 * @return string
	 */
	protected function convertCssColortoHex($color) {
		$color = strtolower($color);
		// check to see if we already have a hex
		if(strpos($color, '#') === 0) {
			$color = str_replace('#', '', $color);
			// expand shorthand
			if(strlen($color) === 3) {
				$parts = str_split($color);
				$color = $parts[0] . $parts[0] . $parts[1] . $parts[1] . $parts[2] . $parts[2];
			}
			return $color;
		}
		// check for rgba
		if(strpos($color, 'rgb') === 0) {
			$opacity = '';
			$parts = explode(',', preg_replace('/^rgba?\(|\)$/', '', $color));
			if(count($parts) === 4) {
				$opacity = array_pop($parts);
			}
			foreach($parts as $i => $part) {
				$parts[$i] = str_pad(dechex($part), 2, '0', STR_PAD_LEFT);
			}
			if($opacity !== '') {
				$opacity = str_pad(dechex(round($opacity * 255)), 2, '0', STR_PAD_LEFT);
			}
			return $opacity . implode('', $parts);
		}
		return null;
	}
}