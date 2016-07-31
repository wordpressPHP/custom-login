<?php

/**
 * Class CL_Dependency_Check
 */
class CL_Dependency_Check {

	/**
	 * @var string
	 */
	const MIN_PHP_VERSION = '5.3.0';

	/**
	 * CL_Dependency_Check constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {

		try {
			if ( $this->is_not_min_version() ) {
				throw new Exception(
					sprintf( __( 'Custom Login requires PHP version greater than %s, you\'re on %s.
					Please disable this plugin and contact your host to request an upgrade.%s', 'custom-login' ),
						self::MIN_PHP_VERSION,
						PHP_VERSION,
						CL_Admin_Notices::ERROR_CODE
					)
				);
			}
		} catch( Exception $e ) {
			new CL_Admin_Notices( $e );
			$this->deactivate_plugin();
		}
	}

	/**
	 * @return bool
	 */
	private function is_not_min_version() {
		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' );
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	private function deactivate_plugin() {

//		if ( ! function_exists( 'deactivate_plugins' ) ) {
//			require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'plugin.php';
//		}
//
//		deactivate_plugins( CUSTOM_LOGIN_BASENAME );
	}
}
