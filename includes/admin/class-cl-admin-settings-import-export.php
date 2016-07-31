<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Admin_Settings_Import_Export
 */
class CL_Admin_Settings_Import_Export implements CL_WordPress_Hooks {

	private $setting_id;
	private $ID;

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		if ( ! $this->is_import_export_on() ) {
			return;
		}

		$this->setup_variables();
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Is the Import/Export feature active (on).
	 *
	 * @return bool
	 */
	private function is_import_export_on() {
		return 'on' === CL_Common::get_option( 'import_export', 'general', 'off' );
	}

	/**
	 * Setup class variables.
	 */
	private function setup_variables() {

		$this->setting_id = CL_Settings_API::SETTING_ID;
		$this->ID         = 'import_export';
	}

	/**
	 * Add class actions.
	 */
	private function add_actions() {

		add_action( $this->setting_id . '_before_sanitize_options', array( $this, 'maybe_import_settings' ) );
		add_action( 'admin_action_' . $this->setting_id . '_download_export', array( $this, 'download_export' ) );
	}

	/**
	 * Add class filters.
	 */
	private function add_filters() {

		add_filter( $this->setting_id . '_registered_settings_sections', array( $this, 'add_settings_sections' ) );
		add_filter( $this->setting_id . '_registered_settings_fields', array( $this, 'add_settings_fields' ) );
	}

	/**
	 * Return the full array of settings
	 *
	 * @access private
	 */
	private function get_encoded_settings() {
		return base64_encode( maybe_serialize( get_option( CL_Settings_API::SETTING_ID, array() ) ) );
	}

	/**
	 * Sanitize callback for Settings API before input into database.
	 *
	 * @link http://stackoverflow.com/a/10797086/558561
	 *
	 * @param array $options
	 */
	public function maybe_import_settings( $options ) {

		if ( ! isset( $options[ $this->ID ] ) || ! isset( $options[ $this->ID ][ 'import' ] ) ) {
			return;
		}

		$import = $options[ $this->ID ][ 'import' ];

		if ( ! empty( $import ) && ( base64_encode( base64_decode( $import, true ) ) === $import ) ) {

			$new_options = maybe_unserialize( base64_decode( $import ) );

			if ( is_array( $new_options ) && array() !== $new_options ) {
				if ( update_option( CL_Settings_API::SETTING_ID, $new_options ) ) {
					add_settings_error(
						$this->setting_id,
						'settings_updated',
						__( 'Custom Login Settings import was successful.', Custom_Login_Bootstrap::DOMAIN ),
						'updated'
					);
				}
			}
		}
	}

	/**
	 * Export the settings.
	 *
	 * @link http://stackoverflow.com/a/16440501/558561
	 */
	public function download_export() {

		if ( ! isset( $_GET[ 'cl_nonce' ] ) || ! wp_verify_nonce( $_GET[ 'cl_nonce' ], 'export' ) ) {
			wp_redirect( remove_query_arg( array( 'action', 'cl_nonce' ) ) );
			exit;
		}

		$date = date( 'Y-m-d' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-type: text/plain; charset=utf-8' );
		header( "Content-Disposition: attachment; filename=custom-login-settings-export-{$date}.txt" );
		header( 'Expires: 0' );

		echo $this->get_encoded_settings();
		exit;
	}

	/**
	 * @param $sections
	 *
	 * @return array
	 */
	public function add_settings_sections( $sections ) {

		$sections[] = array(
			'id'     => $this->ID,
			'title'  => __( 'Import/Export Settings', Custom_Login_Bootstrap::DOMAIN ),
			'submit' => true,
		);

		return $sections;
	}

	/**
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function add_settings_fields( $fields ) {

		$fields[ $this->ID ] = array(
			array(
				'name'        => 'import',
				'label'       => __( 'Import', Custom_Login_Bootstrap::DOMAIN ),
				'desc'        => '',
				'type'        => 'textarea',
				'sanitize_cb' => '__return_empty_string',
			),

			array(
				'name'        => 'export',
				'label'       => __( 'Export', Custom_Login_Bootstrap::DOMAIN ),
				'desc'        => sprintf(
					__( 'This textarea is always pre-populated with the current settings.
Copy these settings to import at a later time, or <a href="%s">click here</a> to download them.',
						Custom_Login_Bootstrap::DOMAIN ),
					esc_url( wp_nonce_url(
						add_query_arg( array( 'action' => $this->setting_id . '_download_export' ),
							''
						),
						'export',
						'cl_nonce'
					) )
				),
				'default'     => $this->get_encoded_settings(),
				'type'        => 'textarea',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
				'sanitize_cb' => '__return_empty_string',
			),

		);

		return $fields;
	}

}
