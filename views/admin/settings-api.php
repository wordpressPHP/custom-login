<?php

use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}

?>
<div class="CustomLogin wrap">

    <div class="CustomLogin__container">

        <div class="CustomLogin__header">
            <h3><?php esc_html_e( 'Custom Login', CustomLogin::DOMAIN ); ?></h3>
            <span><?php echo $object->getCustomLogin()->getVersion(); ?></span>
            <div><?php
                printf(
                    esc_html__( 'A %s plugin', CustomLogin::DOMAIN ),
                    '<strong><a href="https://frosty.media/" target="_blank">Frosty Media</a></strong>'
                );
                ?>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="https://twitter.com/Frosty_Media">
                    <span class="dashicons dashicons-twitter"></span></a>
            </div>
        </div><!-- #CustomLogin__header -->

        <div id="CustomLogin__notices">
            <h2></h2>
        </div><!-- #CustomLogin__notices -->

        <div id="CustomLogin__sticky">
            <div class="wrap">
                <div id="sticky-admin-notice">
                    <?php do_action( SettingsApi::SETTING_ID . '_sticky_admin_notice' ); ?>
                </div>
                <div class="alignright">
                    <?php do_action( SettingsApi::SETTING_ID . '_before_submit_button' ); ?>
                    <?php submit_button(
                        __( 'Save Changes', CustomLogin::DOMAIN ),
                        'primary',
                        'cl_save',
                        false
                    ); ?>
                </div>
                <br class="clear">
            </div>
        </div><!-- #CustomLogin__sticky -->

        <div class="CustomLogin__sidebar">
            <?php $object->renderNavigation(); ?>
            <?php do_action( SettingsApi::SETTING_ID . '_settings_sidebars', $object->getSettingsSidebars() ); ?>
        </div><!-- #CustomLogin__sidebar -->

        <div class="CustomLogin__main">
            <?php $object->renderForms(); ?>
        </div><!-- #CustomLogin__main -->

    </div><!-- #CustomLogin__wrapper -->

</div>
