<?php

use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}


$admin_url = add_query_arg(
    [ 'page' => CustomLogin::DOMAIN . '-add-ons' ],
    admin_url( 'options-general.php' )
);

$content = sprintf(
    __(
        'Install Custom Login Add-ons via <a href="%s">this page</a>. A valid license key is required.',
        CustomLogin::DOMAIN
    ),
    esc_url( $admin_url )
);

$object->createPostbox(
    'custom-login-add-ons',
    __( 'Add-on Plugin Installer',
        CustomLogin::DOMAIN ),
    $content
);
