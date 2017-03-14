<?php

namespace PassyCo\CustomLogin\Admin;

use PassyCo\CustomLogin\Api\SettingsApi;
use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\HooksTrait;
use PassyCo\CustomLogin\WpHooksInterface;

/**
 * Class SettingsImportExport
 *
 * @package PassyCo\CustomLogin\Admin
 */
class SettingsImportExport implements WpHooksInterface {

    use HooksTrait;

    const ID = 'import_export';

    /**
     * Add class hooks.
     */
    public function addHooks() {
        if ( ! $this->isImportExportOn() ) {
            return;
        }

        $this->addAction(
            SettingsApi::SETTING_ID . '_before_sanitize_options',
            [ $this, 'maybeImportSettings' ]
        );
        $this->addAction(
            'admin_action_' . SettingsApi::SETTING_ID . '_download_export',
            [ $this, 'downloadExport' ]
        );
        $this->addFilter(
            SettingsApi::SETTING_ID . '_registered_settings_sections',
            [ $this, 'addSettingsSections' ]
        );
        $this->addFilter(
            SettingsApi::SETTING_ID . '_registered_settings_fields',
            [ $this, 'addSettingsFields' ]
        );
    }

    /**
     * Sanitize callback for Settings API before input into database.
     *
     * @link http://stackoverflow.com/a/10797086/558561
     *
     * @param array $options
     */
    protected function maybeImportSettings( $options ) {
        if ( ! isset( $options[ self::ID ] ) || ! isset( $options[ self::ID ]['import'] ) ) {
            return;
        }

        $import = $options[ self::ID ]['import'];

        if ( ! empty( $import ) && ( base64_encode( base64_decode( $import, true ) ) === $import ) ) {

            $new_options = maybe_unserialize( base64_decode( $import ) );

            if ( is_array( $new_options ) && $new_options !== [] ) {
                if ( update_option( SettingsApi::SETTING_ID, $new_options ) ) {
                    add_settings_error(
                        SettingsApi::SETTING_ID,
                        'settings_updated',
                        __( 'Custom Login Settings import was successful.', CustomLogin::DOMAIN ),
                        'updated'
                    );
                }
            }
        }
    }

    /**
     * Export the settings.
     *
     * @link http://stackoverflow.com/a/16440501/558561
     */
    protected function downloadExport() {
        if ( ! isset( $_GET['cl_nonce'] ) || ! wp_verify_nonce( $_GET['cl_nonce'], 'export' ) ) {
            wp_redirect( remove_query_arg( [ 'action', 'cl_nonce' ] ) );
            exit;
        }

        $date = date( 'Y-m-d' );

        ignore_user_abort( true );

        nocache_headers();
        header( 'Content-type: text/plain; charset=utf-8' );
        header( "Content-Disposition: attachment; filename=custom-login-settings-export-{$date}.txt" );
        header( 'Expires: 0' );

        echo $this->getEncodedSettings();
        exit;
    }

    /**
     * @param $sections
     *
     * @return array
     */
    protected function addSettingsSections( $sections ) {
        $sections[] = [
            'id' => self::ID,
            'title' => __( 'Import/Export Settings', CustomLogin::DOMAIN ),
            'submit' => true,
        ];

        return $sections;
    }

    /**
     * @param $fields
     *
     * @return mixed
     */
    protected function addSettingsFields( $fields ) {
        $fields[ self::ID ] = [
            [
                'name' => 'import',
                'label' => __( 'Import', CustomLogin::DOMAIN ),
                'desc' => '',
                'type' => 'textarea',
                'sanitize_cb' => '__return_empty_string',
            ],
            [
                'name' => 'export',
                'label' => __( 'Export', CustomLogin::DOMAIN ),
                'desc' => sprintf(
                    __( 'This textarea is always pre-populated with the current settings. Copy these settings to import at a later time, or <a href="%s">click here</a> to download them.', CustomLogin::DOMAIN ),
                    esc_url( wp_nonce_url(
                        add_query_arg( [ 'action' => SettingsApi::SETTING_ID . '_download_export' ],
                            ''
                        ),
                        'export',
                        'cl_nonce'
                    ) )
                ),
                'default' => $this->getEncodedSettings(),
                'type' => 'textarea',
                'attributes' => [
                    'readonly' => 'readonly',
                ],
                'sanitize_cb' => '__return_empty_string',
            ],

        ];

        return $fields;
    }

    /**
     * Return the full array of settings
     *
     * @access private
     */
    private function getEncodedSettings() {
        return base64_encode( maybe_serialize( get_option( SettingsApi::SETTING_ID, [] ) ) );
    }

    /**
     * Is the Import/Export feature active (on).
     *
     * @return bool
     */
    private function isImportExportOn() {
        return Common::getOption( 'import_export', 'general', 'off' ) === Common::ON;
    }
}
