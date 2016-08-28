<?php

/**
 * Class EDD_Remote_Install_Client
 *
 * Allows plugins to install new plugins or upgrades
 *
 * @author Mindshare Studios, Inc.
 * @package Libraries
 */
class Frosty_Media_Remote_Install_Client {

	const NONCE_KEY = 'Frosty_Media_RI';

	/**
	 * @var string
	 */
	private $api_url = '';

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var string
	 */
	static $download_id;

	/**
	 * Add class hooks
	 */
	public function add_hooks() {
		$this->add_actions();
	}

	/**
	 * @param $api_url
	 * @param array $options
	 */
	public function setup( $api_url, $options = array() ) {

		$this->options = array(
			'menu_page'       => '',
			'nonce'           => wp_create_nonce( self::NONCE_KEY ),
			'skipplugincheck' => false,
			'i18n'            => array(
				'active'     => __( 'Active', Custom_Login_Bootstrap::DOMAIN ),
				'activate'   => __( 'Activate', Custom_Login_Bootstrap::DOMAIN ),
				'deactivate' => __( 'Deactivate', Custom_Login_Bootstrap::DOMAIN ),
				'inactive'   => __( 'Inactive', Custom_Login_Bootstrap::DOMAIN ),
			),
		);

		$this->api_url = trailingslashit( $api_url );
		$options       = wp_parse_args( $options, $this->options );
		$this->options = $options;
	}

	/**
	 * Add class actions.
	 */
	private function add_actions() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_edd-ri-check-plugin-status', array( $this, 'check_plugin_status', ) );
		add_action( 'wp_ajax_edd-ri-do-manual-install', array( $this, 'do_manual_install' ) );
		add_action( 'wp_ajax_edd-ri-activate-plugin', array( $this, 'activate_plugin' ) );
		add_action( 'wp_ajax_edd-ri-deactivate-plugin', array( $this, 'deactivate_plugin' ) );
		add_action( 'wp_ajax_edd-ri-check-remote-install', array( $this, 'check_remote_install' ) );
		add_action( 'wp_ajax_edd-ri-do-remote-install', array( $this, 'do_remote_install' ) );

		add_action( 'eddri-install-complete', array( $this, 'install_complete' ), 0, 1 );

		add_action( 'plugins_api', array( $this, 'plugins_api' ), 100, 3 );
	}

	/**
	 * Try to convert plugin name to slug
	 *
	 * @param string $str Download name
	 *
	 * @return string The download slug
	 */
	private function slug( $str ) {
		return preg_replace( "/[\s_]/", "-", strtolower( $str ) );
	}

	/**
	 * @param string $var
	 *
	 * @return bool
	 */
	private function validate_post( $var ) {
		return isset( $_POST[ $var ] );
	}

	/**
	 * Checks if plugin is installed
	 *
	 * @param string $plugin_name
	 *
	 * @return bool
	 */
	private function is_plugin_installed( $plugin_name ) {

		$return = false;

		if ( empty( $plugin_name ) ) {
			return $return;
		}

		foreach ( get_plugins() as $plugin ) {

			if ( $plugin[ 'Name' ] === $plugin_name ) {
				$return = true;
				break;
			}
		}

		return $return;
	}

	/**
	 * Register scripts and styles
	 *
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook !== CL_Extensions::$menu_page ) {
			return;
		}

		wp_enqueue_style( 'edd-remote-install-style', plugin_dir_url( __FILE__ ) . 'css/edd-remote-install-admin.css' );

		wp_enqueue_script( 'edd-remote-install-script', plugin_dir_url( __FILE__ ) . 'js/edd-remote-install-admin.js', array( 'jquery' ) );
		wp_localize_script( 'edd-remote-install-script', 'edd_ri_options', $this->options );
	}

	/**
	 * Check plugin status
	 *
	 * Checks to see if a plugin is currently installed and disables the install button if so
	 *
	 * $_POST[ 'download' ] Download requested
	 *
	 * @return string $response
	 */
	public function check_plugin_status() {

		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		if ( $this->is_plugin_installed( $_POST[ 'download' ] ) ) {
			die( "active" );
		} elseif ( ! empty( $_POST[ 'basename' ] ) &&
		           file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $_POST[ 'basename' ] )
		) {
			die( "installed" );
		}

		die( "install" );
	}

	/**
	 * Do manual install
	 *
	 * If a plugin was unable to be installed automatically, generate an install URL and redirect to the plugins API
	 *
	 * $_POST ['download'] Download requested
	 * $_POST ['license'] License key
	 *
	 * @return string $url
	 */
	public function do_manual_install() {

		if ( ! $this->validate_post( 'download' ) ) {
			die( - 1 );
		}

		$download_name = urlencode( $_POST[ 'download' ] );
		$download_slug = $_POST[ 'basename' ];

		$license = '';

		if ( isset( $_POST[ 'license' ] ) ) {
			$license = $_POST[ 'license' ];
		}

		$url = wp_nonce_url( add_query_arg(
			array(
				'action'  => 'install-plugin',
				'plugin'  => $download_slug,
				'name'    => $download_name,
				'license' => $license,
				'eddri'   => CL_Extensions::$menu_page,
			),
			admin_url( 'update.php' )
		), 'install-plugin_' . $download_slug );

		die( esc_url( $url ) );
	}

	/**
	 * Check remote install
	 *
	 * Checks remote server for the specified Download
	 *
	 * $_POST['download'] Download requested
	 *
	 * @return string $response
	 */
	public function check_remote_install() {

		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			die( json_encode( 'You do not have sufficient permissions to install plugins on this site.' ) );
		}

		$api_params = array(
			'edd_action' => 'check_download',
			'item_name'  => urlencode( $_POST[ 'download' ] ),
		);

		// Call the custom API.
		$request = wp_remote_post(
			esc_url( $this->api_url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( ! is_wp_error( $request ) ) {

			$request = json_decode( wp_remote_retrieve_body( $request ) );
			$request = maybe_unserialize( $request );

			if ( $request->download == "free" ) {
				$response = "0";
			} elseif ( $request->download == "not-free" ) {
				$response = "1";
			} else {
				$response = "does not exist";
			}
		} else {
			$response = "An unknown error occurred.";
		}

		die( json_encode( $response ) );
	}

	/**
	 * Activate plugin
	 *
	 * Attempts to activate a plugin which is installed and inactive. Triggered by user clicking "Activate".
	 *
	 * $_POST['download'] Download requested
	 *
	 * @return string
	 */
	public function activate_plugin() {

		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		$path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $_POST[ 'basename' ];
		activate_plugin( $path );

		if ( is_plugin_active( $_POST[ 'basename' ] ) ) {
			die( 'activated' );
		} else {
			die( 'error' );
		}
	}

	/**
	 * Deactivate plugin
	 *
	 * Attempts to deactivate a plugin. Triggered by user clicking "Deactivate".
	 *
	 * $_POST ['download'] Download requested
	 *
	 * @return string
	 */
	public function deactivate_plugin() {

		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		$path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $_POST[ 'basename' ];
		deactivate_plugins( $path );

		if ( ! is_plugin_active( $_POST[ 'basename' ] ) ) {
			die( 'deactivated' );
		} else {
			die( 'error' );
		}
	}

	/**
	 * Manual install
	 *
	 * Outputs full install log in cases where auto-install failed
	 *
	 * $_POST ['download'] Download requested
	 *
	 * @return string
	 */
	public function manual_install() {
		echo "Hi";
	}

	/**
	 * Do remote install
	 *
	 * Passes the download and license key (if specified) to the server and receives and installs the plugin package
	 *
	 * $_POST ['license'] License key (if specified)
	 * $_POST ['download'] Download requested
	 *
	 * @return string
	 */
	public function do_remote_install() {

		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			die( json_encode( 'You do not have sufficient permissions to install plugins on this site.' ) );
		}

		$download = $_POST[ 'download' ];

		if ( isset( $_POST[ 'license' ] ) ) {

			$license = trim( $_POST[ 'license' ] );

			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $download ), // the name of our product in EDD
				'url'        => home_url(),
			);
			
			// Call the custom API.
			$response = wp_remote_post(
				esc_url( $this->api_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				die( "error" );
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->license !== "valid" ) {
				die( "invalid" );
			}
		} else {
			// If its a free download, don't send a license
			$license = null;
		}

		$api_params = array(
			'edd_action' => 'get_download',
			'item_name'  => urlencode( $download ),
			'license'    => urlencode( $license ),
		);
		
		// decode the license data
		$download_id   = $this->get_remote_download_id( $download );
		$download_link = $this->get_encoded_download_package_url( $download_id, $license );

		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		} //for plugins_api..

		$upgrader = new Plugin_Upgrader( $skin = new Plugin_Installer_Skin(
			compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

		$result = $upgrader->install( $download_link );

		if ( $result == 1 ) {
			$slug   = $this->slug( $download );
			$path   = WP_PLUGIN_DIR . $_POST[ 'basename' ];
			$result = activate_plugin( $path );

			$args[ 'slug' ]    = $slug;
			$args[ 'license' ] = $license;
			do_action( 'eddri-install-complete-' . $this->options[ 'page' ], $args );
		}

		die();
	}

	/**
	 * @param $download_title
	 *
	 * @return int
	 */
	private function get_remote_download_id( $download_title ) {

		$api_params = array(
			'edd_action' => 'get_download_id',
			'item_name'  => urlencode( $download_title ),
		);

		$response = wp_remote_post(
			esc_url_raw( $this->api_url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		$download_id = 0;

		if ( ! is_wp_error( $response ) ) {
			if ( '200' === wp_remote_retrieve_response_code( $response ) ) {
				$download_id = self::$download_id = json_decode( wp_remote_retrieve_body( $response ) );
			}
		}

		return $download_id;
	}

	/**
	 * @param $download_id
	 * @param $license
	 *
	 * @return mixed|void
	 */
	private function get_encoded_download_package_url( $download_id, $license ) {

		$package_url = add_query_arg( array(
			'edd_action' => 'package_download',
			'id'         => $download_id,
			'key'        => $license,
			'expires'    => rawurlencode( base64_encode( strtotime( '+10 minutes' ) ) ),
		), $this->api_url );

		return apply_filters( 'edd_sl_encoded_package_url', $package_url );
	}

	/**
	 * Callback action that's fired when an install is completed successfully
	 *
	 * @param array $args Install complete arguments
	 *
	 * @return void
	 */
	public function install_complete( $args ) {
	}

	/**
	 * Plugins API
	 *
	 * Overrides the plugins API parameters for download URLs originated by EDDRI
	 *
	 * $_POST ['eddri'] EDDRI page that originated the request
	 * $_POST ['license'] License key
	 * $_POST ['name'] Name of the plugin requested
	 *
	 * @param $api
	 * @param $action
	 * @param $args
	 *
	 * @return stdClass
	 */
	public function plugins_api( $api, $action, $args ) {

		if ( 'plugin_information' == $action ) {

			if ( isset( $_POST[ 'eddri' ] ) && $_POST[ 'eddri' ] == CL_Extensions::$menu_page ) {

				///////////// NEW /////////////////
				$download_id   = ! empty( self::$download_id ) ?
					self::$download_id : $this->get_remote_download_id( $_POST[ 'name' ] );
				$download_link = $this->get_encoded_download_package_url( $download_id, $_POST[ 'license' ] );
				///////////// NEW /////////////////

				$api                = new stdClass();
				$api->name          = $args->slug;
				$api->version       = "";
				$api->download_link = $download_link;
			}
		}

		return $api;
	}
}
