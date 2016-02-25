<?php

/**
 * @todo Make this AJAX loaded.
 */

$defaults = array(
	'items' => 6,
	'feed'  => 'https://frosty.media/feed/?post_type=plugin&plugin_tag=custom-login-extension',
);

$args = wp_parse_args( $args, $defaults );

$rss_items   = CL_Common::fetch_rss_items( $args[ 'items' ], $args[ 'feed' ] );
$description = CL_Common::get_extension_description();

$content = $description;
$content .= '<ul>';

if ( ! $rss_items ) {
	$content .= '<li>' . __( 'Error fetching feed', Custom_Login_Bootstrap::DOMAIN ) . '</li>';
} else {
	foreach ( $rss_items as $item ) {

		if ( ! ( $item instanceof SimplePie_Item ) ) {
			continue;
		}

		$content .= '<li>';
		$content .= '<a href="' . esc_url( $item->get_permalink(), null, 'display' ) .
		            '?utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsite&utm_campaign=' .
		            CL_Settings_API::SETTING_ID . '_settings-api" target="_blank">' .
		            esc_html( $item->get_title() ) . '</a>';
		$content .= '</li>';
	}
}
$content .= '</ul>';

CL_Settings_API::create_postbox( 'custom-login-extensions',
	sprintf( __( 'Custom Login Extensions %s',
		Custom_Login_Bootstrap::DOMAIN ),
		'<span class="dashicons dashicons-editor-help" data-toggle=".cl-extensions-desc"></span>'
	),
	$content
);