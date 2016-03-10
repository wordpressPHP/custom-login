<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Scripts_Styles
 */
class CL_Scripts_Styles {

	/**
	 * Helper function to convert HEX to RGB
	 *
	 * @link http://css-tricks.com/snippets/php/convert-hex-to-rgb/#comment-355641
	 *
	 * @param string $color
	 *
	 * @return array
	 */
	public static function hex2rgb( $color ) {

		if ( $color[ 0 ] == '#' ) {
			$color = substr( $color, 1 );
		}
		if ( strlen( $color ) == 6 ) {
			list( $r, $g, $b ) = array(
				$color[ 0 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 3 ],
				$color[ 4 ] . $color[ 5 ],
			);
		} elseif ( strlen( $color ) == 3 ) {
			list( $r, $g, $b ) = array(
				$color[ 0 ] . $color[ 0 ],
				$color[ 1 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 2 ],
			);
		} else {
			return false;
		}
		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return array( 'red' => $r, 'green' => $g, 'blue' => $b );
	}
	
	/**
	 * Helper function to convert RGB to HEX
	 *
	 * @link http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
	 *
	 * @param string $rgb
	 *
	 * @return string
	 */
	public static function rgb2hex( $rgb ) {

		$hex = "#";
		$hex .= str_pad( dechex( $rgb[ 0 ] ), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex( $rgb[ 1 ] ), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex( $rgb[ 2 ] ), 2, "0", STR_PAD_LEFT );

		return $hex; // returns the hex value including the number sign (#)
	}
	
	/**
	 * Helper function to convert RGBA to HEX
	 *
	 * @link http://stackoverflow.com/questions/5798129/regular-expression-to-only-allow-whole-numbers-and-commas-in-a-string
	 *
	 * @param string $rgba
	 *
	 * @return string
	 */
	public static function rgba2hex( $rgba ) {

		$rgba = preg_replace(
			array(
				'/[^\d,]/',    // Matches anything that's not a comma or number.
				'/(?<=,),+/',  // Matches consecutive commas.
				'/^,+/',       // Matches leading commas.
				'/,+$/'        // Matches trailing commas.
			), '', $rgba );
		$rgba = explode( ',', $rgba );
		
		$hex = "#";
		$hex .= str_pad( dechex( $rgba[ 0 ] ), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex( $rgba[ 1 ] ), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex( $rgba[ 2 ] ), 2, "0", STR_PAD_LEFT );
		
		return $hex; // returns the hex value including the number sign (#)
	}
	
	/**
	 * Helper function to convert RGB(A) to array
	 *
	 * @return    bool
	 */
	public static function is_rgba( $str ) {
		$is_rgba = strpos( $str, 'rgba' );
		
		if ( false === $is_rgba ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Browser prefixes
	 *
	 * @since 1.1 (1/8/13)
	 *
	 * @param $input
	 * @param $option
	 *
	 * @return string
	 */
	public static function prefixit( $input, $option ) {

		$prefixes = array( '-webkit-', '-moz-', '-ms-', '-o-', '' );
		
		$output = "\n\t";

		foreach ( $prefixes as $prefix ) {
			$output .= self::trailingsemicolonit( $prefix . $input . ': ' . esc_attr( $option ) );
		}
		$output .= self::trailingsemicolonit( $prefix . $input . ': ' . esc_attr( $option ) );
		
		return $output;
	}

	/**
	 * Add a semi colon
	 *      - remove `esc_attr` since it's encoding single quotes in image urls with quotes.
	 *
	 * @since 1.1 (1/8/13)
	 * @updated 1.1.1 (1/9/13)
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function trailingsemicolonit( $input ) {

		$output = rtrim( $input, ';' );
		$output .= ";\n\t";
		
		return $output;
	}
	
	/**
	 * Open a new CSS rule
	 *
	 * @since 2.0
	 *
	 * @param string $rule
	 *
	 * @return string
	 */
	public static function cssrule( $rule ) {

		$output = rtrim( $rule, '{' );
		$output .= " {\n\t";
		
		return $output;
	}
}
