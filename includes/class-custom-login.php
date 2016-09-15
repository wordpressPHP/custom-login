<?php

if ( ! class_exists( 'Custom_Login_Bootstrap', true ) ) {

    /**
     * Class Custom_Login_Bootstrap
     */
    class Custom_Login_Bootstrap {

        const DOMAIN = 'custom-login';
        const ID = 'custom_login';
        const INIT_PRIORITY = 8;
        const INIT_ACTION = 'custom_login_init';
        const INIT_ACTION_ADMIN = 'custom_login_init_admin';

        /** @var string $file */
        protected $file;

        /** @var string $version */
        protected $version;

        /** @var CL_Injector $cl_injector */
        protected $cl_injector;

        /**
         * Custom_Login_Bootstrap constructor.
         *
         * @param string $file magic file constant
         * @param string $version Custom Login version
         */
        public function __construct( $file, $version ) {
            $this->file    = $file;
            $this->version = $version;
            $this->setup_constants();
            $this->load_dependency_injector();
            $this->add_hooks();
        }

        /**
         * Register our autoloader.
         */
        public function autoload_register() {
            spl_autoload_register( array( $this, 'autoload_classes' ) );
        }

        /**
         * Run a dependency check during initial load.
         */
        public function dependency_check() {
            $this->cl_injector->get_cl_dependency_check()->init();
        }

        /**
         * Setup our connected hooks.
         *      ...It's just a hookup
         */
        public function hookup() {
            $this->cl_injector->get_cl_hookup()->add_hooks();
        }

        /**
         * Create our custom action hooks.
         * These actions are called on the `init` hook; priority set in self::INIT_PRIORITY.
         */
        public function do_actions() {
            $is_wp_login_php = CL_Common::is_wp_login_php();

            /**
             * @param bool $is_wp_login_php Is the current page a "WordPress" login page?
             */
            do_action( self::INIT_ACTION, $is_wp_login_php );

            if ( is_admin() ) {
                do_action( self::INIT_ACTION_ADMIN );
            }
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
                // WordPress Admin classes
                $file = self::dir_path( "includes/admin/class-{$class_name}.php" );
            } elseif ( false !== strpos( $class_name, 'cl-customize' ) ) {
                // WordPress Customizer classes
                $file = self::dir_path( "includes/customize/class-{$class_name}.php" );
            } elseif ( false !== strpos( $class_name, 'cl-exception' ) ) {
                // Custom Login Interfaces
                $file = self::dir_path( "includes/exceptions/{$class_name}.php" );
            } elseif ( false !== strpos( $class_name, 'cl-interface' ) ) {
                // Custom Login Interfaces
                $file = self::dir_path( "includes/interfaces/{$class_name}.php" );
            } else {
                // Custom Login Classes
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

        /**
         * Setup plugin constants.
         */
        private function setup_constants() {
            defined( 'CUSTOM_LOGIN_VERSION' ) || define( 'CUSTOM_LOGIN_VERSION', $this->version );
            defined( 'CUSTOM_LOGIN_FILE' ) || define( 'CUSTOM_LOGIN_FILE', $this->file );
            defined( 'CUSTOM_LOGIN_DIR' ) || define( 'CUSTOM_LOGIN_DIR', plugin_dir_path( $this->file ) );
            defined( 'CUSTOM_LOGIN_URL' ) || define( 'CUSTOM_LOGIN_URL', plugin_dir_url( $this->file ) );
            defined( 'CUSTOM_LOGIN_BASENAME' ) || define( 'CUSTOM_LOGIN_BASENAME', plugin_basename( $this->file ) );
        }

        /**
         * Instantiate our dependency injector object.
         */
        private function load_dependency_injector() {
            $this->cl_injector = new CL_Injector( new CL_Dependency_Check, new CL_Hookup( new CL_Init ) );
        }

        /**
         * Setup the base action hooks.
         */
        private function add_hooks() {
            add_action( 'plugins_loaded', array( $this, 'autoload_register' ), 10 );
            add_action( 'plugins_loaded', array( $this, 'dependency_check' ), 989 );
            add_action( 'init', array( $this, 'hookup' ), 4 );
            add_action( 'init', array( $this, 'do_actions' ), self::INIT_PRIORITY );
        }
    }
}
