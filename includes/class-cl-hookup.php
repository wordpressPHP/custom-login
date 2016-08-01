<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Hookup
 */
class CL_Hookup {

    /**
     * @var CL_Init
     */
    protected $cl_init;

    /**
     * CL_Hookup constructor.
     *
     * @param \CL_Init $cl_init
     */
    public function __construct( CL_Init $cl_init ) {
        $this->cl_init = $cl_init;
    }

	/**
	 * Add class hooks.
	 *
	 * @uses `custom_login_init` A custom action hook called on the `init` hook; priority 10
	 * @uses `custom_login_admin_init` A custom action hook called on the `init` hook; priority 10 but only if
	 *      the is_admin() condition is met.
	 */
	public function add_hooks() {

		add_action( 'custom_login_init', array( $this, 'init' ) );
		add_action( 'custom_login_admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Initialize front and back-end classes.
	 *
	 * @param bool $is_login_page Is the current page the 'wp-login.php' page?
	 */
	public function init( $is_login_page ) {

		/**
		 * Instantiate classes not needed on the login page.
		 * This speeds up the login page.
		 */
		if ( ! $is_login_page ) {
			$this->cl_init
                ->add( new CL_Cron );
		}

        $this->cl_init
            ->add( new CL_Login_Customizer )
            ->add( new CL_WP_Login )
            ->initialize();

		$this->setup_settings_api();
	}

	/**
	 * Initialize backend only classes.
	 */
	public function admin_init() {

		$this->includes();

        $this->cl_init
            ->add( new CL_Admin_Plugin_PHP )
            ->add( new CL_Admin_Dashboard )
            ->add( new CL_Admin_Tracking )
            ->add( new CL_Extensions )
            ->add( new CL_Admin_Settings_Import_Export )
            ->initialize();
	}

	/**
	 * Sets up the Settings API and Default Settings classes.
	 */
	private function setup_settings_api() {

		$cl_settings_api = CL_Settings_API::get_instance();
		$cl_settings_api->add_hooks();

		/**
		 * Since this class is called in the `custom_login_init` action hook,
		 * we need to make sure it's only loaded in the admin.
		 */
		if ( is_admin() ) {
			$cl_default_settings = new CL_Default_Settings( $cl_settings_api );
			$cl_default_settings->add_hooks();
			$cl_settings_api->set_sections( $cl_default_settings->get_registered_settings_sections() );
			$cl_settings_api->set_fields( $cl_default_settings->get_registered_settings_fields() );
		}
	}

	/**
	 * Include functions file not loaded by the autoloader.
	 */
	private function includes() {
		require_once CUSTOM_LOGIN_DIR . 'includes/functions.php';

		if ( ! class_exists( 'Frosty_Media_Remote_Install_Client', false ) ) {
			require_once( CUSTOM_LOGIN_DIR . 'includes/libraries/fm-remote-install-client/Frosty_Media_Remote_Install_Client.php' );
		}
	}
}
