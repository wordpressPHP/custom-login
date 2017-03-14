<?php

namespace PassyCo\CustomLogin;

use PassyCo\CustomLogin\Admin\AdminDashboard;
use PassyCo\CustomLogin\Admin\AdminNotices;
use PassyCo\CustomLogin\Admin\AdminTracking;
use PassyCo\CustomLogin\Admin\PluginsPhp;
use PassyCo\CustomLogin\Admin\SettingsImportExport;
use PassyCo\CustomLogin\Api\SettingsApi;
use PassyCo\CustomLogin\Api\WpRest;

/**
 * Class Hookup
 *
 * @package PassyCo\CustomLogin
 */
class Hookup implements WpHooksInterface {

    use HooksTrait;

    /** @var Init $init */
    protected $init;

    /**
     * Hookup constructor.
     *
     * @param Init $init
     */
    public function __construct( Init $init ) {
        $this->setInit( $init );
    }

    /**
     * Add class hooks.
     *
     * @uses `custom_login_init` A custom action hook called on the `init` hook; priority 10
     * @uses `custom_login_admin_init` A custom action hook called on the `init` hook; priority 10
     *     but only if the is_admin() condition is met.
     */
    public function addHooks() {
        $this->addAction( CustomLogin::INIT_ACTION, [ $this, 'doInit' ], 10, 2 );
        $this->addAction( CustomLogin::INIT_ACTION_ADMIN, [ $this, 'doAdminInit' ] );
    }

    /**
     * Initialize front and back-end classes.
     *
     * @param bool $is_login_page Is the current page the 'wp-login.php' page?
     * @param CustomLogin $custom_login
     */
    protected function doInit( $is_login_page, CustomLogin $custom_login ) {
        new Common( $custom_login );
        new Templates( $custom_login );

        /**
         * Instantiate classes not needed on the login page.
         * This speeds up the login page.
         */
        if ( ! $is_login_page ) {
            $this->getInit()->add( new Cron );
        }

        $this->getInit()
             ->add( new LoginCustomizer() )
             ->add( new WpLogin( $custom_login ) )
             ->add( new WpRest() );

        $this->setupSettingsApi( $custom_login );
        $this->getInit()->initialize();
    }

    /**
     * Initialize Admin only classes.
     *
     * @param CustomLogin $custom_login
     */
    protected function doAdminInit( CustomLogin $custom_login ) {
        new AdminNotices( $custom_login );

        $this->includes();
        $this->getInit()
             ->add( new PluginsPhp( $custom_login ) )
             ->add( new AdminDashboard( $custom_login ) )
             ->add( new AdminTracking( $custom_login ) )
//             ->add( new Extensions() )
             ->add( new SettingsImportExport() )
             ->initialize();
    }

    /**
     * @param Init $init
     */
    protected function setInit( Init $init ) {
        $this->init = $init;
    }

    /**
     * @return Init
     */
    protected function getInit() {
        return $this->init;
    }

    /**
     * Sets up the SettingsApi and DefaultSettings classes.
     *
     * @param CustomLogin $custom_login
     */
    private function setupSettingsApi( CustomLogin $custom_login ) {
        $settings_api = new SettingsApi( $custom_login );
        $this->getInit()->add( $settings_api );

        /**
         * Since this class is called in the `custom_login_init` action hook,
         * we need to make sure it's only loaded in the admin.
         */
        if ( is_admin() ) {
            $this->getInit()->add( new DefaultSettings( $settings_api ) );
            $settings_api->setSections( DefaultSettings::getRegisteredSettingsSections() );
            $settings_api->setFields( DefaultSettings::getRegisteredSettingsFields() );
        }
    }

    /**
     * Include functions file not loaded by the autoloader.
     */
    private function includes() {
        if ( ! class_exists( 'Frosty_Media_Remote_Install_Client', false ) ) {
            require_once __DIR__ . '/libraries/fm-remote-install-client/Frosty_Media_Remote_Install_Client.php';
        }
    }
}
