<?php

namespace PassyCo\CustomLogin;

use PassyCo\CustomLogin\Admin\AdminNotices;
use PassyCo\CustomLogin\Exceptions\InvalidVersion;

/**
 * Class DependencyCheck
 *
 * @package PassyCo\CustomLogin
 */
class DependencyCheck {

    const MIN_PHP_VERSION = '5.6.0';

    /**
     * @throws InvalidVersion
     */
    public function init() {
        try {
            if ( $this->isNotMinVersion() ) {
                throw new InvalidVersion(
                    sprintf( __( 'Custom Login requires PHP version greater than %s, you\'re on %s. Please disable this plugin and contact your host to request an upgrade.%s', 'custom-login' ),
                        self::MIN_PHP_VERSION,
                        PHP_VERSION,
                        AdminNotices::ERROR_CODE
                    )
                );
            }
        } catch ( InvalidVersion $e ) {
            AdminNotices::renderNotice( $e );
            $this->deactivatePlugin();
        }
    }

    /**
     * Do a version compare of the current PHP Version this is installed on.
     *
     * @return bool
     */
    private function isNotMinVersion() {
        return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' );
    }

    /**
     * Deactivate the plugin.
     */
    private function deactivatePlugin() {
        if ( ! ! false ) { // Don't want this to fire...
            if ( ! function_exists( 'deactivate_plugins' ) ) {
                require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'plugin.php';
            }

            deactivate_plugins( CUSTOM_LOGIN_BASENAME );
        }
    }
}
