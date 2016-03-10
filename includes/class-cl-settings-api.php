<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Custom_Login_Bootstrap as Custom_Login;

/**
 * Class CL_Settings_API
 */
class CL_Settings_API {

	/**
	 * Version
	 * @var string
	 */
	const VERSION = '3.0.0';
	const SETTING_ID = 'custom_login_settings';

	/**
	 * @var string
	 */
	public static $menu_page;
	
	/**
	 * @var array
	 */
	var $settings_sections = array();
	var $settings_fields = array();
	var $settings_sidebars = array();
	var $scripts_array = array();
	var $localize_array = array();

	/** Singleton *************************************************************/
	private static $instance;

	/**
	 * Get the instance of the plugin.
	 *
	 * @return CL_Settings_API The one true instance
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * CL_Settings_API constructor.
	 */
	private function __construct() {
	}

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
	}

	/**
	 * Register the plugin settings page and subsequent hooks.
	 */
	public function admin_menu() {

		self::$menu_page = add_options_page(
			__( 'Custom Login Settings', 'custom-login' ),
			__( 'Custom Login', 'custom-login' ),
			CL_Common::get_option( 'capability', 'general', 'manage_options' ),
			Custom_Login::DOMAIN,
			array( $this, 'settings_page' )
		);

		add_action( 'load-' . self::$menu_page, array( $this, 'load_cl_admin' ), 89 );
		add_action( Custom_Login::DOMAIN . '_admin_enqueue_scripts', array( $this, 'admin_print_footer_scripts', ), 99 );
	}

	/**
	 * Display the plugin settings page
	 */
	public function settings_page() {
		CL_Common::render_view( 'admin/settings-api' );
	}

	/**
	 * Action hooks called on the menu page hooks.
	 *      This method is only called on the `load-$action` action
	 */
	public function load_cl_admin() {

		$prefix = self::SETTING_ID;
		add_action( 'admin_notices', array( $this, 'maybe_add_notice' ) );
		add_action( $prefix . '_sticky_admin_notice', array( $this, 'sticky_social_links', ), 10 );
		add_action( $prefix . '_settings_sidebars', array( $this, 'sidebar_author' ), 19 );
		add_action( $prefix . '_settings_sidebars', array( $this, 'sidebar_feed' ), 20 );
	}

	/**
	 * Register admin scripts and styles
	 */
	public function register_admin_scripts() {

		wp_register_script( Custom_Login::DOMAIN, plugins_url( 'js/admin.js', CUSTOM_LOGIN_FILE ),
			array( 'jquery' ), CUSTOM_LOGIN_VERSION, true );

		wp_register_script( 'wp-color-picker-alpha', plugins_url( 'js/wp-color-picker-alpha.js', CUSTOM_LOGIN_FILE ),
			array( 'wp-color-picker' ), '1.2.1', true );

		wp_register_script( 'codemirror', plugins_url( 'js/codemirror.js', CUSTOM_LOGIN_FILE ),
			array(), CUSTOM_LOGIN_VERSION, true );

		wp_register_style( Custom_Login::DOMAIN, plugins_url( 'css/admin.css', CUSTOM_LOGIN_FILE ), false,
			CUSTOM_LOGIN_VERSION, 'screen' );

		wp_register_style( 'bulma-framework', plugins_url( 'css/bulma.css', CUSTOM_LOGIN_FILE ), false,
			'0.0.12', 'screen' );

		wp_register_style( 'codemirror', plugins_url( 'css/codemirror.css', CUSTOM_LOGIN_FILE ), false,
			'5.12.0', 'screen' );
	}

	/**
	 * Enqueue admin scripts and styles when on the Custom Login settings page.
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		/**
		 * Only continue if we're on out settings page.
		 */
		if ( 'settings_page_' . Custom_Login::DOMAIN !== $hook ) {
			return;
		}

		/* Core */
		wp_enqueue_media();
		wp_enqueue_script( array( 'plugin-install' ) );
		wp_enqueue_style( array( 'wp-color-picker', 'thickbox', 'plugin-install' ) );

		/* jQuery Sticky */
		wp_enqueue_script( 'sticky', plugins_url( 'js/jquery.sticky.js', CUSTOM_LOGIN_FILE ), array( 'jquery' ), '1.0.0', true );

		/* Dashicons */
		wp_enqueue_style( 'dashicons' );

		/* Custom Login */
		wp_enqueue_script( array( Custom_Login::DOMAIN, 'codemirror' ) );
		wp_enqueue_style( array( Custom_Login::DOMAIN, 'codemirror', 'bulma-framework' ) );

		do_action( Custom_Login::DOMAIN . '_admin_enqueue_scripts' );
	}

	/**
	 * This method is called in the custom action 'custom-login_admin_enqueue_scripts' and insures that
	 * these action hooks to 'admin_print_footer_scripts' are only called on our setting spage.
	 */
	public function admin_print_footer_scripts() {

		add_action( 'admin_print_footer_scripts', array( $this, 'enqueue_field_type_scripts' ), 89 );
		add_action( 'admin_print_footer_scripts', array( $this, 'wp_localize_script' ), 99 );
	}

	/**
	 * Enqueue field type dependant scripts if they are registered.
	 *
	 * @uses wp_script_is
	 * @uses wp_print_scripts Since this method is called in 'admin_print_footer_scripts' we have to print the
	 *                          script and not enqueue it.
	 */
	public function enqueue_field_type_scripts() {

		if ( ! empty( $this->scripts_array ) ) {
			foreach ( array_unique( $this->scripts_array ) as $script ) {

				if ( wp_style_is( $script, 'registered' ) ) {
					wp_print_styles( $script );
				}

				if ( wp_script_is( $script, 'registered' ) ) {
					wp_print_scripts( $script );
				}
			}
		}
	}

	/**
	 * Localize our script array.
	 *
	 * @uses wp_localize_script
	 */
	public function wp_localize_script() {

		$this->localize_array[ 'prefix' ]  = self::SETTING_ID;
		$this->localize_array[ 'blog_id' ] = get_current_blog_id();
		$this->localize_array[ 'nonce' ]   = wp_create_nonce( __CLASS__ . CUSTOM_LOGIN_BASENAME );

		wp_localize_script( Custom_Login::DOMAIN, 'cl_settings_api', $this->localize_array );
	}

	/**
	 * Set settings sections
	 *
	 * @param array $sections setting sections array
	 */
	public function set_sections( array $sections ) {

		$sections = apply_filters( self::SETTING_ID . '_add_settings_sections', $sections );

		$this->settings_sections = $sections;
	}

	/**
	 * Add a single section
	 *
	 * @param array $section
	 */
	public function add_section( array $section ) {
		$this->settings_sections[] = $section;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $field_args The settings field args array
	 */
	public function set_fields( array $field_args ) {

		$field_args = apply_filters( self::SETTING_ID . '_add_settings_fields', $field_args );

		$this->settings_fields = $field_args;
	}

	/**
	 * Add a single field
	 *
	 * @param string $section
	 * @param array $field_args
	 */
	public function add_field( $section, array $field_args ) {

		$defaults = array(
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text',
		);

		$args = wp_parse_args( $field_args, $defaults );

		$this->settings_fields[ $section ][] = $args;
	}

	/**
	 * Add a single sidebar section
	 *
	 * @param array $sidebar
	 */
	public function add_sidebar( $sidebar = array() ) {

		$sidebar = apply_filters( self::SETTING_ID . '_add_settings_sidebar', $sidebar );

		if ( ! empty( $sidebar ) ) {
			$this->settings_sidebars[] = $sidebar;
		}
	}

	/**
	 * Add to the localize array variable.
	 *
	 * @param string $key The array key value
	 * @param mixed $value The array value
	 */
	public function add_localize_array( $key, $value ) {

		if ( isset( $this->localize_array[ $key ] ) ) {
			$this->localize_array[ $key ][] = $value;
		} else {
			$this->localize_array[ $key ] = $value;
		}
	}

	/**
	 * Add to the script array variable.
	 *
	 * @param mixed $value The array value
	 */
	public function add_scripts_array( $value ) {

		if ( ! in_array( $value, $this->scripts_array ) ) {
			$this->scripts_array[] = $value;
		}
	}

	/**
	 * Show navigation as lists
	 *
	 * Shows all the settings section labels as list items
	 */
	public static function show_navigation() {
		CL_Common::render_view( 'admin/settings-api-nav' );
	}

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 */
	public static function show_forms() {
		CL_Common::render_view( 'admin/settings-api-forms' );
	}

	/**
	 * Create a postbox widget.
	 *
	 * @param string $id ID of the postbox.
	 * @param string $title Title of the postbox.
	 * @param string $content Content of the postbox.
	 * @param string $group The class group
	 */
	public static function create_postbox( $id, $title, $content, $group = '' ) {
		$args = implode( '|', array( $id, $title, $content, $group ) );
		CL_Common::render_view( 'admin/create-postbox', null, $args );
	}

	/**
	 * Create social links to show in the sticky admin bar.
	 */
	public function sticky_social_links() {
		CL_Common::render_view( 'admin/sticky-social-links' );
	}

	/**
	 * Sidebar box about the plugin author.
	 *
	 * @param array $args Sidebar args
	 */
	public function sidebar_author( array $args ) {
		CL_Common::render_view( 'admin/sidebar-author', null, $args );
	}

	/**
	 * Sidebar box with news feed links.
	 *
	 * @param array $args Sidebar args
	 */
	public function sidebar_feed( array $args ) {
		CL_Common::render_view( 'admin/sidebar-feed', null, $args );
	}

	/**
	 * Add an admin notice if there is an upgrade that needs the users attention.
	 *
	 * @throws Exception
	 */
	public function maybe_add_notice() {

		try {
			if ( $this->has_upgrade() ) {
				$message = sprintf(
					esc_html__( 'Custom Login has detected old settings. If you wish to use them please run
						%sthis%s script before making any changes below.', Custom_Login::DOMAIN ),
					'<a href="' . esc_url( admin_url( 'options.php?page=custom-login-upgrades' ) ) . '">',
					'</a>'
				);
				throw new Exception( sprintf( $message . '%s', CL_Admin_Notices::NOTICE_CODE ) );
			}
		} catch( Exception $e ) {
			new CL_Admin_Notices( $e );
		}
	}

	/**
	 * Display Upgrade Notices
	 *
	 * @access private
	 * @return bool
	 */
	private function has_upgrade() {

		$has_upgrade = false;
		
		// Version > 2.0
		if ( false !== get_option( 'custom_login', false ) ) {
			$has_upgrade = true;
		}

		// Version > 3.0
		if ( false !== get_option( 'custom_login_general', false ) || false !== get_option( 'custom_login_design', false ) ) {
			$has_upgrade = true;
		}
		
		return $has_upgrade;
	}

	/**
	 * @param null $data
	 *
	 * @return array|null
	 */
	private function maybe_build_data_args( $data = null ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST ) ) {
			$data = array_map( 'sanitize_text_field', $_POST );
		}

		return $data;
	}
}
