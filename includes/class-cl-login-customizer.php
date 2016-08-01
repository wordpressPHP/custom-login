<?php

use CL_Interface_WordPress_Hooks as WordPress_Hooks;
use Custom_Login_Bootstrap as Custom_Login;

/**
 * Class CL_Login_Customizer
 * Contains methods for customizing the theme customization screen.
 *
 * @link http://codex.wordpress.org/Theme_Customization_API
 */
class CL_Login_Customizer implements WordPress_Hooks {

	static $setting_id = 'design';

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'login_head', array( $this, 'login_head' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Get the setting id.
	 *      This is used to generate an associative array string value for the $wp_customize
	 *      `add_setting` and `add_control` methods.
	 *
	 * @param string $setting_id The 'name' or 'id' of the setting.
	 *
	 * @return string
	 */
	protected static function get_setting_id( $setting_id ) {
		return sprintf( '%s[%s][%s]', CL_Settings_API::SETTING_ID, self::$setting_id, $setting_id );
	}

	/**
	 * Get the section id.
	 *      This is used to generate an control setting string value for the $wp_customize
	 *      `add_section` method.
	 *
	 * @param string $section_id
	 *
	 * @return string
	 */
	protected static function get_section_id( $section_id ) {
		return sprintf( 'custom_login_%s', $section_id );
	}

	/**
	 * Get the control id.
	 *      This is used to generate an control setting string value for the $wp_customize
	 *      `add_control` method.
	 *
	 * @param string $control_id
	 *
	 * @return string
	 */
	protected static function get_control_id( $control_id ) {
		return sprintf( '%s_%s_%s', CL_Settings_API::SETTING_ID, self::$setting_id, $control_id );
	}

	/**
	 * @param array $args
	 * @param string $title
	 *
	 * @return array
	 */
	protected static function get_wp_customize_section_array( array $args, $title = '' ) {

		$defaults = array(
			'title'           => $title,
			'panel'           => CL_Settings_API::SETTING_ID, // WordPress 4.3
			'capability'      => CL_Common::get_option( 'capability', 'general', 'manage_options' ),
			'priority'        => 10,
			'description'     => '',
			'active_callback' => '__return_true',
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * @param array $args Incoming settings args array.
	 * @param mixed $default (Optional) The default value.
	 *
	 * @return array
	 */
	protected static function get_wp_customize_setting_array( array $args, $default = null ) {

		$defaults = array(
			'type'      => 'option',
			'transport' => 'postMessage',
			'default'   => $default,
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * @param array $args Incoming control args array.
	 * @param string $section_name The section this setting control is part of.
	 *
	 * @return array
	 */
	protected static function get_wp_customize_control_array( array $args, $section_name ) {

		$defaults = array(
			'label'   => '',
			'section' => self::get_section_id( $section_name ),
			//			'settings' => self::get_control_id( $setting_name ),
			'type'    => null,
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * This hooks into 'customize_register' (available as of WP 3.4) and allows
	 * you to add new sections and controls to the Theme Customize screen.
	 *
	 * @see add_action('customize_register',$func)
	 * @link http://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function register( WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_panel( CL_Settings_API::SETTING_ID,
			array(
				'priority'        => 10,
				'capability'      => CL_Common::get_option( 'capability', 'general', 'manage_options' ),
				'title'           => __( 'Custom Login', Custom_Login::DOMAIN ),
				'description'     => wpautop( sprintf( __( 'Style your wp-login.php page with ease.%s', Custom_Login::DOMAIN ), '&trade;' ) ),
				'active_callback' => array( 'CL_Common', 'is_wp_login_php' ),
			)
		);

		$default_settings = CL_Default_Settings::get_registered_settings_fields();
		$design_settings  = ! empty( $default_settings[ 'design' ] ) ? $default_settings[ 'design' ] : array();

		if ( empty( $design_settings ) ) {
			return; // @todo create an exception here
		}

		foreach ( $design_settings as $key => $setting ) {

			if ( ! isset( $setting[ 'customize' ] ) || ! is_array( $setting[ 'customize' ] ) ) {
				continue;
			}

			$customize    = $setting[ 'customize' ];
			$section_name = $customize[ 'section' ];
			$setting_name = isset( $setting[ 'name' ] ) ? $setting[ 'name' ] : sanitize_key( $setting[ 'label' ] );

			/**
			 * Add sections
			 */
			if ( isset( $customize[ 'add_section' ] ) && true === $customize[ 'add_section' ] ) {

				// Get the section args array
				$section_args = isset( $customize[ 'add_section' ][ 'args' ] )
				                && array() !== $customize[ 'add_section' ][ 'args' ] ?
					$customize[ 'add_section' ][ 'args' ] : array();

				$wp_customize->add_section(
					self::get_section_id( $section_name ),
					self::get_wp_customize_section_array( $section_args, $setting[ 'label' ] )
				);
			}

			/**
			 * Add settings & control
			 */
			if ( isset( $customize[ 'add_setting' ] ) && is_array( $customize[ 'add_setting' ] ) ) {

				// Get the setting default value
				$default = isset( $setting[ 'default' ] ) ? $setting[ 'default' ] : '';

				// Get the setting args array
				$setting_args = isset( $customize[ 'add_setting' ][ 'args' ] )
				                && array() !== $customize[ 'add_setting' ][ 'args' ] ?
					$customize[ 'add_setting' ][ 'args' ] : array();

				$wp_customize->add_setting(
					self::get_setting_id( $setting_name ),
					self::get_wp_customize_setting_array( $setting_args, $default )
				);

				/**
				 * Add settings controls
				 */
				if ( ( isset( $customize[ 'add_control' ] ) && is_array( $customize[ 'add_control' ] ) ) ) {

					// Get the control args array
					$control_args = isset( $customize[ 'add_control' ][ 'args' ] )
					                && array() !== $customize[ 'add_control' ][ 'args' ] ?
						$customize[ 'add_control' ][ 'args' ] : array();

					// Label
					if ( ! isset( $control_args[ 'label' ] ) ) {
						$control_args[ 'label' ] = $setting[ 'label' ];
					}

					/**
					 * Add settings control for custom callback class.
					 */
					if ( isset( $customize[ 'add_control' ][ 'callback' ] ) &&
					     is_string( $customize[ 'add_control' ][ 'callback' ] )
					) {

						$control_class = $customize[ 'add_control' ][ 'callback' ];

						/**
						 * Autoload the "$control_class" class...
						 */
						if ( class_exists( $control_class, true ) ) {
							$wp_customize->add_control(
								new $control_class(
									$wp_customize,
									self::get_setting_id( $setting_name ),
									self::get_wp_customize_control_array( $control_args, $section_name )
								)
							);
						}
					} else {
						$wp_customize->add_control(
							self::get_setting_id( $setting_name ),
							self::get_wp_customize_control_array( $control_args, $section_name )
						);
					}
				}
			}
		}
	}

	/**
	 * This will output the Custom Login settings to the login head.
	 */
	public function login_head() {

		if ( is_customize_preview() ) {
			CL_Common::render_view( 'login/wp-login' );
		}
	}

	/**
	 * This outputs the javascript needed to automate the live settings preview.
	 * Also keep in mind that this function isn't necessary unless your settings
	 * are using 'transport'=>'postMessage' instead  the default 'transpo' => 'refresh'
	 */
	public function enqueue_script() {
		wp_enqueue_script(
			Custom_Login::DOMAIN . '-customizer',
			plugins_url( 'js/customizer.js', CUSTOM_LOGIN_FILE ),
			array( 'jquery', 'jquery-ui-core', 'jquery-ui-slider' ),
			CUSTOM_LOGIN_VERSION,
			true
		);

		wp_enqueue_style(
			Custom_Login::DOMAIN . '-customizer',
			plugins_url( 'css/customizer.css', CUSTOM_LOGIN_FILE ),
			array(),
			CUSTOM_LOGIN_VERSION,
			'screen'
		);
	}

	/**
	 * This will generate a line of CSS for use in header output. If the setting
	 * ($mod_name) has no defined value, the CSS will not be output.
	 *
	 * @param string $selector CSS selector
	 * @param string $style The name of the CSS *property* to modify
	 * @param string $mod_name The name of the 'theme_mod' option to fetch
	 * @param string $prefix Optional. Anything that needs to be output before the CSS property
	 * @param string $postfix Optional. Anything that needs to be output after the CSS property
	 * @param bool $echo Optional. Whether to print directly to the page (default: true).
	 *
	 * @return string Returns a single line of CSS with selectors and a property.
	 */
	public static function generate_css( $selector, $style, $mod_name, $prefix = '', $postfix = '', $echo = true ) {

		$return = '';
		$css    = CL_Common::get_option( $mod_name, 'design', '' );

		if ( ! empty( $css ) ) {
			$return = sprintf( '%s { %s:%s; }',
				$selector,
				$style,
				$prefix . $css . $postfix
			);

			if ( $echo ) {
				echo $return;
			}
		}

		return $return;
	}
}
