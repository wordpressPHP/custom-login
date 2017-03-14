<?php

namespace PassyCo\CustomLogin;

use PassyCo\CustomLogin\Api\SettingsApi;

/**
 * Class Extensions
 *
 * @package PassyCo\CustomLogin
 */
class Extensions implements WpHooksInterface {

    use HooksTrait;

    /**
     * @var string
     */
    var $api_url;

    /**
     * @var string
     */
    private $setting_id;

    /**
     * @var string
     */
    private $page_id;

    /**
     * @var string
     */
    static $menu_page;

    /**
     * @var array
     */
    var $extensions = [];

    /**
     * Add class hooks.
     */
    public function addHooks() {
        $this->setup_variables();
        $this->instantiate_remote_install();
        $this->add_actions();
        $this->get_extensions();
    }

    /**
     * Setup class variables.
     */
    private function setup_variables() {
        $this->api_url    = defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ?
            'http://frostymedia.dev/edd-sl-api/' : 'https://frosty.media/edd-sl-api/';
        $this->setting_id = SettingsApi::SETTING_ID;
        $this->page_id    = 'add-ons';
    }

    /**
     * Setup Remote Installer class.
     */
    private function instantiate_remote_install() {
        $remote_install = new \Frosty_Media_Remote_Install_Client();
        $remote_install->setup(
            $this->api_url,
            [
                'skipplugincheck' => false,
                'menu_page' => self::$menu_page,
                'url' => home_url(),
            ]
        );
        $remote_install->add_hooks();
    }

    /**
     * Add class actions.
     */
    private function add_actions() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ], 10 );
        add_action(
            $this->setting_id . '_settings_sidebars',
            [ $this, 'sidebar_add_on_plugins' ],
            20
        );
    }

    /**
     * Sidebar box about the plugin author.
     *
     * @param array $args Sidebar args
     */
    public function sidebar_add_on_plugins( array $args ) {
        Common::renderView( 'admin/sidebar-add-on-plugins', null, $args );
    }

    /**
     *
     */
    public function admin_menu() {
        $page_uri = sprintf( '%s-%s', CustomLogin::DOMAIN, $this->page_id );

        self::$menu_page = add_options_page(
            __( 'Custom Login Add-on Plugins', CustomLogin::DOMAIN ),
            __( 'Add-on Plugins', CustomLogin::DOMAIN ),
            'install_plugins',
            $page_uri,
            [ $this, 'settings_page' ]
        );

//		remove_submenu_page( 'options-general.php', $page_uri );
        add_action( 'load-' . self::$menu_page, [ $this, 'load' ] );
        add_action( 'load-' . self::$menu_page, [ $this, 'remote_install_client' ], 10 );
    }

    public function settings_page() {
        Common::renderView( 'admin/settings-add-ons', $this );
    }

    /**
     * Load hooks only on our specific page.
     */
    public function load() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
    }

    /**
     *
     */
    public function enqueue_scripts() {
        wp_enqueue_style( CustomLogin::DOMAIN );
        wp_enqueue_script( [ 'updates', 'plugin-install' ] );
    }

    /**
     * Load the remote installer on our setting page only.
     *
     * @update 3.1
     */
    public function remote_install_client() {
    }

    /**
     *
     */
    private function get_extensions() {
        // Save this for a later date since our checkin for our HTML data doesn't get called on init...
        $ext_url       = false; //add_query_arg( array( 'edd_action' => 'cl_announcements' ), trailingslashit( CUSTOM_LOGIN_API_URL ) . 'cl-checkin-api/' );
        $transient_key = Common::getTransientKey( 'extensions' );
        $extensions    = Common::wpRemoteGet( $ext_url, $transient_key, WEEK_IN_SECONDS, 'CustomLogin' );

        if ( false !== $extensions && is_object( $extensions ) ) {
            $this->extensions[] = $extensions->html;
        } else {
            /* Stealth Login */
            $this->extensions[] = [
                'title' => 'Custom Login Stealth Login',
                'description' => 'Protect your wp-login.php page from brute force attacks.',
                'url' => 'https://frosty.media/plugins/custom-login-stealth-login/',
                'image' => 'https://i.imgur.com/mhuymPG.jpg',
                'basename' => 'custom-login-stealth-login/custom-login-stealth-login.php',
                'links' => [
                    [
                        'description' => 'Personal',
                        'download_id' => '108',
                        'price_id' => '1',
                        'price' => '$35',
                    ],
                    [
                        'description' => 'Plus',
                        'download_id' => '108',
                        'price_id' => '2',
                        'price' => '$95',
                    ],
                    [
                        'description' => 'Professional',
                        'download_id' => '108',
                        'price_id' => '3',
                        'price' => '$195',
                    ],
                ],
            ];

            /* Page Template */
            $this->extensions[] = [
                'title' => 'Custom Login Page Template',
                'description' => 'Add a login form to any WordPress page.',
                'url' => 'https://frosty.media/plugins/custom-login-page-template/',
                'image' => 'https://i.imgur.com/A0rzS9q.jpg',
                'basename' => 'custom-login-page-template/custom-login-page-template.php',
                'links' => [
                    [
                        'description' => 'Personal',
                        'download_id' => '120',
                        'price_id' => '1',
                        'price' => '$35',
                    ],
                    [
                        'description' => 'Plus',
                        'download_id' => '120',
                        'price_id' => '2',
                        'price' => '$95',
                    ],
                    [
                        'description' => 'Professional',
                        'download_id' => '120',
                        'price_id' => '3',
                        'price' => '$195',
                    ],
                ],
            ];

            /* Login Redirects */
            $this->extensions[] = [
                'title' => 'Custom Login Redirects',
                'description' => 'Manage redirects after logging in.',
                'url' => 'https://extendd.com/plugin/wordpress-login-redirects/',
                'image' => 'https://i.imgur.com/aNGoyAa.jpg',
                'basename' => 'custom-login-redirects/custom-login-redirects.php',
                'links' => [
                    [
                        'description' => 'Personal',
                        'download_id' => '124',
                        'price_id' => '1',
                        'price' => '$35',
                    ],
                    [
                        'description' => 'Plus',
                        'download_id' => '124',
                        'price_id' => '2',
                        'price' => '$95',
                    ],
                    [
                        'description' => 'Professional',
                        'download_id' => '124',
                        'price_id' => '3',
                        'price' => '$195',
                    ],
                ],
            ];

            /* No Password */
            $this->extensions[] = [
                'title' => 'Custom Login No Password',
                'description' => 'Allow users to login without a password.',
                'url' => 'https://frosty.media/plugins/custom-login-no-passowrd-login/',
                'image' => 'https://i.imgur.com/7SXIpi5.jpg',
                'basename' => 'custom-login-no-password/custom-login-no-password.php',
                'links' => [
                    [
                        'description' => 'Personal',
                        'download_id' => '128',
                        'price_id' => '1',
                        'price' => '$35',
                    ],
                    [
                        'description' => 'Plus',
                        'download_id' => '128',
                        'price_id' => '2',
                        'price' => '$95',
                    ],
                    [
                        'description' => 'Professional',
                        'download_id' => '128',
                        'price_id' => '3',
                        'price' => '$195',
                    ],
                ],
            ];
        }
    }
}
