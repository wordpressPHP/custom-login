<?php

namespace PassyCo\CustomLogin;

/**
 * Class WpLogin
 *
 * @package PassyCo\CustomLogin
 */
class WpLogin extends AbstractLogin implements WpHooksInterface {

    use HooksTrait;

    /** @var array $css_atts */
    public static $css_atts = [];

    /** @var array $js_atts */
    public static $js_atts = [];

    /**
     * Add class hooks.
     */
    public function addHooks() {
        if ( ! $this->isCustomLoginActive() || $this->isIframeRequest() ) {
//            return;
        }

        $this->addAction( 'login_enqueue_scripts', [ $this, 'loginScripts' ] );
        $this->addAction( 'login_footer', [ $this, 'loginFooterHtml' ], 8 );
        $this->addAction( 'login_footer', [ $this, 'loginFooterJquery' ], 19 );
        $this->addAction( current_action(), [ $this, 'loginRemoveScripts' ], 12 );
        $this->addAction( 'login_head', [ $this, 'loginHead' ] );

        $this->addFilter( 'gettext', [ $this, 'removeLostPasswordText' ], 20, 2 );
        $this->addFilter( 'login_headerurl', [ $this, 'loginHeaderUrl' ] );
        $this->addFilter( 'login_headertitle', [ $this, 'loginHeaderTitle' ] );
    }

    /**
     * Enqueue additional scripts.
     *
     * @since 2.0
     * @updated 3.2
     */
    protected function loginScripts() {
        wp_enqueue_script(
            'prefixfree',
            'https://raw.githubusercontent.com/LeaVerou/prefixfree/gh-pages/prefixfree.min.js',
            [],
            '1.0.7',
            false
        );

        /**
         * Animate.css
         *
         * @link https://github.com/daneden/animate.css/blob/master/animate.min.css
         */
        if ( Common::getOption( 'animate.css', 'design', 'off' ) === Common::ON ) {
            wp_enqueue_style(
                'animate',
                $this->getCustomLogin()->getUrl() . '/assets/css/animate.min.css',
                [ 'login' ],
                '3.5.1',
                'screen'
            );
        }

        /* Custom jQuery */
        if ( Common::getOption( 'custom_jquery', 'design', '' ) !== '' ) {
            wp_enqueue_script( 'jquery' );
        }
        wp_enqueue_script( 'jquery' );
    }

    /**
     * If there is custom HTML set in the settings echo it to the
     * 'login_footer' hook in wp-login.php.
     *
     * @return string|void
     */
    protected function loginFooterHtml() {
        if ( ( $custom_html = Common::getOption( 'custom_html', 'design', false ) !== false ) ) {
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
    protected function loginFooterJquery() {
        ?>
        <script type="text/javascript">
          (function ($, undefined) {
            var getStyle = new Promise(function (resolve, reject) {
              $.ajax({
                url: 'http://wp.dev/wp-json/custom-login/v1/style',
                method: 'GET',
                success: function (res) {
                  console.log(res);
                  if (res.css.length) {
                    resolve(res);
                  } else {
                    reject(Error("No Posts"));
                  }
                }
              });
            });

            getStyle.then(function (res) {
              $('head').append(res.css);
            });
          }(jQuery));
        </script>
        <?php
        if ( Common::getOption( 'custom_jquery', 'design', '' ) !== '' ) {

            self::$js_atts = [
                'version' => CUSTOM_LOGIN_VERSION,
                'trans_key' => Common::getTransientKey( 'script' ),
            ];
            self::$js_atts = wp_parse_args( Common::getOptions( 'design' ), self::$js_atts );

            foreach ( self::$js_atts as $atts => $value ) {
                if ( 'custom_jquery' !== $atts && $atts !== 'version' && $atts !== 'trans_key' ) {
                    unset( self::$js_atts[ $atts ] );
                }
            }

            echo "<script type='text/javascript'>\n";
            Templates::getTemplatePart( 'wp-login', 'script' );
            echo "\n</script>\n";
        }
    }

    /**
     * Finds the global page for the wp-login.php. When on the page
     * remove default stylesheets so we can add our own.
     *
     * @param bool $is_login_page Is the current page the 'wp-login.php' page?
     */
    protected function loginRemoveScripts( $is_login_page ) {
        if ( $is_login_page ) {

            $suffix = is_rtl() ? '-rtl' : '';
            $suffix .= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' :
                '.min'; // Don't have minified version in place.

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

            if ( Common::getOption( 'remove_login_css', 'general', 'off' ) === Common::ON ) {
                $this->addFilter( 'wp_admin_css', '__return_false' );
                wp_deregister_style( [ 'login' ] );
            }
        }
    }

    /**
     * Actions hooked into login_head
     */
    protected function loginHead() {
        self::$css_atts = [
            'version' => $this->getCustomLogin()->getVersion(),
            'trans_key' => Common::getTransientKey( 'style' ),
        ];
        self::$css_atts = wp_parse_args( Common::getOptions( 'design' ), self::$css_atts );

        echo "<style type=\"text/css\">\n";
        Templates::getTemplatePart( 'wp-login', 'style' );
        echo "\n</style>\n";

        if ( Common::getOption( 'wp_shake_js', 'general' ) === Common::ON ) {
            remove_action( 'login_head', 'wp_shake_js', 12 );
        }
    }

    /**
     * Remove the "Lost your password?" text.
     *
     * @param $translated_text
     * @param $untranslated_text
     *
     * @return string
     */
    protected function removeLostPasswordText( $translated_text, $untranslated_text ) {
        if ( Common::isWpLoginDotPhp() ) {

            if ( Common::getOption( 'lostpassword_text', 'general', 'off' ) === Common::ON ) {
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
    protected function loginHeaderUrl( $url ) {
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
    protected function loginHeaderTitle( $title ) {
        if ( ! is_multisite() ) {
            $title = esc_attr( get_bloginfo( 'description' ) );
        }

        return $title;
    }

    /**
     * Is Custom Login active (on).
     *
     * @return bool
     */
    private function isCustomLoginActive() {
        return Common::getOption( 'active', 'general', 'off' ) === Common::ON;
    }

    /**
     * Is the current request in an iFrame?
     *
     * @return bool
     */
    private function isIframeRequest() {
        return defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST;
    }
}
