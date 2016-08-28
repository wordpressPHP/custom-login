<?php

/**
 * Class CL_Dependency_Check
 */
class CL_Dependency_Check {

    const MIN_PHP_VERSION = '5.3.0';

    /**
     * CL_Dependency_Check constructor.
     *
     * @throws CL_Exception_Invalid_Version
     */
    public function __construct() {
        try {
            if ( $this->is_not_min_version() ) {
                throw new CL_Exception_Invalid_Version(
                    sprintf( __( 'Custom Login requires PHP version greater than %s, you\'re on %s. Please disable this plugin and contact your host to request an upgrade.%s', 'custom-login' ),
                        self::MIN_PHP_VERSION,
                        PHP_VERSION,
                        CL_Admin_Notices::ERROR_CODE
                    )
                );
            }
        } catch ( CL_Exception_Invalid_Version $e ) {
            new CL_Admin_Notices( $e );
            $this->deactivate_plugin();
        }
    }

    /**
     * Do a version compare of the current PHP Version this is installed on.
     *
     * @return bool
     */
    private function is_not_min_version() {
        return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' );
    }

    /**
     * Deactivate the plugin.
     */
    private function deactivate_plugin() {
        if ( ! ! false ) { // Don't want this to fire...
            if ( ! function_exists( 'deactivate_plugins' ) ) {
                require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'plugin.php';
            }

            deactivate_plugins( CUSTOM_LOGIN_BASENAME );
        }
    }
}
