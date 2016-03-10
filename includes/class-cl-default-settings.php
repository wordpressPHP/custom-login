<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
		add_action( 'pre_update_option_' . CL_Settings_API::SETTING_ID, array(
			$this,
			'combine_settings_array',
		), 10, 2 );
		add_action( 'update_option_' . CL_Settings_API::SETTING_ID, array( $this, 'delete_transients' ) );
	}

	/**
	 * Initialize and register the settings sections and fields.
	 * This method is called on the `admin_init` hook.
	 *
	 * @see add_option
	 * @see add_settings_section
	 * @see add_settings_field
	 * @see register_setting
	 *
	 * @link http://wordpress.stackexchange.com/a/100137
	 */
	public function admin_init() {
		global $pagenow;

		// Only load the following when we're in the WordPress settings page.
		if ( empty( $pagenow ) || ! in_array( $pagenow, array( 'options-general.php', 'options.php' ) ) ) {
			return;
		}

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
					'id'          => $name,
					'desc'        => ! empty( $option[ 'desc' ] ) ? $option[ 'desc' ] : '',
					'name'        => $option[ 'label' ],
					'section'     => CL_Settings_API::SETTING_ID . '[' . $section_id . ']',
					'size'        => isset( $option[ 'size' ] ) ? $option[ 'size' ] : null,
					'options'     => isset( $option[ 'options' ] ) ? $option[ 'options' ] : array(),
					'default'     => isset( $option[ 'default' ] ) ? $option[ 'default' ] : '',
					'sanitize_cb' => isset( $option[ 'sanitize_cb' ] ) && ! empty( $option[ 'sanitize_cb' ] ) ?
						$option[ 'sanitize_cb' ] : '',
					'callback'    => isset( $option[ 'class' ] ) ?
						$option[ 'class' ] : ( new CL_Admin_Field_Types( $this->cl_settings_api ) ),
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
			array( $this, 'sanitize_options' )
		);
	}

	/**
	 * Sanitize callback
	 *
	 * @param array $options The incoming options
	 *
	 * @return array
	 */
	public function sanitize_options( $options ) {

		error_log( 'OPTIONS!! ' . print_r( $options, true ) );

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
	protected function get_sanitize_callback( $slug = '' ) {

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
				return isset( $option[ 'sanitize_cb' ] ) && is_callable( $option[ 'sanitize_cb' ] ) ?
					$option[ 'sanitize_cb' ] :
					false;
			}
		}

		return false;
	}

	/**
	 * This method combines our settings array into one multi-dimensional array.
	 *
	 * @param mixed $value The new, un-serialized option value.
	 * @param mixed $old_value The old option value.
	 *
	 * @return array
	 */
	public function combine_settings_array( $value, $old_value ) {
		return array_replace( $old_value, $value );
	}

	/**
	 * Delete our transients on settings save.
	 */
	public function delete_transients() {

		delete_transient( CL_Common::get_transient_key( 'style' ) );
		delete_transient( CL_Common::get_transient_key( 'script' ) );
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
				'desc'  => __( 'Use WordPress&lsquo; Appearance Customizer?', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'    => 'capability',
				'label'   => __( 'Capability', 'custom-login' ),
				'desc'    => __( 'The minimum capability a registered user needs to manage all Custom Login settings.', 'custom-login' ),
				'type'    => 'select',
				'size'    => 'large',
				'default' => 'manage_options',
				'options' => CL_Common::get_editable_roles(),
			),

			/**
			 * Section Login
			 */
			array(
				'name'  => '_section_login_functions',
				'label' => __( 'Login functions', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
			),

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

			/**
			 * Section Tracking
			 */
			array(
				'name'  => '_section_tracking',
				'label' => __( 'Tracking', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
			),

			array(
				'name'  => 'tracking',
				'label' => __( 'Usage tracking', 'custom-login' ),
				'desc'  => __( 'Allow anonymous tracking?', 'custom-login' ),
				// how this plugin is used? Opt-in and receive a 20% discount code for all Custom Login extensions. Get your coupon code <a href="http://frosty.media/?p=21442">here</a>.
				'type'  => 'checkbox',
			),

			/**
			 * Section Notices
			 */
			array(
				'name'  => '_section_notices',
				'label' => __( 'Notices', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
			),

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

			/**
			 * Section Import/Export
			 */
			array(
				'name'  => '_section_misc',
				'label' => __( 'Miscellaneous', 'custom-login' ),
				'desc'  => '',
				'type'  => 'html_break',
			),
			array(
				'name'  => 'import_export',
				'label' => __( 'Import/Export', 'custom-login' ),
				'desc'  => __( 'Allow the import and export feature.', 'custom-login' ),
				'type'  => 'checkbox',
			),
		);

		/**
		 * Design Settings Section
		 */
		$fields[ 'design' ] = array(

			/**
			 * Section HTML
			 */
			array(
				'name'      => '_section_html',
				'label'     => __( 'HTML', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'html',
					'add_section' => true,
				),
			),

			array(
				'name'       => 'html_background_color',
				'label'      => __( 'Background color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '#f1f1f1',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'html',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),
			array(
				'name'        => 'html_background_url',
				'label'       => __( 'Background image', 'custom-login' ),
				'desc'        => '',
				'type'        => 'file',
				'default'     => '',
				'size'        => 'large',
				'sanitize_cb' => 'esc_url',
				'customize'   => array(
					'section'     => 'html',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'WP_Customize_Image_Control',
						'args'     => array(),
					),
				),
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

			/**
			 * Section LOGO
			 */
			array(
				'name'      => '_section_logo',
				'label'     => __( 'Logo', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'logo',
					'add_section' => true,
				),
			),

			array(
				'name'  => 'hide_wp_logo',
				'label' => __( 'Hide the WP logo', 'custom-login' ),
				'desc'  => __( 'This setting hides the h1 element.', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'        => 'logo_background_url',
				'label'       => __( 'Image', 'custom-login' ),
				'desc'        => __( 'I would suggest a max width of 320px, the default form width. You can widen the width (setting below).', 'custom-login' ),
				'type'        => 'file',
				'default'     => '',
				'size'        => 'large',
				'sanitize_cb' => 'esc_url',
				'customize'   => array(
					'section'     => 'logo',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'WP_Customize_Image_Control',
						'args'     => array(),
					),
				),
			),
			array(
				'name'        => 'logo_background_size_width',
				'label'       => __( 'Image width', 'custom-login' ),
				'desc'        => __( 'Enter your desired image height (All not integers will be removed).', 'custom-login' ),
				'type'        => 'text_number',
				'size'        => 'small',
				'default'     => '',
				'sanitize_cb' => 'int',
			),
			array(
				'name'        => 'logo_background_size_height',
				'label'       => __( 'Image height', 'custom-login' ),
				'desc'        => __( 'Enter your desired image height (All not integers will be removed).', 'custom-login' ),
				'type'        => 'text_number',
				'size'        => 'small',
				'default'     => '',
				'sanitize_cb' => 'int',
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

			/**
			 * Section FORM
			 */
			array(
				'name'      => '_section_form',
				'label'     => __( 'Login Form', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'form',
					'add_section' => true,
				),
			),

			array(
				'name'  => 'logo_force_form_max_width',
				'label' => __( 'Force max-width', 'custom-login' ),
				'desc'  => __( 'If checked and the login form width (set below) is not empty, a CSS rule of <code>width</code> will be applied on the logo wrapper element <code>.login h1</code>. This settings applies to the Logo image (when background size is used).', 'custom-login' ),
				'type'  => 'checkbox',
			),
			array(
				'name'        => 'login_form_width',
				'label'       => __( 'Width', 'custom-login' ),
				'desc'        => __( 'Change the default width of the login form.', 'custom-login' ),
				'type'        => 'text_number',
				'size'        => 'small',
				'default'     => '320',
				'sanitize_cb' => 'int',
			),
			array(
				'name'       => 'login_form_background_color',
				'label'      => __( 'Background color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'form',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),
			array(
				'name'        => 'login_form_background_url',
				'label'       => __( 'Background URL', 'custom-login' ),
				'desc'        => __( 'Add a background image to the login form.', 'custom-login' ),
				'type'        => 'file',
				'default'     => '',
				'size'        => 'large',
				'sanitize_cb' => 'esc_url',
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
				'name'        => 'login_form_border_radius',
				'label'       => __( 'Border radius', 'custom-login' ),
				'desc'        => '',
				'type'        => 'text_number',
				'size'        => 'small',
				'default'     => '',
				'sanitize_cb' => 'int',
			),
			array(
				'name'        => 'login_form_border_size',
				'label'       => __( 'Border size', 'custom-login' ),
				'desc'        => '',
				'type'        => 'text_number',
				'size'        => 'small',
				'default'     => '',
				'sanitize_cb' => 'int',
			),
			array(
				'name'       => 'login_form_border_color',
				'label'      => __( 'Border color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'form',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
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
				'name'       => 'login_form_box_shadow_color',
				'label'      => __( 'Box shadow color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'form',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),

			/**
			 * Section MISCELLANEOUS
			 */
			array(
				'name'      => '_section_miscellaneous',
				'label'     => __( 'Miscellaneous', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'miscellaneous',
					'add_section' => true,
				),
			),

			array(
				'name'       => 'label_color',
				'label'      => __( 'Label color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'miscellaneous',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),

			array(
				'name'       => 'nav_color',
				'label'      => __( 'Nav color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'miscellaneous',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),
			array(
				'name'       => 'nav_text_shadow_color',
				'label'      => __( 'Nav text-shadow color', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'miscellaneous',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),
			array(
				'name'       => 'nav_hover_color',
				'label'      => __( 'Nav color hover', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'miscellaneous',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),
			array(
				'name'       => 'nav_text_shadow_hover_color',
				'label'      => __( 'Nav text-shadow hover', 'custom-login' ),
				'desc'       => '',
				'type'       => 'alphacolor',
				'default'    => '',
				'attributes' => array(
					'data-alpha' => 'true',
				),
				'customize'  => array(
					'section'     => 'miscellaneous',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Alpha_Color_Control',
						'args'     => array(
							'type'    => 'alphacolor',
							'palette' => true,
						),
					),
				),
			),

			/**
			 * Section CSS
			 */
			array(
				'name'      => '_section_css',
				'label'     => __( 'Custom CSS', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'custom_css',
					'add_section' => true,
				),
			),

			array(
				'name'        => 'custom_css',
				'label'       => '',
				'desc'        => '',
				'type'        => 'textarea',
				'sanitize_cb' => 'wp_filter_nohtml_kses',
				'attributes'  => array(
					'data-codemirror' => 'true',
					'data-type'       => 'text/css',
				),
				'customize'   => array(
					'section'     => 'custom_css',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Textarea_Control',
						'args'     => array(),
					),
				),
			),
			array(
				'name'      => 'animate.css',
				'label'     => __( 'Animate', 'custom-login' ),
				'desc'      => sprintf( __( 'Enqueue <a href="%s">animate.css</a> on the login page?', 'custom-login' ), 'http://daneden.github.io/animate.css/' ),
				'type'      => 'checkbox',
				'customize' => array(
					'section'     => 'custom_css',
					'add_setting' => array(),
					'add_control' => array(
						'args' => array(
							'type' => 'checkbox',
						),
					),
				),
			),

			/**
			 * Section CUSTOM HTML
			 */
			array(
				'name'      => '_section_custom_html',
				'label'     => __( 'Custom HTML', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'custom_html',
					'add_section' => true,
				),
			),

			array(
				'name'        => 'custom_html',
				'label'       => '',
				'desc'        => '',
				'type'        => 'textarea',
				'sanitize_cb' => 'wp_kses_post', //Allow HTML
				'attributes'  => array(
					'data-codemirror' => 'true',
					'data-type'       => 'text/html',
				),
				'customize'   => array(
					'section'     => 'custom_html',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Textarea_Control',
						'args'     => array(),
					),
				),
			),

			/**
			 * Section CUSTOM jQuery
			 */
			array(
				'name'      => '_section_custom_jquery',
				'label'     => __( 'Custom jQuery', 'custom-login' ),
				'desc'      => '',
				'type'      => 'html_break',
				'customize' => array(
					'section'     => 'custom_jquery',
					'add_section' => true,
				),
			),

			array(
				'name'        => 'custom_jquery',
				'label'       => '',
				'desc'        => '',
				'type'        => 'textarea',
				'sanitize_cb' => 'wp_specialchars_decode',
				'attributes'  => array(
					'data-codemirror' => 'true',
					'data-type'       => 'text/javascript',
				),
				'customize'   => array(
					'section'     => 'custom_jquery',
					'add_setting' => array(),
					'add_control' => array(
						'callback' => 'CL_Customize_Textarea_Control',
						'args'     => array(),
					),
				),
			),
		);

		return apply_filters( 'custom_login_registered_settings_fields', $fields );
	}

}
