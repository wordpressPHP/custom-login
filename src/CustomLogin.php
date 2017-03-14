<?php

namespace PassyCo\CustomLogin;

/**
 * Class CustomLogin
 *
 * @package PassyCo\CustomLogin
 */
class CustomLogin extends AbstractPlugin {

    const DOMAIN = 'custom-login';
    const ID = 'custom_login';

    const INIT_ACTION = 'custom_login_init';
    const INIT_ACTION_ADMIN = 'custom_login_init_admin';
    const INIT_PRIORITY = 8;

    /** @var Injector $injector */
    private $injector;

    /**
     * CustomLogin loaded
     */
    public function pluginsLoaded() {
        $this->setInjector( new Injector( new DependencyCheck, new Hookup( new Init ) ) );
        $this->addAction( 'plugins_loaded', [ $this, 'initDependencyCheck' ], 989 );
        $this->addAction( 'init', [ $this, 'initHookupHooks' ], self::INIT_PRIORITY / 2 );
        $this->addAction( 'init', [ $this, 'doActions' ], self::INIT_PRIORITY );
    }

    /**
     * @return Injector
     */
    public function getInjector() {
        return $this->injector;
    }

    /**
     * Run a dependency check during initial load.
     */
    protected function initDependencyCheck() {
        $this->getInjector()->getDependencyCheck()->init();
    }

    /**
     * Setup our connected Hookup object hooks.
     *      ...It's just a hookup
     */
    protected function initHookupHooks() {
        $this->getInjector()->getHookup()->addHooks();
    }

    /**
     * Create our custom action hooks.
     * These actions are called on the `init` hook; priority set in self::INIT_PRIORITY.
     */
    protected function doActions() {
        $is_wp_login_php = Common::isWpLoginDotPhp();

        /**
         * @param bool $is_wp_login_php Is the current page a "WordPress" login page?
         * @param CustomLogin $this Current instance of the CustomLogin object
         */
        do_action( self::INIT_ACTION, $is_wp_login_php, $this );

        if ( is_admin() ) {
            /**
             * @param CustomLogin $this Current instance of the CustomLogin object
             */
            do_action( self::INIT_ACTION_ADMIN, $this );
        }
    }

    /**
     * Instantiate the Dependency Injector object.
     *
     * @param Injector $injector
     */
    private function setInjector( Injector $injector ) {
        $this->injector = $injector;
    }

    /**
     * Setup plugin constants.
     */
    private function setConstants() {
        defined( 'CUSTOM_LOGIN_VERSION' ) || define( 'CUSTOM_LOGIN_VERSION', $this->getVersion() );
        defined( 'CUSTOM_LOGIN_FILE' ) || define( 'CUSTOM_LOGIN_FILE', $this->getFile() );
        defined( 'CUSTOM_LOGIN_DIR' ) || define( 'CUSTOM_LOGIN_DIR', $this->getDirectory() );
        defined( 'CUSTOM_LOGIN_URL' ) || define( 'CUSTOM_LOGIN_URL', $this->getUrl() );
        defined( 'CUSTOM_LOGIN_BASENAME' ) || define( 'CUSTOM_LOGIN_BASENAME', plugin_basename( $this->getFile() ) );
    }
}
