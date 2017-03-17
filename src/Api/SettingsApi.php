<?php

namespace PassyCo\CustomLogin\Api;

use PassyCo\CustomLogin\AbstractLogin;
use PassyCo\CustomLogin\Admin\AdminNotices;
use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\HooksTrait;
use PassyCo\CustomLogin\WpHooksInterface;

/**
 * Class SettingsApi
 *
 * @package PassyCo\CustomLogin\Api
 */
class SettingsApi extends AbstractLogin implements WpHooksInterface {

    use HooksTrait;

    const VERSION = '3.2.0';
    const SETTING_ID = CustomLogin::ID . '_settings';

    /**
     * @var string $menu_page
     */
    public static $menu_page;

    /**
     * @var array $settings_sections
     */
    protected $settings_sections = [];

    /**
     * @var array $settings_fields
     */
    protected $settings_fields = [];

    /**
     * @var array $settings_sidebars
     */
    protected $settings_sidebars = [];

    /**
     * @var array $settings_scripts
     */
    private $settings_scripts = [];

    /**
     * @var array $l_10_n
     */
    private $l_10_n = [];

    /**
     * Add class hooks.
     */
    public function addHooks() {
        $this->addAction( 'admin_menu', [ $this, 'adminMenu' ] );
        $this->addAction( 'admin_enqueue_scripts', [ $this, 'registerAdminScripts' ] );
        $this->addAction( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ], 11 );
    }

    /**
     * Register the plugin settings page and subsequent hooks.
     */
    public function adminMenu() {
        self::$menu_page = add_options_page(
            __( 'Custom Login Settings', 'custom-login' ),
            __( 'Custom Login', 'custom-login' ),
            Common::getOption( 'capability', 'general', 'manage_options' ),
            CustomLogin::DOMAIN,
            function() {
                Common::renderView( 'admin/settings-api', $this );
            }
        );

        $this->addAction(
            'load-' . self::$menu_page,
            [ $this, 'loadCustomLoginMenuPage' ],
            89
        );
        $this->addAction(
            CustomLogin::DOMAIN . '_admin_enqueue_scripts',
            [ $this, 'adminPrintFooterScripts' ],
            99
        );
    }

    /**
     * Action hooks called on the menu page hooks.
     *      This method is only called on the `load-$action` action
     */
    protected function loadCustomLoginMenuPage() {
        $this->addAction(
            'admin_notices',
            [ $this, 'maybeAddNotice' ]
        );
        $this->addAction(
            self::SETTING_ID . '_sticky_admin_notice',
            [ $this, 'addStickySocialLinks' ],
            10
        );
        $this->addAction(
            self::SETTING_ID . '_settings_sidebars',
            [ $this, 'addSidebarAuthor' ],
            19
        );
        $this->addAction(
            self::SETTING_ID . '_settings_sidebars',
            [ $this, 'addSidebarFeed' ],
            20
        );
    }

    /**
     * Register admin scripts and styles
     */
    protected function registerAdminScripts() {
        wp_register_script( CustomLogin::DOMAIN,
            $this->getCustomLogin()->getUrl() . '/assets/js/admin.js',
            [ 'jquery' ],
            $this->getCustomLogin()->getVersion(),
            true
        );

        wp_register_script( 'wp-color-picker-alpha',
            $this->getCustomLogin()->getUrl() . '/assets/js/wp-color-picker-alpha.js',
            [ 'wp-color-picker' ],
            '1.2.1',
            true
        );

        wp_register_script( 'codemirror',
            $this->getCustomLogin()->getUrl() . '/assets/js/codemirror.js',
            [],
            $this->getCustomLogin()->getVersion(),
            true
        );

        wp_register_style( CustomLogin::DOMAIN,
            $this->getCustomLogin()->getUrl() . '/assets/css/admin.css',
            false,
            $this->getCustomLogin()->getVersion(),
            'screen'
        );

        wp_register_style( 'bulma-framework',
            $this->getCustomLogin()->getUrl() . '/assets/css/bulma.css',
            false,
            '0.0.12',
            'screen'
        );

        wp_register_style( 'codemirror',
            $this->getCustomLogin()->getUrl() . '/assets/css/codemirror.css',
            false,
            '5.12.0',
            'screen'
        );
    }

    /**
     * Enqueue admin scripts and styles when on the Custom Login settings page.
     *
     * @param string $hook
     */
    protected function adminEnqueueScripts( $hook ) {
        if ( $this->getSettingsPageHook() !== $hook ) {
            return;
        }

        /* Core */
        wp_enqueue_media();
        wp_enqueue_script( [ 'plugin-install' ] );
        wp_enqueue_style( [ 'wp-color-picker', 'thickbox', 'plugin-install' ] );

        /* jQuery Sticky */
        wp_enqueue_script( 'sticky',
            $this->getCustomLogin()->getUrl() . '/assets/js/jquery.sticky.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );

        wp_enqueue_style( [ 'dashicons', CustomLogin::DOMAIN, 'codemirror', 'bulma-framework' ] );
        wp_enqueue_script( [ CustomLogin::DOMAIN, 'codemirror' ] );

        /**
         * @param CustomLogin $this ->getCustomLogin()
         */
        do_action( CustomLogin::DOMAIN . '_admin_enqueue_scripts', $this->getCustomLogin() );
    }

    /**
     * This method is called in the custom action 'custom-login_admin_enqueue_scripts' and insures
     * that these action hooks to 'admin_print_footer_scripts' are only called on our setting
     * spage.
     */
    protected function adminPrintFooterScripts() {
        $this->addAction(
            'admin_print_footer_scripts',
            [ $this, 'enqueueFieldTypeScripts' ],
            89
        );
        $this->addAction(
            'admin_print_footer_scripts',
            [ $this, 'wpLocalizeScript' ],
            99
        );
    }

    /**
     * Enqueue field type dependant scripts if they are registered.
     *
     * @uses wp_script_is
     * @uses wp_print_scripts Since this method is called in 'admin_print_footer_scripts' we have
     *     to print the script and not enqueue it.
     */
    protected function enqueueFieldTypeScripts() {
        if ( ! empty( $this->settings_scripts ) ) {
            foreach ( array_unique( $this->settings_scripts ) as $script ) {

                if ( wp_style_is( $script, 'registered' ) ) {
                    wp_print_styles( $script );
                }

                if ( wp_script_is( $script, 'registered' ) ) {
                    wp_print_scripts( $script );
                }
            }
        }
    }

    /**
     * Localize our script array.
     *
     * @uses wp_localize_script()
     */
    protected function wpLocalizeScript() {
        $this->l_10_n['prefix']  = self::SETTING_ID;
        $this->l_10_n['blog_id'] = get_current_blog_id();
        $this->l_10_n['nonce']   = wp_create_nonce(
            __CLASS__ . plugin_basename( $this->getCustomLogin()->getFile() )
        );

        wp_localize_script( CustomLogin::DOMAIN, 'cl_settings_api', $this->l_10_n );
    }

    /**
     * @return array
     */
    public function getSettingsSections() {
        return $this->settings_sections;
    }

    /**
     * Set settings sections
     *
     * @param array $sections setting sections array
     */
    public function setSections( array $sections ) {
        $sections = apply_filters( self::SETTING_ID . '_add_settings_sections', $sections );

        $this->settings_sections = $sections;
    }

    /**
     * Add a single section
     *
     * @param array $section
     */
    public function addSection( array $section ) {
        $this->settings_sections[] = $section;
    }

    /**
     * @return array
     */
    public function getSettingsFields() {
        return $this->settings_fields;
    }

    /**
     * Set settings fields
     *
     * @param array $field_args The settings field args array
     */
    public function setFields( array $field_args ) {
        $field_args = apply_filters( self::SETTING_ID . '_add_settings_fields', $field_args );

        $this->settings_fields = $field_args;
    }

    /**
     * Add a single field
     *
     * @param string $section
     * @param array $field_args
     */
    public function addField( $section, array $field_args ) {
        $defaults = [
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text',
        ];

        $args = wp_parse_args( $field_args, $defaults );

        $this->settings_fields[ $section ][] = $args;
    }

    /**
     * @return array
     */
    public function getSettingsSidebars() {
        return $this->settings_sidebars;
    }

    /**
     * Add a single sidebar section
     *
     * @param array $sidebar
     */
    public function addSidebar( $sidebar = [] ) {
        $sidebar = apply_filters( self::SETTING_ID . '_add_settings_sidebar', $sidebar );

        if ( ! empty( $sidebar ) ) {
            $this->settings_sidebars[] = $sidebar;
        }
    }

    /**
     * Add to the localize array variable.
     *
     * @param string $key The array key value
     * @param mixed $value The array value
     */
    public function add_localize_array( $key, $value ) {
        if ( isset( $this->l_10_n[ $key ] ) ) {
            $this->l_10_n[ $key ][] = $value;
        } else {
            $this->l_10_n[ $key ] = $value;
        }
    }

    /**
     * Add to the script array variable.
     *
     * @param mixed $value The array value
     */
    public function addScriptsArray( $value ) {
        if ( ! in_array( $value, $this->settings_scripts, true ) ) {
            $this->settings_scripts[] = $value;
        }
    }

    /**
     * Show navigation as lists
     *
     * Shows all the settings section labels as list items
     */
    public function renderNavigation() {
        Common::renderView( 'admin/settings-api-nav' );
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    public function renderForms() {
        Common::renderView( 'admin/settings-api-forms' );
    }

    /**
     * Create a postbox widget.
     *
     * @param string $id ID of the postbox.
     * @param string $title Title of the postbox.
     * @param string $content Content of the postbox.
     * @param string $group The class group
     */
    public function createPostbox( $id, $title, $content, $group = '' ) {
        $args = implode( '|', [ $id, $title, $content, $group ] );
        Common::renderView( 'admin/create-postbox', null, $args );
    }

    /**
     * Create social links to show in the sticky admin bar.
     */
    protected function addStickySocialLinks() {
        Common::renderView( 'admin/sticky-social-links' );
    }

    /**
     * Sidebar box about the plugin author.
     *
     * @param array $args Sidebar args
     */
    protected function addSidebarAuthor( array $args ) {
        Common::renderView( 'admin/sidebar-author', $this, $args );
    }

    /**
     * Sidebar box with news feed links.
     *
     * @param array $args Sidebar args
     */
    protected function addSidebarFeed( array $args ) {
        Common::renderView( 'admin/sidebar-feed', $this, $args );
    }

    /**
     * Add an admin notice if there is an upgrade that needs the users attention.
     *
     * @throws \Exception
     */
    protected function maybeAddNotice() {
        try {
            if ( $this->hasUpgrade() ) {
                $message = sprintf(
                    esc_html__( 'Custom Login has detected old settings. If you wish to use them please run %sthis%s script before making any changes below.', CustomLogin::DOMAIN ),
                    '<a href="' . esc_url( admin_url( 'options.php?page=custom-login-upgrades' ) ) . '">',
                    '</a>'
                );
                throw new \Exception( sprintf( $message . '%s', AdminNotices::NOTICE_CODE ) );
            }
        } catch ( \Exception $e ) {
            AdminNotices::renderNotice( $e );
        }
    }

    /**
     * Display Upgrade Notices
     *
     * @return bool
     */
    private function hasUpgrade() {
        $has_upgrade = false;

        // Version > 2.0
        if ( get_option( 'custom_login', false ) !== false ) {
            $has_upgrade = true;
        }

        // Version > 3.0
        if ( get_option( 'custom_login_general', false ) !== false ||
             get_option( 'custom_login_design', false ) !== false
        ) {
            $has_upgrade = true;
        }

        return $has_upgrade;
    }

    /**
     * Gets the name of the hook when on the Custom Login settings page.
     *
     * @return string
     */
    private function getSettingsPageHook() {
        return 'settings_page_' . CustomLogin::DOMAIN;
    }
}
