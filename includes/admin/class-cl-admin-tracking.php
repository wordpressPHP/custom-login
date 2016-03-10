<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Admin_Tracking
 *
 * @since 3.0.0
 * @updated 4.0.0
 */
class CL_Admin_Tracking {

	const SETTING_ID = 'custom_login_hide_tracking_notice';

	/**
	 * The API URL.
	 *
	 * @var string
	 */
	private $api;

	/**
	 * The data.
	 *
	 * @var string
	 */
	private $data;

	/**
	 * @var string
	 */
	private $settings_page;

	/**
	 * CL_Admin_Tracking constructor.
	 */
	public function __construct() {

		$this->api = 'https://frosty.media/cl-checkin-api/?edd_action=cl_checkin';
		
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
			$this->api = str_replace( 'https://frosty.media/', 'http://frostymedia.dev/', $this->api );
		}

		$this->settings_page = sprintf( admin_url( 'options-general.php?page=%s' ), Custom_Login_Bootstrap::DOMAIN );
	}

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		$this->add_actions();
	}

	/**
	 * Schedule a weekly checkin
	 *
	 */
	private function add_actions() {

		/**
		 * Send a 'once a week' (while tracking is allowed) to check in,
		 * which can be used to determine active sites.
		 */
		add_action( CL_Cron::WEEKLY_ID, function() {
			$this->send_checkin();
		} );

		add_action( CL_Settings_API::SETTING_ID . '_after_sanitize_options', array(
			$this,
			'check_for_settings_optin',
		) );
		add_action( 'admin_action_cl_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'admin_action_cl_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'admin_notices', array( $this, 'maybe_add_notice' ) );
	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @return bool
	 */
	private function tracking_allowed() {
		return 'on' === CL_Common::get_option( 'tracking', 'general', 'off' );
	}

	/**
	 * Get the last time a 'checkin' was sent.
	 *
	 * @access private
	 * @return false|string False if it's never been set else a timestamp string
	 */
	private function get_last_send() {
		return get_option( 'custom_login_tracking_last_send' );
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @param array $extra_data
	 */
	private function setup_data( array $extra_data = array() ) {

		$data = array();

		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$data[ 'url' ]     = home_url();
		$data[ 'version' ] = get_bloginfo( 'version' );
		$data[ 'theme' ]   = $theme;
		$data[ 'email' ]   = get_bloginfo( 'admin_email' );

		// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data[ 'active_plugins' ]   = $active_plugins;
		$data[ 'inactive_plugins' ] = $plugins;
		$data[ 'post_count' ]       = wp_count_posts( 'post' )->publish;
		$data[ 'cl_version' ]       = CUSTOM_LOGIN_VERSION;
		
		if ( is_array( $extra_data ) && array() !== $extra_data ) {
			foreach ( $extra_data as $key => $value ) {
				$data[ sanitize_key( $key ) ] = is_array( $value ) ?
					array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
			}
		}

		$this->data = $data;
	}

	/**
	 * Create the admin notice message.
	 *
	 * @return string
	 */
	private function get_admin_notice_message() {

		$return = '';
		$return .= esc_attr__( 'Allow Custom Login to anonymously track how this plugin is used?', Custom_Login_Bootstrap::DOMAIN );
		$return .= '&nbsp;';
		$return .= esc_html__( 'If you opt-in you\'ll be eligible for a 20&#37; off coupon for any
single plugin on', Custom_Login_Bootstrap::DOMAIN );
		$return .= ' <a href="https://frosty.media/plugins">frosty.media</a>. ';
		$return .= esc_attr__( 'No sensitive data is tracked.', Custom_Login_Bootstrap::DOMAIN );
		$return .= $this->get_admin_opt_in_out_message();

		return $return;
	}

	/**
	 * Create the admin notice opt-(in/out) message links.
	 *
	 * @return string
	 */
	private function get_admin_opt_in_out_message() {

		return sprintf( '<span class="alignright"><a href="%s">%s</a> &mdash; <a href="%s">%s</a></span>',
			esc_url_raw( wp_nonce_url(
				add_query_arg( 'action', 'cl_opt_into_tracking', admin_url( 'admin.php' ) ),
				CUSTOM_LOGIN_BASENAME,
				'cl_nonce'
			) ),
			esc_attr__( 'Allow', Custom_Login_Bootstrap::DOMAIN ),
			esc_url_raw( wp_nonce_url(
				add_query_arg( 'action', 'cl_opt_out_of_tracking', admin_url( 'admin.php' ) ),
				CUSTOM_LOGIN_BASENAME,
				'cl_nonce'
			) ),
			esc_attr__( 'Do not allow', Custom_Login_Bootstrap::DOMAIN )
		);
	}

	/**
	 * Safe redirect back to the Custom Login settings page.
	 */
	private function wp_safe_redirect() {

		wp_safe_redirect( remove_query_arg( 'action', $this->settings_page ) );
		exit;
	}

	/**
	 * Send data to our secure server.
	 *
	 * @param bool $override
	 * @param array $extra_data
	 */
	private function send_checkin( $override = false, array $extra_data = array() ) {
		
		if ( ! $this->tracking_allowed() && ! $override ) {
			return;
		}

		// Send a maximum of once every three weeks
		$last_send = $this->get_last_send();

		if ( $last_send && $last_send > strtotime( '-3 weeks' ) ) {
			return;
		}

		$this->setup_data( $extra_data );

		$response = wp_remote_post( esc_url_raw( $this->api ), array(
			'method'      => 'POST',
			'timeout'     => (int) apply_filters( 'cl_wp_remote_post_timeout', 15 ),
			'redirection' => 5,
			'body'        => $this->data,
			'user-agent'  => 'CustomLogin/' . CUSTOM_LOGIN_VERSION . '; ' . get_bloginfo( 'url' ),
		) );
		
		if ( ! is_wp_error( $response ) ) {
			update_option( 'custom_login_tracking_last_send', time() );
		}
	}

	/**
	 * Check for a new opt-in on settings save
	 *
	 * This runs during the sanitation of General settings, thus the return
	 *
	 * @param array $options The incoming options
	 */
	public function check_for_settings_optin( $options ) {
		
		// Send an initial check in on settings save
		if ( isset( $options[ 'general' ][ 'tracking' ] ) && 'on' === $options[ 'general' ][ 'tracking' ] ) {
			$this->send_checkin( true, array( 'on_activation' => 'settings' ) );
		}
	}

	/**
	 * Check for a new opt-in via the admin notice
	 */
	public function check_for_optin() {

		if ( isset( $_GET[ 'cl_nonce' ] ) && wp_verify_nonce( $_GET[ 'cl_nonce' ], CUSTOM_LOGIN_BASENAME ) ) {
			$options = get_option( CL_Settings_API::SETTING_ID, array() );

			$options[ 'general' ][ 'tracking' ] = 'on';
			update_option( CL_Settings_API::SETTING_ID, $options );
			update_option( self::SETTING_ID, '1' );

			$this->send_checkin( true, array( 'on_activation' => 'admin notice' ) );
		}

		$this->wp_safe_redirect();
	}

	/**
	 * Check for a new opt-out via the admin notice
	 *
	 */
	public function check_for_optout() {

		if ( isset( $_GET[ 'cl_nonce' ] ) && wp_verify_nonce( $_GET[ 'cl_nonce' ], CUSTOM_LOGIN_BASENAME ) ) {
			$options = get_option( CL_Settings_API::SETTING_ID, array() );

			$options[ 'general' ][ 'tracking' ] = 'off';
			update_option( CL_Settings_API::SETTING_ID, $options );
			update_option( self::SETTING_ID, '1' );
		}

		$this->wp_safe_redirect();
	}

	/**
	 * Add an admin notice if tracking isn't enabled and it has not been dismissed.
	 *
	 * @throws Exception
	 */
	public function maybe_add_notice() {

		$show_notice = (bool) apply_filters( 'custom_login_show_tracking_notice', true );

		try {
			$options     = CL_Common::get_options( 'general' );
			$hide_notice = get_option( self::SETTING_ID );

			if ( '1' === $hide_notice || true === $hide_notice ) {
				$show_notice = false;
			}

			if ( isset( $options[ 'admin_notices' ] ) && 'off' === $options[ 'admin_notices' ] ) {
				if ( ! CL_Common::is_settings_page() ) {
					$show_notice = false;
				}
			}

			if ( isset( $options[ 'tracking' ] ) ) {
				$show_notice = false;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				$show_notice = false;
			}

			if ( $show_notice ) {
				$message = sprintf( '%s %s', $this->get_admin_notice_message(), CL_Admin_Notices::NOTICE_CODE );
				throw new Exception( $message );
			}
		} catch( Exception $e ) {
			new CL_Admin_Notices( $e );
		}
	}
}
