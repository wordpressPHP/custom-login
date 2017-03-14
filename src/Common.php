<?php

namespace PassyCo\CustomLogin;

use PassyCo\CustomLogin\Api\SettingsApi;

/**
 * Class Common
 *
 * @package PassyCo\CustomLogin
 */
class Common extends AbstractStaticLogin {

    const ON = 'on';

    /**
     * Get the value of a settings field
     *
     * @param string $option settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     *
     * @return mixed An empty string if no default is set, otherwise the value of the option.
     */
    public static function getOption( $option, $section = '', $default = '' ) {
        $section = self::sanitizeSectionId( $section );
        $setting = get_option( SettingsApi::SETTING_ID, [] );

        if ( ! empty( $setting[ $section ] ) && ! empty( $setting[ $section ][ $option ] ) ) {
            return $setting[ $section ][ $option ];
        }

        return $default;
    }

    /**
     * Get all values of a settings section
     *
     * @param string $section the section name this field belongs to
     *
     * @return array
     */
    public static function getOptions( $section ) {
        $section = self::sanitizeSectionId( $section );
        $setting = get_option( SettingsApi::SETTING_ID, [] );

        if ( ! empty( $setting[ $section ] ) ) {
            return $setting[ $section ];
        }

        return [];
    }

    /**
     * Is Custom Login using the new customizer design settings?
     *
     * @return bool
     */
    public static function usingCustomizer() {
        return self::getOption( 'use_customizer', 'general' ) === self::ON;
    }

    /**
     * Render a template view.
     *
     * @param string $view_path The path + filename. Omit the `php` extension.
     * @param null|object $object Optional object to pass to the view.
     * @param null|string|array $args Optional array to pass to the view.
     */
    public static function renderView( $view_path, $object = null, $args = null ) {
        include self::getDir() . "views/{$view_path}.php";
    }

    /**
     * Fetch RSS items from the feed.
     *
     * @param int $num Number of items to fetch.
     * @param string $feed The feed to fetch.
     *
     * @return array|bool False on error, array (SimplePie_Item) of RSS items on success.
     */
    public static function getFeedItems( $num, $feed ) {
        if ( ! function_exists( 'fetch_feed' ) ) {
            include_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'feed.php';
        }

        $rss = fetch_feed( $feed );

        // Bail if feed doesn't work
        if ( ! ( $rss instanceof \SimplePie ) || is_wp_error( $rss ) ) {
            return false;
        }

        $rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );

        // If the feed was erroneous
        if ( ! $rss_items ) {
            $md5 = md5( $feed );
            delete_transient( 'feed_' . $md5 );
            delete_transient( 'feed_mod_' . $md5 );
            $rss       = fetch_feed( $feed );
            $rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
        }

        return $rss_items;
    }

    /**
     * Helper function to return the data URI.
     *
     * @param string $_image
     * @param string $mime
     *
     * @return string
     */
    public static function getDataUri( $_image, $mime = '' ) {
        $image = self::getUrl() . $_image;
        $data  = file_exists( $image ) ?
            base64_encode(
                wp_remote_retrieve_body(
                    wp_remote_get(
                        esc_url_raw( $image )
                    )
                )
            ) : '';

        return ! empty( $data ) ? 'data:image/' . $mime . ';base64,' . $data : '';
    }

    /**
     * Get's the cached transient key.
     *
     * @param string $input
     *
     * @return string
     */
    public static function getTransientKey( $input ) {
        $len = is_multisite() ? 40 : 45;
        $key = 'custom_login_' . $input . '_';
        $key = $key . substr( md5( $input ), 0, $len - strlen( $key ) );

        return $key;
    }

    /**
     * Helper function to make remote calls
     *
     * @since 3.0.0
     * @updated 3.0.8
     *
     * @param bool|string $url
     * @param string $transient_key
     * @param int $expiration
     * @param string $user_agent
     *
     * @return mixed
     */
    public static function wpRemoteGet( $url = false, $transient_key, $expiration = 0, $user_agent = 'WordPress' ) {
        if ( ! $url ) {
            return false;
        }

        if ( 'wordpress' === strtolower( $user_agent ) ) {
            $_version = $GLOBALS['wp_version'];
        } else {
            $_version = CUSTOM_LOGIN_VERSION;
        }

        $expiration = 0 !== $expiration ? abs( $expiration ) : WEEK_IN_SECONDS;

        if ( false === ( $json = get_transient( $transient_key ) ) ) {
            $response = wp_remote_get(
                esc_url_raw( $url ),
                [
                    'timeout' => apply_filters( 'cl_wp_remote_get_timeout', (int) 15 ),
                    'sslverify' => false,
                    'user-agent' => $user_agent . '/' . $_version . '; ' . get_bloginfo( 'url' ),
                ]
            );

            if ( ! is_wp_error( $response ) ) {
                if ( isset( $response['body'] ) && strlen( $response['body'] ) > 0 ) {
                    $json = json_decode( wp_remote_retrieve_body( $response ) );

                    // Discount, double check?
                    if ( is_wp_error( $json ) ) {
                        return false;
                    }

                    // Cache the results for '$expiration'
                    set_transient( $transient_key, $json, $expiration );

                    // Return the data
                    return $json;
                }
            } else {
                delete_transient( $transient_key );
            }
        }

        return $json;
    }

    /**
     * Helper function check if we're on our settings page.
     *
     * @since 3.0.9
     *
     * @return bool
     */
    public static function isSettingsPage() {
        $return = false;
        $screen = get_current_screen();

        if ( null !== $screen ) {
            if ( $screen->id === SettingsApi::$menu_page ) {
                $return = true;
            }
        } else {
            if ( 'options-general.php' === $GLOBALS['pagenow'] &&
                 ( isset( $_GET['page'] ) && CustomLogin::DOMAIN === $_GET['page'] )
            ) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Is the current page the WordPress login page: `wp-login.php`?
     *
     * @param \WP_Customize_Panel|null $wp_customize_panel Optional parameter holding the
     *      WP_Customize_Panel object during the `customize_register` hook callback for add_panel().
     *
     * @return bool
     */
    public static function isWpLoginDotPhp( \WP_Customize_Panel $wp_customize_panel = null ) {
        global $pagenow;

        /**
         * When inside the customizer, we need to check
         * two global variable definitions.
         */
        if ( ! is_null( $wp_customize_panel ) && $wp_customize_panel instanceof \WP_Customize_Panel ) {
            return 'customize.php' === $pagenow || 'wp-login.php' === $pagenow;
        }

        return 'wp-login.php' === $pagenow;
    }

    /**
     * Return all editable role capabilities.
     *
     * @return array
     */
    public static function getEditableRoles() {
        $roles = [];

        // get_editable_roles()
        $editable_roles = apply_filters( 'editable_roles', wp_roles()->roles );

        if ( empty( $editable_roles ) ) {
            return $roles;
        }

        foreach ( $editable_roles as $role_name => $role ) {
            // https://wordpress.org/support/topic/invalid-argument-supplied-for-foreach-error-line-in-wp-dashboard?replies=2#post-6427631
            if ( ! is_array( $role['capabilities'] ) ) {
                continue;
            }

            foreach ( $role['capabilities'] as $capability => $caps_array ) {
                // Remove the (deprecated) capabilities from the array
                if ( preg_match( '/^level_/', $capability ) ) {
                    continue;
                }
                $roles[ $capability ] = $capability;
            }
        }

        return $roles;
    }

    /**
     * Gets the colors in the current WordPress admin color scheme.
     *
     * @uses get_user_meta
     * @uses get_current_user_id
     * @link https://gist.github.com/JeffMatson/86b44ec68bbc4ce80e6e
     *
     * @return array
     */
    public static function getAdminColors() {
        global $_wp_admin_css_colors;

        $current_color_scheme = get_user_meta( get_current_user_id(), 'admin_color', true );

        $colors = array_merge(
            $_wp_admin_css_colors[ $current_color_scheme ]->colors,
            $_wp_admin_css_colors[ $current_color_scheme ]->icon_colors
        );

        return $colors;
    }

    /**
     * @return string
     */
    public static function getExtensionDescription() {
        return sprintf( '<span class="description cl-extensions-desc">%s</span>',
            _x( 'A Custom Login Add-on <strong>is</strong> a WordPress plugin. It\'s called an "add-on plugin" because it will not work without Custom Login installed.',
                'Defining what a Custom Login Extension/Add-on Plugin is.', CustomLogin::DOMAIN )
        );
    }

    /**
     * Remove the 'custom_login_settings' from the section id.
     *
     * @param string $section_id
     *
     * @return string
     */
    private static function sanitizeSectionId( $section_id ) {
        return str_replace( [ SettingsApi::SETTING_ID, '[', ']' ], '', $section_id );
    }
}
