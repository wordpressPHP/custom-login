<?php

use CL_Interface_WordPress_Hooks as WordPress_Hooks;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Extensions
 */
class CL_Extensions implements WordPress_Hooks {

	/**
	 * @var string
	 */
	var $api_url;

	/**
	 * @var string
	 */
	private $setting_id;

	/**
	 * @var string
	 */
	private $page_id;

	/**
	 * @var string
	 */
	static $menu_page;

	/**
	 * @var array
	 */
	var $extensions = array();

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		$this->setup_variables();
		$this->instantiate_remote_install();
		$this->add_actions();
		$this->get_extensions();
	}

	/**
	 * Setup class variables.
	 */
	private function setup_variables() {

		$this->api_url    = defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ?
			'http://frostymedia.dev/edd-sl-api/' : 'https://frosty.media/edd-sl-api/';
		$this->setting_id = CL_Settings_API::SETTING_ID;
		$this->page_id    = 'add-ons';
	}

	/**
	 * Setup Remote Installer class.
	 */
	private function instantiate_remote_install() {

		$remote_install = new Frosty_Media_Remote_Install_Client();
		$remote_install->setup(
			$this->api_url,
			array(
				'skipplugincheck' => false,
				'menu_page'       => self::$menu_page,
				'url'             => home_url(),
			)
		);
		$remote_install->add_hooks();
	}

	/**
	 * Add class actions.
	 */
	private function add_actions() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_action( $this->setting_id . '_settings_sidebars', array( $this, 'sidebar_add_on_plugins' ), 20 );
	}

	/**
	 * Sidebar box about the plugin author.
	 *
	 * @param array $args Sidebar args
	 */
	public function sidebar_add_on_plugins( array $args ) {
		CL_Common::render_view( 'admin/sidebar-add-on-plugins', null, $args );
	}

	/**
	 *
	 */
	public function admin_menu() {

		$page_uri = sprintf( '%s-%s', Custom_Login_Bootstrap::DOMAIN, $this->page_id );

		self::$menu_page = add_options_page(
			__( 'Custom Login Add-on Plugins', Custom_Login_Bootstrap::DOMAIN ),
			__( 'Add-on Plugins', Custom_Login_Bootstrap::DOMAIN ),
			'install_plugins',
			$page_uri,
			array( $this, 'settings_page' )
		);

//		remove_submenu_page( 'options-general.php', $page_uri );
		add_action( 'load-' . self::$menu_page, array( $this, 'load' ) );
		add_action( 'load-' . self::$menu_page, array( $this, 'remote_install_client' ), 10 );
	}

	public function settings_page() {
		CL_Common::render_view( 'admin/settings-add-ons', $this );
	}

	/**
	 * Load hooks only on our specific page.
	 */
	public function load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
	}

	/**
	 *
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( Custom_Login_Bootstrap::DOMAIN );
		wp_enqueue_script( array( 'updates', 'plugin-install' ) );
	}
	
	/**
	 * Load the remote installer on our setting page only.
	 *
	 * @update 3.1
	 */
	public function remote_install_client() {
	}

	/**
	 *
	 */
	private function get_extensions() {

		// Save this for a latter date since our checkin for our HTML data doesn't get called on init...
		$ext_url       = false; //add_query_arg( array( 'edd_action' => 'cl_announcements' ), trailingslashit( CUSTOM_LOGIN_API_URL ) . 'cl-checkin-api/' );
		$transient_key = CL_Common::get_transient_key( 'extensions' );
		$extensions    = CL_Common::wp_remote_get( $ext_url, $transient_key, WEEK_IN_SECONDS, 'CustomLogin' );

		if ( false !== $extensions && is_object( $extensions ) ) {
			$this->extensions[] = $extensions->html;
		} else {
			/* Stealth Login */
			$this->extensions[] = array(
				'title'       => 'Custom Login Stealth Login',
				'description' => 'Protect your wp-login.php page from brute force attacks.',
				'url'         => 'https://frosty.media/plugins/custom-login-stealth-login/',
				'image'       => 'https://i.imgur.com/mhuymPG.jpg',
				'basename'    => 'custom-login-stealth-login/custom-login-stealth-login.php',
				'links'       => array(
					array(
						'description' => 'Personal',
						'download_id' => '108',
						'price_id'    => '1',
						'price'       => '$35',
					),
					array(
						'description' => 'Plus',
						'download_id' => '108',
						'price_id'    => '2',
						'price'       => '$95',
					),
					array(
						'description' => 'Professional',
						'download_id' => '108',
						'price_id'    => '3',
						'price'       => '$195',
					),
				),
			);

			/* Page Template */
			$this->extensions[] = array(
				'title'       => 'Custom Login Page Template',
				'description' => 'Add a login form to any WordPress page.',
				'url'         => 'https://frosty.media/plugins/custom-login-page-template/',
				'image'       => 'https://i.imgur.com/A0rzS9q.jpg',
				'basename'    => 'custom-login-page-template/custom-login-page-template.php',
				'links'       => array(
					array(
						'description' => 'Personal',
						'download_id' => '120',
						'price_id'    => '1',
						'price'       => '$35',
					),
					array(
						'description' => 'Plus',
						'download_id' => '120',
						'price_id'    => '2',
						'price'       => '$95',
					),
					array(
						'description' => 'Professional',
						'download_id' => '120',
						'price_id'    => '3',
						'price'       => '$195',
					),
				),
			);

			/* Login Redirects */
			$this->extensions[] = array(
				'title'       => 'Custom Login Redirects',
				'description' => 'Manage redirects after logging in.',
				'url'         => 'https://extendd.com/plugin/wordpress-login-redirects/',
				'image'       => 'https://i.imgur.com/aNGoyAa.jpg',
				'basename'    => 'custom-login-redirects/custom-login-redirects.php',
				'links'       => array(
					array(
						'description' => 'Personal',
						'download_id' => '124',
						'price_id'    => '1',
						'price'       => '$35',
					),
					array(
						'description' => 'Plus',
						'download_id' => '124',
						'price_id'    => '2',
						'price'       => '$95',
					),
					array(
						'description' => 'Professional',
						'download_id' => '124',
						'price_id'    => '3',
						'price'       => '$195',
					),
				),
			);

			/* No Password */
			$this->extensions[] = array(
				'title'       => 'Custom Login No Password',
				'description' => 'Allow users to login without a password.',
				'url'         => 'https://frosty.media/plugins/custom-login-no-passowrd-login/',
				'image'       => 'https://i.imgur.com/7SXIpi5.jpg',
				'basename'    => 'custom-login-no-password/custom-login-no-password.php',
				'links'       => array(
					array(
						'description' => 'Personal',
						'download_id' => '128',
						'price_id'    => '1',
						'price'       => '$35',
					),
					array(
						'description' => 'Plus',
						'download_id' => '128',
						'price_id'    => '2',
						'price'       => '$95',
					),
					array(
						'description' => 'Professional',
						'download_id' => '128',
						'price_id'    => '3',
						'price'       => '$195',
					),
				),
			);

		} // if
	}
}
