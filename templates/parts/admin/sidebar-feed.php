<?php

use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}

/**
 * @todo Make this AJAX loaded.
 */

$defaults = [
    'items' => 6,
    'feed' => 'https://frosty.media/feed/?post_type=plugin&plugin_tag=custom-login-extension',
];

$args = wp_parse_args( $args, $defaults );

$rss_items   = Common::getFeedItems( $args['items'], $args['feed'] );
$description = Common::getExtensionDescription();

$content = $description;
$content .= '<ul>';

if ( ! $rss_items ) {
    $content .= '<li>' . esc_html__( 'Error fetching feed', CustomLogin::DOMAIN ) . '</li>';
} else {
    foreach ( $rss_items as $item ) {

        if ( ! ( $item instanceof \SimplePie_Item ) ) {
            continue;
        }

        $content .= '<li>';
        $url = add_query_arg(
            [
                'utm_source' => 'wpadmin',
                'utm_medium' => 'sidebarwidget',
                'utm_term' => 'newsite',
                'utm_campaign' => SettingsApi::SETTING_ID . '_settings-api',
            ],
            $item->get_permalink()
        );
        $content .= '<a href="' . esc_url( $url ) . '" target="_blank">' .
                    esc_html( $item->get_title() ) . '</a></li>';
    }
}
$content .= '</ul>';

$object->createPostbox(
    'custom-login-extensions',
    sprintf( __( 'Custom Login Add-ons %s',
        CustomLogin::DOMAIN ),
        '<span class="dashicons dashicons-editor-help" data-toggle=".cl-extensions-desc"></span>'
    ),
    $content
);