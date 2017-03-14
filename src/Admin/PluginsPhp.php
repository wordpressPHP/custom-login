<?php

namespace PassyCo\CustomLogin\Admin;

use PassyCo\CustomLogin\AbstractLogin;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\HooksTrait;
use PassyCo\CustomLogin\WpHooksInterface;

/**
 * Class PluginsPhp
 *
 * @package PassyCo\CustomLogin\Admin
 */
class PluginsPhp extends AbstractLogin implements WpHooksInterface {

    use HooksTrait;

    /**
     * Add class hooks.
     */
    public function addHooks() {
        $this->addAction( 'load-plugins.php', [ $this, 'loadPluginsPhp' ] );
    }

    /**
     * Plugins row action links
     *
     * @since 3.0
     *
     * @param array $links already defined action links
     * @param string $file plugin file path and name being processed
     *
     * @return array $links
     */
    protected function pluginActionLinks( $links, $file ) {
        if ( $file === plugin_basename( $this->getCustomLogin()->getFile() ) ) {
            $link = '<a href="' . sprintf( admin_url( 'options-general.php?page=%s' ), CustomLogin::DOMAIN ) . '">' . esc_html__( 'Settings', CustomLogin::DOMAIN ) . '</a>';
            array_unshift( $links, $link );
        }

        return $links;
    }

    /**
     * Plugin row meta links
     *
     * @since 3.0
     *
     * @param array $input already defined meta links
     * @param string $file plugin file path and name being processed
     *
     * @return array $input
     */
    protected function pluginRowMeta( $input, $file ) {
        if ( $file === plugin_basename( $this->getCustomLogin()->getFile() ) ) {
            $links = [
                '<a href="' . sprintf( admin_url( 'options-general.php?page=%s/extensions' ), CustomLogin::DOMAIN ) . '">' . esc_html__( 'Extension Installer', CustomLogin::DOMAIN ) . '</a>',
                '<a href="https://frosty.media/plugin/tag/custom-login-extension/" target="_blank">' . esc_html__( 'Shop Extensions', CustomLogin::DOMAIN ) . ' <span class="dashicons dashicons-external"></span></a>',
            ];

            $input = array_merge( $input, $links );
        }

        return $input;
    }

    /**
     * Load additional hooks for this class.
     */
    private function loadPluginsPhp() {
        $this->addFilter( 'plugin_action_links', [ $this, 'pluginActionLinks' ], 10, 2 );
        $this->addFilter( 'plugin_row_meta', [ $this, 'pluginRowMeta' ], 10, 2 );
    }
}
