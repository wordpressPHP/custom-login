<?php

use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Api\SettingsApi;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof SettingsApi ) ) {
    return;
}

foreach ( $object->getSettingsSections() as $section ) {

    // Continue if the design section if using the customizer.
    if ( $section['id'] === 'design' && Common::usingCustomizer() ) {
        continue;
    } ?>
    <div id="<?php echo $section['id']; ?>" class="group">

        <form action="<?php esc_url( admin_url( 'options.php' ) ); ?>"
              id="<?php echo $section['id']; ?>form" method="post">
            <?php
            do_action( SettingsApi::SETTING_ID . '_form_top_' . $section['id'] );

            settings_fields( SettingsApi::SETTING_ID );

            do_settings_sections( SettingsApi::SETTING_ID . '_' . $section['id'] );

            do_action( SettingsApi::SETTING_ID . '_form_bottom_' . $section['id'] );

            if ( isset( $section['submit'] ) && $section['submit'] === true ) {
                submit_button( sprintf( __( 'Save %s', CustomLogin::DOMAIN ), $section['title'] ) );
            }
            ?>
        </form>

    </div>
    <?php
    do_action( SettingsApi::SETTING_ID . '_settings_section', $section, $section['id'] );
}

do_action( SettingsApi::SETTING_ID . '_after_settings_sections_form' );
