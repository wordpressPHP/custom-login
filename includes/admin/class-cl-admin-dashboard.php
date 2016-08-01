<?php

use CL_Interface_WordPress_Hooks as WordPress_Hooks;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Admin_Dashboard
 */
class CL_Admin_Dashboard implements WordPress_Hooks {

	/**
	 * @var string
	 */
	private $id = 'cl-admin-dashboard';

	/**
	 * @var string
	 */
	private $feed_url = 'https://frosty.media/feed/';

	/**
	 * Add class hooks.
	 */
	public function add_hooks() {

		add_action( 'load-index.php', function() {
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		} );
	}

	/**
	 * Check if the dashboard widget is allowed.
	 *
	 * @return bool
	 */
	private function is_dashboard_allowed() {
		return 'on' === CL_Common::get_option( 'dashboard_widget', 'general', 'off' );
	}

	/**
	 * Add Dashboard widget
	 */
	public function add_dashboard_widget() {

		if ( ! $this->is_dashboard_allowed() ) {
			return;
		}

		wp_add_dashboard_widget(
			$this->id,
			__( 'Frosty Media News & Add-on Plugins', Custom_Login_Bootstrap::DOMAIN ),
			array( $this, 'dashboard_widget_callback' )
		);
	}

	/**
	 * Dashboard scripts & styles
	 */
	public function enqueue_scripts() {

		wp_enqueue_style(
			$this->id,
			plugins_url( 'css/dashboard.css', CUSTOM_LOGIN_FILE ),
			array(),
			CUSTOM_LOGIN_VERSION,
			'screen'
		);

		wp_enqueue_script(
			$this->id,
			plugins_url( 'js/dashboard.js', CUSTOM_LOGIN_FILE ),
			array( 'jquery' ),
			CUSTOM_LOGIN_VERSION,
			true
		);

		$localize_array = array(
			'is_active' => (bool) $this->is_dashboard_allowed(),
			'feed_url' => esc_url( 'https://frosty.media' ),
			'site_title' => __( 'Frosty.Media', Custom_Login_Bootstrap::DOMAIN ),
			'feed_title' => __( 'Unknown Title', Custom_Login_Bootstrap::DOMAIN ),
		);

		if ( !$this->is_dashboard_allowed() ) {
			$rss_items = CL_Common::fetch_rss_items( 1, $this->feed_url );

			if ( isset( $rss_items[ 0 ] ) && ( $item = $rss_items[ 0 ] ) &&
			     ( $item instanceof SimplePie_Item ) ) {
				$localize_array[ 'feed_title' ] = esc_html( $item->get_title() );
			}
		}

		wp_localize_script( $this->id, 'cl_admin_dashboard', $localize_array );
	}

	/**
	 * Dashboard widget
	 */
	public function dashboard_widget_callback() {
		static $count;

		// 'post' feed.
		$rss_items = CL_Common::fetch_rss_items( 1, $this->feed_url );

		$content = '<div class="rss-widget"><ul>';

		if ( ! $rss_items ) {
			$content .= '<li>' . __( 'Error fetching feed', Custom_Login_Bootstrap::DOMAIN ) . '</li>';
		} else {
			foreach ( $rss_items as $key => $item ) {

				if ( ! ( $item instanceof SimplePie_Item ) ) {
					continue;
				}

				$count ++;
				$feed_url = esc_url( $item->get_permalink() );
				$content .= '<li>';
				$content .= '<a class="rsswidget" href="' . esc_url( add_query_arg( array(
						'utm_medium'   => 'wpadmin_dashboard',
						'utm_term'     => 'newsitem',
						'utm_campaign' => Custom_Login_Bootstrap::DOMAIN,
					), $feed_url ) ) . '">' . esc_html( $item->get_title() ) . '</a>';

				if ( 1 === $count ) {
					$content .= '&nbsp;&nbsp;&nbsp;<span class="rss-date">' . $item->get_date( get_option( 'date_format' ) ) . '</span>';
					$content .= '<div class="rssSummary">' . strip_tags( wp_trim_words( $item->get_description(), 28 ) ) . '</div>';
				}
				$content .= '</li>';
			}
		}
		$content .= '</ul></div>';

		// 'plugin' feed.
		$rss_items = CL_Common::fetch_rss_items( 3, add_query_arg( array( 'post_type' => 'plugin', 'plugin_tag' => 'custom-login-extension', ), $this->feed_url ) );

		$content .= '<div class="rss-widget"><ul>';

		if ( ! $rss_items ) {
			$content .= '<li>' . __( 'Error fetching feed', Custom_Login_Bootstrap::DOMAIN ) . '</li>';
		} else {

			$extension = _x( 'Custom Login Add-on Plugins', 'A plugin that adds onto the Custom Login functions.', Custom_Login_Bootstrap::DOMAIN );
			$content .= sprintf( '<li><strong>%s</strong> <span class="dashicons dashicons-editor-help" data-toggle=".cl-extensions-desc"></span></li>', $extension );
			$content .= '<li>' . CL_Common::get_extension_description() . '</li>';

			foreach ( $rss_items as $item ) {

				if ( ! ( $item instanceof SimplePie_Item ) ) {
					continue;
				}

				$url = esc_url( $item->get_permalink() );
				$content .= '<li>';
				$content .= '<a class="" href="' . esc_url( add_query_arg( array(
						'utm_medium'   => 'wpadmin_dashboard',
						'utm_term'     => 'newsitem',
						'utm_campaign' => Custom_Login_Bootstrap::DOMAIN,
					), $url ) ) . '">' . esc_html( $item->get_title() ) . '</a>';
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
}
