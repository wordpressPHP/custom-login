<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Admin_Notices
 */
class CL_Admin_Plugin_PHP {

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		add_action( 'load-plugins.php', function() {
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		});
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
	public function plugin_action_links( $links, $file ) {

		if ( CUSTOM_LOGIN_BASENAME === $file ) {
			$link = '<a href="' . sprintf(
				admin_url( 'options-general.php?page=%s' ), Custom_Login_Bootstrap::DOMAIN ) . '">' .
			        esc_html__( 'Settings', Custom_Login_Bootstrap::DOMAIN ) . '</a>';
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
	public function plugin_row_meta( $input, $file ) {

		if ( CUSTOM_LOGIN_BASENAME === $file ) {
			$links = array(
				'<a href="' . sprintf( admin_url( 'options-general.php?page=%s/extensions' ), Custom_Login_Bootstrap::DOMAIN ) . '">' . esc_html__( 'Extension Installer', Custom_Login_Bootstrap::DOMAIN ) . '</a>',
				'<a href="https://frosty.media/plugin/tag/custom-login-extension/" target="_blank">' . esc_html__( 'Shop Extensions', Custom_Login_Bootstrap::DOMAIN ) . ' <span class="dashicons dashicons-external"></span></a>',
			);

			$input = array_merge( $input, $links );
		}

		return $input;
	}
}
