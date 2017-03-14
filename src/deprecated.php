<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'CUSTOMLOGIN' ) ) {
	/**
	 * The main function responsible for returning the one true
	 * Instance to functions everywhere.
	 *
	 * @since 2.0.0
	 * @deprecated 4.0.0
	 *
	 * @return Custom_Login_Bootstrap
	 */
	function CUSTOMLOGIN() {
		_deprecated_function( __FUNCTION__, '4.0.0', 'Custom_Login_Bootstrap::get_instance()' );

		return Custom_Login_Bootstrap::get_instance();
	}
}
