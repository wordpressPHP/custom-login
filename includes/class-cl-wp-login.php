<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_WP_Login
 */
class CL_WP_Login implements CL_WordPress_Hooks {

    /**
     * @var array
     */
    public static $css_atts = array();
    public static $js_atts = array();

    /**
     * Add class hooks.
     */
    public function add_hooks() {

        if ( ! $this->is_active() || $this->is_iframe_request() ) {
            return;
        }

        $this->add_actions();
        $this->add_filters();
    }

    /**
     * Is Custom Login active (on).
     *
     * @return bool
     */
    private function is_active() {
        return 'on' === CL_Common::get_option( 'active', 'general', 'off' );
    }

    /**
     * Is the current request in an iFrame?
     *
     * @return bool
     */
    private function is_iframe_request() {
        return defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST;
    }

    /**
     * WordPress action hooks
     */
    private function add_actions() {

        add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
        add_action( 'login_footer', array( $this, 'login_footer_html' ), 8 );
        add_action( 'login_footer', array( $this, 'login_footer_jquery' ), 19 );

        add_action( current_action(), array( $this, 'login_remove_scripts' ), 12 );
        add_action( 'login_head', array( $this, 'login_head' ) );
    }

    /**
     * WordPress filter hooks
     */
    private function add_filters() {

        add_filter( 'gettext', array( $this, 'remove_lostpassword_text' ), 20, 2 );
        add_filter( 'login_headerurl', array( $this, 'login_headerurl' ) );
        add_filter( 'login_headertitle', array( $this, 'login_headertitle' ) );
    }

    /**
     *************************************************************
     ****************  ACTIONS  **********************************
     *************************************************************
     */

    /**
     * Enqueue additional scripts.
     *
     * @since 2.0
     * @updated 3.2
     */
    function login_enqueue_scripts() {

        wp_enqueue_script( 'prefixfree', 'https://raw.githubusercontent.com/LeaVerou/prefixfree/gh-pages/prefixfree.min.js',
            array(), '1.0.7', false );

        /**
         * Animate.css
         *
         * @link https://github.com/daneden/animate.css/blob/master/animate.min.css
         */
        if ( 'on' === CL_Common::get_option( 'animate.css', 'design', 'off' ) ) {
            wp_enqueue_style( 'animate', plugins_url( 'css/animate.min.css', CUSTOM_LOGIN_FILE ),
                array( 'login' ), '3.5.1', 'screen' );
        }

        /* Custom jQuery */
        if ( '' !== CL_Common::get_option( 'custom_jquery', 'design', '' ) ) {
            wp_enqueue_script( 'jquery' );
        }
    }

    /**
     * If there is custom HTML set in the settings echo it to the
     * 'login_footer' hook in wp-login.php.
     *
     * @return string|void
     */
    public function login_footer_html() {

        if ( false !== ( $custom_html = CL_Common::get_option( 'custom_html', 'design', false ) ) ) {
            $html = wp_kses_post( $custom_html );
            echo "{$html}\n";
        }
    }

    /**
     * Database access to the scripts and styles.
     *
     * @since 2.1
     * @return  string|void
     */
    public function login_footer_jquery() {

        if ( '' !== CL_Common::get_option( 'custom_jquery', 'design', '' ) ) {

            self::$js_atts = array(
                'version'   => CUSTOM_LOGIN_VERSION,
                'trans_key' => CL_Common::get_transient_key( 'script' ),
            );
            self::$js_atts = wp_parse_args( CL_Common::get_options( 'design' ), self::$js_atts );

            foreach ( self::$js_atts as $atts => $value ) {
                if ( 'custom_jquery' !== $atts && 'version' !== $atts && 'trans_key' !== $atts ) {
                    unset( self::$js_atts[ $atts ] );
                }
            }

            echo "<script type='text/javascript'>\n";
            CL_Templates::get_template_part( 'wp-login', 'script' );
            echo "\n</script>\n";
        }
    }

    /**
     * Finds the global page for the wp-login.php. When on the page
     * remove default stylesheets so we can add our own.
     *
     * @param bool $is_login_page Is the current page the 'wp-login.php' page?
     */
    public function login_remove_scripts( $is_login_page ) {

        if ( $is_login_page ) {

            $suffix = is_rtl() ? '-rtl' : '';
            $suffix .= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'; // Don't have minified version in place.

            /**
             * User reports on messed up checkboxes
             *
             * Probably easier to use WordPress login CSS
             *
             * wp_deregister_style( array( 'login' ) );
             *
             * wp_enqueue_style( 'forms', get_admin_url( get_current_blog_id(), "css/forms{$suffix}.css", 'admin' ), null, CUSTOM_LOGIN_VERSION, 'screen' );
             * wp_enqueue_style( 'l10n', get_admin_url( get_current_blog_id(), "css/l10n{$suffix}.css", 'admin' ), null, CUSTOM_LOGIN_VERSION, 'screen' );
             * wp_register_style( 'login', plugins_url( "css/login/login{$suffix}.css", CUSTOM_LOGIN_FILE ), array( 'buttons' ), CUSTOM_LOGIN_VERSION, 'all' );
             */

            if ( 'on' === CL_Common::get_option( 'remove_login_css', 'general', 'off' ) ) {
                add_filter( 'wp_admin_css', '__return_false' );
                wp_deregister_style( array( 'login' ) );
            }
        }
    }

    /**
     * Actions hooked into login_head
     */
    public function login_head() {

        self::$css_atts = array(
            'version'   => CUSTOM_LOGIN_VERSION,
            'trans_key' => CL_Common::get_transient_key( 'style' ),
        );
        self::$css_atts = wp_parse_args( CL_Common::get_options( 'design' ), self::$css_atts );

        echo "<style type=\"text/css\">\n";
        CL_Templates::get_template_part( 'wp-login', 'style' );
        echo "\n</style>\n";

        if ( 'on' === CL_Common::get_option( 'wp_shake_js', 'general' ) ) {
            remove_action( 'login_head', 'wp_shake_js', 12 );
        }
    }

    /**
     *************************************************************
     ****************  FILTERS  **********************************
     *************************************************************
     */

    /**
     * Remove the "Lost your password?" text.
     *
     * @param $translated_text
     * @param $untranslated_text
     *
     * @return string
     */
    public function remove_lostpassword_text( $translated_text, $untranslated_text ) {

        if ( CL_Common::is_wp_login_php() ) {

            if ( 'on' === CL_Common::get_option( 'lostpassword_text', 'general', 'off' ) ) {
                // Make the changes to the text
                switch ( $untranslated_text ) {

                    case 'Lost your password?':
                        $translated_text = '';
                        break;
                }
            }
        }

        return $translated_text;
    }

    /**
     * Replace the default link to your URL
     *
     * @param string $url
     *
     * @return string
     */
    public function login_headerurl( $url ) {

        if ( ! is_multisite() ) {
            $url = esc_url( home_url() );
        }

        return $url;
    }

    /**
     * Replace the default title to your description
     *
     * @param string $title
     *
     * @return string
     */
    public function login_headertitle( $title ) {

        if ( ! is_multisite() ) {
            $title = esc_attr( get_bloginfo( 'description' ) );
        }

        return $title;
    }
}
