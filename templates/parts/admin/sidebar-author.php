<?php

use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}

ob_start();
?>
    <a href="https://wordpress.org/support/view/plugin-reviews/custom-login"
       class="star-rating"
       target="_blank">
        <i class="dashicons dashicons-star-filled"></i>
        <i class="dashicons dashicons-star-filled"></i>
        <i class="dashicons dashicons-star-filled"></i>
        <i class="dashicons dashicons-star-filled"></i>
        <i class="dashicons dashicons-star-filled"></i>
    </a>
    <?php echo esc_html_x( 'Rate', 'Rate; as in rate this plugin.', CustomLogin::DOMAIN ); ?>

    <ul>
        <li>
            <?php echo esc_html_x( 'Author:', 'Author or creator', CustomLogin::DOMAIN ); ?>
            <a href="http://austin.passy.co" target="_blank">Austin Passy</a>
        </li>
        <li>
            <?php esc_html_e( 'Twitter:', CustomLogin::DOMAIN ); ?>
            <a href="https://twitter.com/TheFrosty" target="_blank">TheFrosty</a>
        </li>
        <li>
            <?php esc_html_e( 'GitHub:', CustomLogin::DOMAIN ); ?>
            <a href="https://github.com/thefrosty/custom-login/issues" target="_blank"><?php
                esc_html_e( 'GitHub:', CustomLogin::DOMAIN );
                ?></a>
        </li>
    </ul>
<?php

$object->createPostbox(
    'frosty-media-author',
    sprintf(
        esc_attr__( 'Custom Login v%s', CustomLogin::DOMAIN ), $object->getCustomLogin()->getVersion()
    ),
    ob_get_clean()
);