<?php
/**
 * Custom Login
 *
 * @package     CustomLogin
 * @author      Austin Passy
 * @copyright   2012 - 2016 Frosty Media
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Custom Login
 * Plugin URI: https://frosty.media/plugins/custom-login
 * Description: A simple way to customize your WordPress <code>/wp-login.php</code> screen! A <a href="https://frosty.media/?ref=wp-admin/plugins.php">Frosty Media</a> plugin.
 * Version: 4.0.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: custom-login
 * GitHub Plugin URI: https://github.com/thefrosty/custom-login
 * GitHub Branch: master
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Custom_Login_Bootstrap' ) ) {

	/**
	 * Class Custom_Login_Bootstrap
	 */
	class Custom_Login_Bootstrap {

		/**
		 * Current version number
		 * @var string
		 */
		const VERSION = '4.0';

		/**
		 * Plugin text domain
		 * @var string
		 */
		const DOMAIN = 'custom-login';

		/** Singleton *************************************************************/
		private static $instance;

		/**
		 * Get the instance of the plugin.
		 *
		 * @return Custom_Login_Bootstrap The one true instance
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Custom_Login_Bootstrap constructor.
		 */
		private function __construct() {

			$this->setup_constants();
			$this->add_actions();
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'CUSTOM_LOGIN_VERSION' ) ) {
				define( 'CUSTOM_LOGIN_VERSION', self::VERSION );
			}

			// Plugin Root File
			if ( ! defined( 'CUSTOM_LOGIN_FILE' ) ) {
				define( 'CUSTOM_LOGIN_FILE', __FILE__ );
			}

			// Plugin Folder Path
			if ( ! defined( 'CUSTOM_LOGIN_DIR' ) ) {
				define( 'CUSTOM_LOGIN_DIR', plugin_dir_path( CUSTOM_LOGIN_FILE ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'CUSTOM_LOGIN_URL' ) ) {
				define( 'CUSTOM_LOGIN_URL', plugin_dir_url( CUSTOM_LOGIN_FILE ) );
			}

			// Plugin Root Basename
			if ( ! defined( 'CUSTOM_LOGIN_BASENAME' ) ) {
				define( 'CUSTOM_LOGIN_BASENAME', plugin_basename( CUSTOM_LOGIN_FILE ) );
			}
		}

		/**
		 * Setup the base action hooks.
		 */
		private function add_actions() {

			add_action( 'plugins_loaded', array( $this, 'autoload_register' ), 10 );
			add_action( 'plugins_loaded', array( $this, 'dependency_check' ), 989 );
			add_action( 'init', array( $this, 'hookup' ), 5 );
			add_action( 'init', array( $this, 'do_actions' ), 10 );
		}

		/**
		 * Register our autoloader.
		 *
		 * @return void
		 */
		public function autoload_register() {
			spl_autoload_register( array( $this, 'autoload_classes' ) );
		}

		/**
		 * Setup our connected hooks.
		 *      -It's just a hookup
		 */
		public function hookup() {
			CL_Hookup::add_hooks();
		}

		/**
		 *
		 */
		public function dependency_check() {
			new CL_Dependency_Check();
		}

		/**
		 * Create our custom action hooks.
		 * These actions are called on the `init` hook; priority '10'.
		 */
		public function do_actions() {

			if ( is_admin() ) {
				do_action( 'custom_login_admin_init' );
			}

			do_action( 'custom_login_init', CL_Common::is_wp_login_php() );
		}

		/**
		 * Helper method to provide directory path to Custom Login.
		 *
		 * @param string $path Path to append
		 *
		 * @return string Directory with optional path appended
		 */
		public static function dir_path( $path = '' ) {
			return CUSTOM_LOGIN_DIR . $path;
		}

		/**
		 * Autoload our Custom Login classes when needed.
		 *
		 * @param string $class_name Name of the class being requested
		 */
		protected function autoload_classes( $class_name ) {

			if ( false !== strpos( $class_name, 'CL_' ) && false !== strpos( $class_name, 'Custom_Login_' ) ) {
				return;
			}

			$class_name = $this->sanitize_class_file_name( $class_name );

			if ( false !== strpos( $class_name, 'cl-admin' ) ) {
				$file = self::dir_path( "includes/admin/class-{$class_name}.php" );
			} elseif ( false !== strpos( $class_name, 'cl-customize' ) ) {
				$file = self::dir_path( "includes/customize/class-{$class_name}.php" );
			} else {
				$file = self::dir_path( "includes/class-{$class_name}.php" );
			}

			if ( file_exists( $file ) ) {
				include_once( $file );
			}
		}

		/**
		 * Replace class name underscores to file dashes.
		 *
		 * @param string $class_name The incoming class name.
		 *
		 * @return string A sanitized file name
		 */
		private function sanitize_class_file_name( $class_name ) {
			return str_replace( '_', '-', strtolower( $class_name ) );
		}
	}

	add_action( 'plugins_loaded', array( 'Custom_Login_Bootstrap', 'get_instance' ), 2 );
}
