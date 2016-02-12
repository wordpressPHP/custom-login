<?php

/**
 * Class CL_Hookup
 */
class CL_Hookup {

	/**
	 * Add class hooks.
	 *
	 * @uses `custom_login_init` A custom action hook called on the `init` hook; priority 10
	 * @uses `custom_login_admin_init` A custom action hook called on the `init` hook; priority 10 but only if
	 *      the is_admin() condition is met.
	 */
	public static function add_hooks() {

		add_action( 'custom_login_init', array( __CLASS__, 'init' ) );
		add_action( 'custom_login_admin_init', array( __CLASS__, 'admin_init' ) );
	}

	/**
	 * Initialize front and back-end classes.
	 *
	 * @param bool $is_login_page Is the current page the 'wp-login.php' page?
	 */
	public static function init( $is_login_page ) {

		/**
		 * Instantiate classed not needed on the login page.
		 * This speeds up the login page.
		 */
		if ( ! $is_login_page ) {
			( new CL_Cron() )->add_hooks();
		} else {

		}

		( new CL_Login_Customizer() )->add_hooks();

		self::setup_settings_api();
	}

	/**
	 * Initialize backend only classes.
	 */
	public static function admin_init() {

		self::includes();
	}

	private static function includes() {
		require_once CUSTOM_LOGIN_DIR . 'includes/functions.php';
	}

	/**
	 * Sets up the Settings API and Default Settings classes.
	 */
	private static function setup_settings_api() {

		$cl_settings_api = CL_Settings_API::get_instance();
		$cl_settings_api->add_hooks();

		/**
		 * Since this class is called in the `custom_login_init` action hook,
		 * we need to make sure it's only loaded in the admin.
		 */
		if ( is_admin() ) {
			$cl_default_settings = new CL_Default_Settings( $cl_settings_api );
			$cl_default_settings->add_hooks();
			$cl_settings_api->set_sections( $cl_default_settings::get_registered_settings_sections() );
			$cl_settings_api->set_fields( $cl_default_settings::get_registered_settings_fields() );
		}
	}
}
