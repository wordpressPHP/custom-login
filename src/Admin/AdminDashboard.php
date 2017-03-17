<?php

namespace PassyCo\CustomLogin\Admin;

use PassyCo\CustomLogin\AbstractLogin;
use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\HooksTrait;
use PassyCo\CustomLogin\WpHooksInterface;

/**
 * Class AdminDashboard
 *
 * @package PassyCo\CustomLogin\Admin
 */
class AdminDashboard extends AbstractLogin implements WpHooksInterface {

    use HooksTrait;

    const ID = 'custom-login-admin-dashboard';

    /**
     * @var string
     */
    private $feed_url = 'https://frosty.media/feed/';

    /**
     * Add class hooks.
     */
    public function addHooks() {
        $this->addAction( 'load-index.php', [ $this, 'loadIndexPhp' ] );
    }

    /**
     * Add Dashboard widget
     */
    protected function addDashboardWidget() {
        if ( ! $this->isDashboardAllowed() ) {
            return;
        }

        wp_add_dashboard_widget(
            self::ID,
            __( 'Frosty Media News & Add-on Plugins', CustomLogin::DOMAIN ),
            [ $this, 'dashboardWidgetCallback' ]
        );
    }

    /**
     * Dashboard scripts & styles
     */
    protected function enqueueScripts() {
        wp_enqueue_style(
            self::ID,
            $this->getCustomLogin()->getUrl() . '/assets/css/dashboard.css',
            [],
            $this->getCustomLogin()->getVersion(),
            'screen'
        );

        wp_enqueue_script(
            self::ID,
            $this->getCustomLogin()->getUrl() . '/assets/js/dashboard.js',
            [ 'jquery' ],
            $this->getCustomLogin()->getVersion(),
            true
        );

        $localize_array = [
            'is_active' => $this->isDashboardAllowed(),
            'feed_url' => esc_url( 'https://frosty.media' ),
            'site_title' => esc_html__( 'Frosty Media', CustomLogin::DOMAIN ),
            'feed_title' => esc_html__( 'Unknown Title', CustomLogin::DOMAIN ),
        ];

        if ( ! $this->isDashboardAllowed() ) {
            $rss_items = Common::getFeedItems( 1, $this->feed_url );

            if ( isset( $rss_items[0] ) && ( $item = $rss_items[0] ) &&
                 ( $item instanceof \SimplePie_Item )
            ) {
                $localize_array['feed_title'] = esc_html( $item->get_title() );
            }
        }

        wp_localize_script( self::ID, 'cl_admin_dashboard', $localize_array );
    }

    /**
     * Dashboard widget
     */
    public function dashboardWidgetCallback() {
        static $count;

        // 'post' feed.
        $rss_items = Common::getFeedItems( 1, $this->feed_url );

        $content = '<div class="rss-widget"><ul>';

        if ( ! $rss_items ) {
            $content .= '<li>' . __( 'Error fetching feed', CustomLogin::DOMAIN ) . '</li>';
        } else {
            foreach ( $rss_items as $key => $item ) {

                if ( ! ( $item instanceof \SimplePie_Item ) ) {
                    continue;
                }

                $count ++;
                $feed_url = esc_url( $item->get_permalink() );
                $content .= '<li>';
                $content .= '<a class="rsswidget" href="' . esc_url( add_query_arg( [
                        'utm_medium' => 'wpadmin_dashboard',
                        'utm_term' => 'newsitem',
                        'utm_campaign' => CustomLogin::DOMAIN,
                    ], $feed_url ) ) . '">' . esc_html( $item->get_title() ) . '</a>';

                if ( 1 === $count ) {
                    $content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' . $item->get_date( get_option( 'date_format' ) ) . '</span>';
                    $content .= '<div class="rssSummary">' . strip_tags( wp_trim_words( $item->get_description(), 28 ) ) . '</div>';
                }
                $content .= '</li>';
            }
        }
        $content .= '</ul></div>';

        // 'plugin' feed.
        $rss_items = Common::getFeedItems( 3, add_query_arg( [
            'post_type' => 'plugin',
            'plugin_tag' => 'custom-login-extension',
        ], $this->feed_url ) );

        $content .= '<div class="rss-widget"><ul>';

        if ( ! $rss_items ) {
            $content .= '<li>' . __( 'Error fetching feed', CustomLogin::DOMAIN ) . '</li>';
        } else {

            $extension = _x( 'Custom Login Add-on Plugins', 'A plugin that adds onto the Custom Login functions.', CustomLogin::DOMAIN );
            $content .= '<li>';
            $content .= sprintf( '<strong>%s</strong> ', $extension );
            $content .= '<span class="dashicons dashicons-editor-help" data-toggle=".cl-extensions-desc"></span></li>';
            $content .= '<li>' . Common::getExtensionDescription() . '</li>';

            foreach ( $rss_items as $item ) {

                if ( ! ( $item instanceof \SimplePie_Item ) ) {
                    continue;
                }

                $url = esc_url( $item->get_permalink() );
                $content .= '<li>';
                $content .= '<a class="" href="' . esc_url( add_query_arg( [
                        'utm_medium' => 'wpadmin_dashboard',
                        'utm_term' => 'newsitem',
                        'utm_campaign' => CustomLogin::DOMAIN,
                    ], $url ) ) . '">' . esc_html( $item->get_title() ) . '</a>';
                $content .= '</li>';
            }
        }
        $content .= '</ul></div>';

        $content .= '<div class="rss-widget">';
        $content .= '<ul class="social">';
        $content .= '<li>';
        $content .= '<a href="https://www.facebook.com/FrostyMediaWP"><span class="dashicons dashicons-facebook"></span>/FrostyMediaWP</a> | ';
        $content .= '<a href="https://twitter.com/Frosty_Media"><span class="dashicons dashicons-twitter"></span>/Frosty_Media</a> | ';
        $content .= '<a href="https://twitter.com/TheFrosty"><span class="dashicons dashicons-twitter"></span>/TheFrosty</a>';
        $content .= '</li>';
        $content .= '</ul>';
        $content .= '</div>';

        echo $content;
    }

    /**
     * Check if the dashboard widget is allowed.
     *
     * @return bool
     */
    private function isDashboardAllowed() {
        return Common::getOption( 'dashboard_widget', 'general', 'off' ) === Common::ON;
    }

    /**
     * Load additional hooks for this class.
     */
    private function loadIndexPhp() {
        $this->addAction( 'wp_dashboard_setup', [ $this, 'addDashboardWidget' ] );
        $this->addAction( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }
}
