<?php

$admin_url = add_query_arg( array( 'page' => Custom_Login_Bootstrap::DOMAIN . '-add-ons' ),
	admin_url( 'options-general.php' ) );

$content = sprintf(
	__( 'Install Custom Login Add-ons via <a href="%s">this page</a>. A valid license key is required.',
		Custom_Login_Bootstrap::DOMAIN ), esc_url( $admin_url ) );

CL_Settings_API::create_postbox( 'custom-login-add-ons', __( 'Add-on Plugin Installer',
	Custom_Login_Bootstrap::DOMAIN ), $content );