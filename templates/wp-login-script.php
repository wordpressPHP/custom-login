<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Should we cache this?
$use_cache = ! ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV );

$js_atts = CL_WP_Login::$js_atts;

// Return NULL if there are zero attributes in our array.
if ( empty( $js_atts ) ) {
	return null;
}

// Cache ALL THE THINGS!
if ( false === ( $js = get_transient( $js_atts[ 'trans_key' ] ) ) ) {

	$js = '';
	
	if ( ! $use_cache ) {
		$js .= "/**\n *\n" . print_r( $js_atts, true ) . " */\n\n";
	}

	$js .= "
/**
 * Custom Login by Austin Passy
 *
 * Plugin URI  : https://frosty.media/plugins/custom-login/
 * Version     : {$js_atts[ 'version' ]}
 * Author URI  : http://austin.passy.co/
 * Add-on Plugins  : https://frosty.media/plugin/tag/custom-login-extension/
 */\n\n";
	
	/* Custom user input */
	if ( ! empty( $js_atts[ 'custom_jquery' ] ) ) {
		$js .= "\n\n/* Custom JS */\n";
		$js .= wp_specialchars_decode( stripslashes( $js_atts[ 'custom_jquery' ] ), 1 );
		$js .= "\n\n";
	}

	// Cache ALL THE Javascript!
	if ( $use_cache ) {
		set_transient( $js_atts[ 'trans_key' ], $js, ( YEAR_IN_SECONDS / 3 ) );
	}
}

echo $js;