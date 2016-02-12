<?php

/**
 * Class CL_Default_Settings
 */
class CL_Default_Settings {

	/**
	 * @var CL_Settings_API
	 */
	private $cl_settings_api;

	/**
	 * CL_Default_Settings constructor.
	 *
	 * @param CL_Settings_API $settings_api
	 */
	public function __construct( CL_Settings_API $settings_api ) {
		$this->cl_settings_api = $settings_api;
	}

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Initialize and register the settings sections and fields.
	 * This method is called on the `admin_init` hook.
	 *
	 * @see add_option
	 * @see add_settings_section
	 * @see add_settings_field
	 * @see register_setting
	 */
	public function admin_init() {

		// Add the setting option if it doesn't exist.
		if ( false === get_option( CL_Settings_API::SETTING_ID ) ) {
			add_option( CL_Settings_API::SETTING_ID, array() );
		}

		// Register the settings sections.
		foreach ( $this->cl_settings_api->settings_sections as $section ) {
			add_settings_section(
				CL_Settings_API::SETTING_ID . '_' . $section[ 'id' ],
				$section[ 'title' ],
				'__return_false',
				CL_Settings_API::SETTING_ID . '_' . $section[ 'id' ]
			);
		}

		// Register settings fields.
		foreach ( $this->cl_settings_api->settings_fields as $section_id => $field_args ) {
			foreach ( $field_args as $option ) {

				$type = isset( $option[ 'type' ] ) ? $option[ 'type' ] : 'text';
				$name = isset( $option[ 'name' ] ) ? $option[ 'name' ] : strtolower( $option[ 'label' ] );

				$args = array(
					'id'       => $name,
					'desc'     => ! empty( $option[ 'desc' ] ) ? $option[ 'desc' ] : '',
					'name'     => $option[ 'label' ],
					'section'  => CL_Settings_API::SETTING_ID . '[' . $section_id . ']',
					'size'     => isset( $option[ 'size' ] ) ? $option[ 'size' ] : null,
					'options'  => isset( $option[ 'options' ] ) ? $option[ 'options' ] : array(),
					'default'  => isset( $option[ 'default' ] ) ? $option[ 'default' ] : '',
					'sanitize' => isset( $option[ 'sanitize' ] ) && ! empty( $option[ 'sanitize' ] ) ? $option[ 'sanitize' ] : '',
					'callback' => isset( $option[ 'class' ] ) ? $option[ 'class' ] : ( new CL_Admin_Field_Types( $this->cl_settings_api ) ),
				);
				$args = wp_parse_args( $args, $option );

				add_settings_field(
					CL_Settings_API::SETTING_ID . '[' . $section_id . '][' . $name . ']',
					$option[ 'label' ],
					array( $args[ 'callback' ], $type ),
					CL_Settings_API::SETTING_ID . '_' . $section_id,
					CL_Settings_API::SETTING_ID . '_' . $section_id,
					$args
				);
			}
		}

		register_setting(
			CL_Settings_API::SETTING_ID,
			CL_Settings_API::SETTING_ID,
			array( $this, 'sanitize_options', )
		);
	}

	/**
	 * Sanitize callback
	 *
	 * @param array $options The incoming options
	 *
	 * @return array
	 */
	function sanitize_options( $options ) {

		if ( is_null( $options ) || ! is_array( $options ) ) {
			return $options;
		}

		do_action( CL_Settings_API::SETTING_ID . '_before_sanitize_options', $options );

		foreach ( $options as $_option_key => $_options_array ) {
			foreach ( $_options_array as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );

				// If callback is set, call it
				if ( $sanitize_callback ) {
					$options[ $_option_key ][ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					continue;
				}

				// Treat everything that's not an array as a string
				if ( ! is_array( $option_value ) ) {
					$options[ $_option_key ][ $option_slug ] = sanitize_text_field( $option_value );
					continue;
				}
			}
		}

		do_action( CL_Settings_API::SETTING_ID . '_after_sanitize_options', $options );

		return $options;
	}

	/**
	 * Get sanitized callback for given option slug.
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed object on success or false on failure.
	 */
	function get_sanitize_callback( $slug = '' ) {

		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->cl_settings_api->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option[ 'name' ] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option[ 'sanitize' ] ) && is_callable( $option[ 'sanitize' ] ) ? $option[ 'sanitize' ] : false;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function get_registered_settings_sections() {

		$sections = array(
			array(
				'id'     => 'general',
				'title'  => __( 'General Settings', 'custom-login' ),
				'submit' => true,
			),
			array(
				'id'     => 'design',
				'title'  => __( 'Design Settings', 'custom-login' ),
				'href'   => esc_url_raw( add_query_arg(
					array(
						'url'    => urlencode( wp_login_url() ),
						'return' => urlencode( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ),
						array(
							'autofocus' => array( 'panel' => CL_Settings_API::SETTING_ID ),
						),
					),
					admin_url( 'customize.php' )
				) ),
				'submit' => false,
			),
		);

		return apply_filters( 'custom_login_registered_settings_sections', $sections );
	}

	/**
	 * @return array
	 */
	public static function get_registered_settings_fields() {

		/**
		 * General Settings Section
		 */
		$fields[ 'general' ] = array(
			array(
				'name'  => 'active',
				'label' => __( 'Activate', 'custom-login' ),
				'desc'  => __( 'Display your Custom Login design!', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'use_customizer',
				'label' => __( 'Customizer', 'custom-login' ),
				'desc'  => __( 'Use the design customizer?', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'    => 'capability',
				'label'   => __( 'Capability', 'custom-login' ),
				'desc'    => sprintf( __( 'Set the minimum capability a user needs to manage the Custom Login settings. The default capability is <code>%s</code>', 'custom-login' ), 'manage_options' ),
				'type'    => 'select',
				'size'    => 'large',
				'default' => 'manage_options',
				'options' => CL_Common::get_editable_roles(),
			),

			/** BREAK **/
			array(
				'label' => sprintf( '<h4>%s</h4>', __( 'Login functions', 'custom-login' ) ),
				'desc'  => '',
				'type'  => 'html_break',
			),
			/** BREAK **/

			array(
				'name'  => 'wp_shake_js',
				'label' => __( 'Disable Login shake', 'custom-login' ),
				'desc'  => __( 'Disable the login forms animated "shake" on error.', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'remove_login_css',
				'label' => __( 'Remove login CSS', 'custom-login' ),
				'desc'  => __( 'Remove WordPress\' enqueued login CSS file? Warning: You\'ll have to add additional styles that are not set by this plugin.', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'lostpassword_text',
				'label' => __( 'Remove lost password text', 'custom-login' ),
				'desc'  => __( 'Remove the &ldquo;Lost Password?&rdquo; text. This does <strong>not</strong> disable the lost password function.', 'custom-login' ),
				'type'  => 'checkbox',
			),

			/** BREAK **/
			array(
				'label' => sprintf( '<h4>%s</h4>', __( 'Tracking', 'custom-login' ) ),
				'desc'  => '',
				'type'  => 'html_break',
			),
			/** BREAK **/

			array(
				'name'  => 'tracking',
				'label' => __( 'Usage tracking', 'custom-login' ),
				'desc'  => __( 'Allow anonymous tracking?', 'custom-login' ),
				// how this plugin is used? Opt-in and receive a 20% discount code for all Custom Login extensions. Get your coupon code <a href="http://frosty.media/?p=21442">here</a>.
				'type'  => 'checkbox',
			),

			/** BREAK **/
			array(
				'label' => sprintf( '<h4>%s</h4>', __( 'Notices', 'custom-login' ) ),
				'desc'  => '',
				'type'  => 'html_break',
			),
			/** BREAK **/

			array(
				'name'  => 'admin_notices',
				'label' => __( 'Admin notices', 'custom-login' ),
				'desc'  => __( 'Allow admin notices from Custom Login.', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'dashboard_widget',
				'label' => __( 'Dashboard widget', 'custom-login' ),
				'desc'  => __( 'Show a dashboard widget, like WordPress news for Frosty Media.', 'custom-login' ),
				'type'  => 'checkbox',
			),
		);

		/**
		 * Design Settings Section
		 */
		$fields[ 'design' ] = array(
			/** BREAK **/
			array(
				'label'     => __( 'HTML', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'    => 'html_background_color',
				'label'   => __( 'Background color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '#f1f1f1',
				'customize' => array(
					'add_setting' => array(
					),
					'add_control' => array(
						'callback' => 'CL_Customize_Color_Alpha_Control'
					),
				),
			),
			array(
				'name'     => 'html_background_url',
				'label'    => __( 'Background image', 'custom-login' ),
				'desc'     => '',
				'type'     => 'file',
				'default'  => '',
				'size'     => 'large',
				'sanitize' => 'esc_url',
			),
			array(
				'name'    => 'html_background_position',
				'label'   => __( 'Background position', 'custom-login' ),
				'desc'    => sprintf( '<a href="http://www.w3schools.com/cssref/pr_background-position.asp" target="_blank">%s</a>.', __( 'html background position', 'custom-login' ) ),
				'type'    => 'select',
				'options' => array(
					'left top'      => 'left top',
					'left center'   => 'left center',
					'left bottom'   => 'left bottom',
					'right top'     => 'right top',
					'right center'  => 'right center',
					'right bottom'  => 'right bottom',
					'center top'    => 'center top',
					'center center' => 'center center',
					'center bottom' => 'center bottom',
				),
			),
			array(
				'name'    => 'html_background_repeat',
				'label'   => __( 'Background repeat', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'no-repeat' => 'no-repeat',
					'repeat'    => 'repeat',
					'repeat-x'  => 'repeat-x',
					'repeat-y'  => 'repeat-y',
				),
			),
			array(
				'name'    => 'html_background_size',
				'label'   => __( 'Background size', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'none'    => 'none',
					'cover'   => 'cover',
					'contain' => 'contain',
					'flex'    => 'flex',
				),
			),

			/** BREAK **/
			array(
				'label' => __( 'Logo', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'  => 'hide_wp_logo',
				'label' => __( 'Hide the WP logo', 'custom-login' ),
				'desc'  => __( 'This setting hides the h1 element.', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'     => 'logo_background_url',
				'label'    => __( 'Image', 'custom-login' ),
				'desc'     => __( 'I would suggest a max width of 320px, the default form width. You can widen the width (setting below).', 'custom-login' ),
				'type'     => 'file',
				'default'  => '',
				'size'     => 'large',
				'sanitize' => 'esc_url',
			),
			array(
				'name'     => 'logo_background_size_width',
				'label'    => __( 'Image width', 'custom-login' ),
				'desc'     => __( 'Enter your desired image height (All not integers will be removed).', 'custom-login' ),
				'type'     => 'text_number',
				'size'     => 'small',
				'default'  => '',
				'sanitize' => 'int',
			),
			array(
				'name'     => 'logo_background_size_height',
				'label'    => __( 'Image height', 'custom-login' ),
				'desc'     => __( 'Enter your desired image height (All not integers will be removed).', 'custom-login' ),
				'type'     => 'text_number',
				'size'     => 'small',
				'default'  => '',
				'sanitize' => 'int',
			),
			array(
				'name'    => 'logo_background_position',
				'label'   => __( 'Background position', 'custom-login' ),
				'desc'    => sprintf( '<a href="http://www.w3schools.com/cssref/pr_background-position.asp" target="_blank">%s</a>', __( 'html background position', 'custom-login' ) ),
				'type'    => 'select',
				'options' => array(
					'left top'      => 'left top',
					'left center'   => 'left center',
					'left bottom'   => 'left bottom',
					'right top'     => 'right top',
					'right center'  => 'right center',
					'right bottom'  => 'right bottom',
					'center top'    => 'center top',
					'center center' => 'center center',
					'center bottom' => 'center bottom',
				),
			),
			array(
				'name'    => 'logo_background_repeat',
				'label'   => __( 'Background repeat', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'no-repeat' => 'no-repeat',
					'repeat'    => 'repeat',
					'repeat-x'  => 'repeat-x',
					'repeat-y'  => 'repeat-y',
				),
			),
			array(
				'name'    => 'logo_background_size',
				'label'   => __( 'Background size', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'none'    => 'none',
					'cover'   => 'cover',
					'contain' => 'contain',
					'flex'    => 'flex',
				),
			),

			/** BREAK **/
			array(
				'label' => __( 'Login Form', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'  => 'logo_force_form_max_width',
				'label' => __( 'Force max-width', 'custom-login' ),
				'desc'  => __( 'If checked and the login form width (set below) is not empty, a CSS rule of <code>width</code> will be applied on the logo wrapper element <code>.login h1</code>. This settings applies to the Logo image (when background size is used).', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'     => 'login_form_width',
				'label'    => __( 'Width', 'custom-login' ),
				'desc'     => __( 'Change the default width of the login form.', 'custom-login' ),
				'type'     => 'text_number',
				'size'     => 'small',
				'default'  => '320',
				'sanitize' => 'int',
			),
			array(
				'name'    => 'login_form_background_color',
				'label'   => __( 'Background color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),
			array(
				'name'     => 'login_form_background_url',
				'label'    => __( 'Background URL', 'custom-login' ),
				'desc'     => __( 'Add a background image to the login form.', 'custom-login' ),
				'type'     => 'file',
				'default'  => '',
				'size'     => 'large',
				'sanitize' => 'esc_url',
			),
			array(
				'name'    => 'login_form_background_position',
				'label'   => __( 'Background position', 'custom-login' ),
				'desc'    => sprintf( '<a href="http://www.w3schools.com/cssref/pr_background-position.asp" target="_blank">%s</a>', __( 'html background position', 'custom-login' ) ),
				'type'    => 'select',
				'options' => array(
					'left top'      => 'left top',
					'left center'   => 'left center',
					'left bottom'   => 'left bottom',
					'right top'     => 'right top',
					'right center'  => 'right center',
					'right bottom'  => 'right bottom',
					'center top'    => 'center top',
					'center center' => 'center center',
					'center bottom' => 'center bottom',
				),
			),
			array(
				'name'    => 'login_form_background_repeat',
				'label'   => __( 'Background repeat', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'no-repeat' => 'no-repeat',
					'repeat'    => 'repeat',
					'repeat-x'  => 'repeat-x',
					'repeat-y'  => 'repeat-y',
				),
			),
			array(
				'name'    => 'login_form_background_size',
				'label'   => __( 'Background size', 'custom-login' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => array(
					'none'    => 'none',
					'cover'   => 'cover',
					'contain' => 'contain',
					'flex'    => 'flex',
				),
			),
			array(
				'name'     => 'login_form_border_radius',
				'label'    => __( 'Border radius', 'custom-login' ),
				'desc'     => '',
				'type'     => 'text_number',
				'size'     => 'small',
				'default'  => '',
				'sanitize' => 'int',
			),
			array(
				'name'     => 'login_form_border_size',
				'label'    => __( 'Border size', 'custom-login' ),
				'desc'     => '',
				'type'     => 'text_number',
				'size'     => 'small',
				'default'  => '',
				'sanitize' => 'int',
			),
			array(
				'name'    => 'login_form_border_color',
				'label'   => __( 'Border color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),
			array(
				'name'    => 'login_form_box_shadow',
				'label'   => __( 'Box shadow', 'custom-login' ),
				'desc'    => sprintf( __( 'Use <a href="%s" target="_blank">box shadow</a> syntax w/ out color. <code>inset h-shadow v-shadow blur spread</code>', 'custom-login' ), 'http://www.w3schools.com/cssref/css3_pr_box-shadow.asp' ),
				'type'    => 'text',
				'size'    => 'medium',
				'default' => '5px 5px 10px',
			),
			array(
				'name'    => 'login_form_box_shadow_color',
				'label'   => __( 'Box shadow color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),

			/** BREAK **/
			array(
				'label' => __( 'Miscellaneous', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'    => 'label_color',
				'label'   => __( 'Label color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),

			/** BREAK **/
			array(
				'label' => __( 'Below Form anchor', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'    => 'nav_color',
				'label'   => __( 'Nav color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),
			array(
				'name'    => 'nav_text_shadow_color',
				'label'   => __( 'Nav text-shadow color', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),
			array(
				'name'    => 'nav_hover_color',
				'label'   => __( 'Nav color hover', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),
			array(
				'name'    => 'nav_text_shadow_hover_color',
				'label'   => __( 'Nav text-shadow hover', 'custom-login' ),
				'desc'    => '',
				'type'    => 'colorpicker',
				'default' => '',
			),

			/** BREAK **/
			array(
				'label' => __( 'Custom CSS', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'     => 'custom_css',
				'label'    => '',
				'desc'     => sprintf( '%s %s', __( 'Allowed variables:', 'custom-login' ), '<ul>
			<li>{BSLASH} = "\" (backslash)</li>
			<li><a href="http://wordpress.org/support/topic/quotes-in-custom-css-gets-replaced-with-useless-quote?replies=4">Request others</a></li>
			</ul>' ),
				'type'     => 'textarea',
				'sanitize' => 'wp_filter_nohtml_kses',
			),
			array(
				'name'  => 'animate.css',
				'label' => __( 'Animate', 'custom-login' ),
				'desc'  => __( 'Include <a href="http://daneden.github.io/animate.css/">animate.css</a>?', 'custom-login' ),
				'type'  => 'checkbox',
			),

			/** BREAK **/
			array(
				'label' => __( 'Custom HTML', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'     => 'custom_html',
				'label'    => '',
				'desc'     => '',
				'type'     => 'textarea',
				'sanitize' => 'wp_kses_post', //Allow HTML
			),

			/** BREAK **/
			array(
				'label' => __( 'Custom jQuery', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
				'customize' => array(
					'add_section' => true,
				),
			),
			/** BREAK **/

			array(
				'name'     => 'custom_jquery',
				'label'    => '',
				'desc'     => '',
				'type'     => 'textarea',
				'sanitize' => 'wp_specialchars_decode',
			),
		);

		return apply_filters( 'custom_login_registered_settings_fields', $fields );
	}

}
