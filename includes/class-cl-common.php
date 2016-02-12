<?php

/**
 * Class CL_Common
 */
class CL_Common {

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return mixed An empty string if no default is set, otherwise the value of the option.
	 */
	public static function get_option( $option, $section = '', $default = '' ) {

		$section = self::clean_section_id( $section );
		$setting = get_option( CL_Settings_API::SETTING_ID, array() );

		if ( ! empty( $setting[ $section ] ) && ! empty( $setting[ $section ][ $option ] ) ) {
			return $setting[ $section ][ $option ];
		}

		return $default;
	}

	/**
	 * Get all values of a settings section
	 *
	 * @param string $section the section name this field belongs to
	 *
	 * @return array
	 */
	public static function get_options( $section ) {

		$section = self::clean_section_id( $section );
		$setting = get_option( CL_Settings_API::SETTING_ID, array() );

		if ( ! empty( $setting[ $section ] ) ) {
			return $setting[ $section ];
		}

		return array();
	}

	/**
	 * Remove the 'custom_login_settings' from the section id.
	 *
	 * @param string $section_id
	 *
	 * @return string
	 */
	private static function clean_section_id( $section_id ) {
		return str_replace( array( CL_Settings_API::SETTING_ID, '[', ']' ), '', $section_id );
	}

	/**
	 * Is Custom Login using the new customizer design settings?
	 *
	 * @return bool
	 */
	public static function using_customizer() {
		return 'on' === self::get_option( 'use_customizer', 'general' );
	}

	/**
	 * Render a template view.
	 *
	 * @param string $view_path The path + filename. Omit the `php` extension.
	 * @param null|object $object Optional object to pass to the view.
	 * @param null|string|array $args Optional array to pass to the view.
	 */
	public static function render_view( $view_path, $object = null, $args = null ) {
		include CUSTOM_LOGIN_DIR . "views/{$view_path}.php";
	}

	/**
	 * Fetch RSS items from the feed.
	 *
	 * @param int $num Number of items to fetch.
	 * @param string $feed The feed to fetch.
	 *
	 * @return array|bool False on error, array (SimplePie_Item) of RSS items on success.
	 */
	public static function fetch_rss_items( $num, $feed ) {

		if ( ! function_exists( 'fetch_feed' ) ) {
			include_once( ABSPATH . WPINC . '/feed.php' );
		}

		$rss = fetch_feed( $feed );

		// Bail if feed doesn't work
		if ( ! ( $rss instanceof SimplePie ) || is_wp_error( $rss ) ) {
			return false;
		}

		$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );

		// If the feed was erroneous
		if ( ! $rss_items ) {
			$md5 = md5( $feed );
			delete_transient( 'feed_' . $md5 );
			delete_transient( 'feed_mod_' . $md5 );
			$rss       = fetch_feed( $feed );
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
		}

		return $rss_items;
	}

	/**
	 * Helper function to return the data URI.
	 *
	 * @param string $_image
	 * @param string $mime
	 *
	 * @return string
	 */
	public static function get_data_uri( $_image, $mime = '' ) {

		$image = trailingslashit( CUSTOM_LOGIN_URL );
		$image .= $_image;

		$data = file_exists( $image ) ? base64_encode( file_get_contents( $image ) ) : '';

		return ! empty( $data ) ? 'data:image/' . $mime . ';base64,' . $data : '';
	}

	/**
	 * Get's the cached transient key.
	 *
	 * @return string
	 */
	public static function get_transient_key( $input ) {

		$len = is_multisite() ? 40 : 45;
		$key = 'custom_login_';
		$key = $key . substr( md5( $input ), 0, $len - strlen( $key ) );

		return $key;
	}

	/**
	 * Helper function to make remote calls
	 *
	 * @since        3.0.0
	 * @updated    3.0.8
	 */
	public static function wp_remote_get( $url = false, $transient_key, $expiration = null, $user_agent = 'WordPress' ) {

		if ( ! $url ) {
			return false;
		}

		if ( 'WordPress' == $user_agent ) {
			global $wp_version;
			$_version = $wp_version;
		} else {
			$_version = CUSTOM_LOGIN_VERSION;
		}

		$expiration = null !== $expiration ? $expiration : WEEK_IN_SECONDS;

		#	delete_transient( $transient_key );
		if ( false === ( $json = get_transient( $transient_key ) ) ) {

			$response = wp_remote_get(
				esc_url( $url ),
				array(
					'timeout'    => apply_filters( 'cl_wp_remote_get_timeout', (int) 15 ),
					'sslverify'  => false,
					'user-agent' => $user_agent . '/' . $_version . '; ' . get_bloginfo( 'url' ),
				)
			);

			if ( ! is_wp_error( $response ) ) {

				if ( isset( $response[ 'body' ] ) && strlen( $response[ 'body' ] ) > 0 ) {

					$json = json_decode( wp_remote_retrieve_body( $response ) );

					// Discount, double check?
					if ( is_wp_error( $json ) ) {
						return false;
					}

					// Cache the results for '$expiration'
					set_transient( $transient_key, $json, $expiration );

					// Return the data
					return $json;
				}
			} else {
				return false; // Error, lets return!
			}
		}

		return $json;
	}

	/**
	 * Helper function check if we're on our settings page.
	 *
	 * @since        3.0.9
	 */
	public static function is_settings_page() {

		$return = true;
		$screen = get_current_screen();

		if ( null !== $screen ) {

			if ( $screen->id !== CL_Settings_API::$menu_page ) {
				$return = false;
			}
		} else {

			if ( 'options-general.php' != $GLOBALS[ 'pagenow' ] ) {
				$return = false;
			}

			if ( ! isset( $_GET[ 'page' ] ) || Custom_Login_Bootstrap::DOMAIN !== $_GET[ 'page' ] ) {
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Is the current page the wp login page 'wp-login.php'?
	 *
	 * @param WP_Customize_Panel|null $panel Optional parameter holding the WP_Customize_Panel object during
	 *      the `customize_register` hook callback for add_panel().
	 *
	 * @return bool
	 */
	public static function is_wp_login_php( $panel = null ) {
		global $pagenow;

		/**
		 * When inside the customizer, we need to check
		 * two global variable definitions.
		 */
		if ( ! is_null( $panel ) || $panel instanceof WP_Customize_Panel ) {
			return 'customize.php' === $pagenow || 'wp-login.php' === $pagenow;
		}

		return 'wp-login.php' === $pagenow;
	}

	/**
	 * Return all editable role capabilities.
	 *
	 * @return array
	 */
	public static function get_editable_roles() {

		$roles = array();

		// get_editable_roles()
		$editable_roles = apply_filters( 'editable_roles', wp_roles()->roles );

		if ( empty( $editable_roles ) ) {
			return $roles;
		}

		foreach ( $editable_roles as $role_name => $role ) {

			// https://wordpress.org/support/topic/invalid-argument-supplied-for-foreach-error-line-in-wp-dashboard?replies=2#post-6427631
			if ( ! is_array( $role[ 'capabilities' ] ) ) {
				continue;
			}

			foreach ( $role[ 'capabilities' ] as $capability => $caps_array ) {

				// Remove the (deprecated) capabilities from the array
				if ( preg_match( '/^level_/', $capability ) ) {
					continue;
				}

				$roles[ $capability ] = $capability;
			}
		}

		return $roles;
	}

	/**
	 * Gets the colors in the current WordPress admin color scheme.
	 *
	 * @uses get_user_meta
	 * @uses get_current_user_id
	 * @link https://gist.github.com/JeffMatson/86b44ec68bbc4ce80e6e
	 *
	 * @return array
	 */
	public static function get_admin_colors() {
		global $_wp_admin_css_colors;

		$current_color_scheme = get_user_meta( get_current_user_id(), 'admin_color', true );

		$colors = array_merge(
			$_wp_admin_css_colors[ $current_color_scheme ]->colors,
			$_wp_admin_css_colors[ $current_color_scheme ]->icon_colors
		);

		return $colors;
	}
}
