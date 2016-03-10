<?php

foreach ( CL_Settings_API::get_instance()->settings_sections as $section ) {

	/**
	 * Continue past the design section if using the customizer.
	 */
	if ( 'design' === $section[ 'id' ] && CL_Common::using_customizer() ) {
		continue;
	} ?>
	<div id="<?php echo $section[ 'id' ]; ?>" class="group">

		<form action="<?php esc_url( admin_url( 'options.php' ) ); ?>"
		      id="<?php echo $section[ 'id' ]; ?>form" method="post">
			<?php
			do_action( CL_Settings_API::SETTING_ID . '_form_top_' . $section[ 'id' ] );

			settings_fields( CL_Settings_API::SETTING_ID );

			do_settings_sections( CL_Settings_API::SETTING_ID . '_' . $section[ 'id' ] );

			do_action( CL_Settings_API::SETTING_ID . '_form_bottom_' . $section[ 'id' ] );

			if ( isset( $section[ 'submit' ] ) && true === $section[ 'submit' ] ) {
				submit_button( sprintf( __( 'Save %s', Custom_Login_Bootstrap::DOMAIN ), $section[ 'title' ] ) );
			} ?>
		</form>

	</div><?php
}

do_action( CL_Settings_API::SETTING_ID . '_after_settings_sections_form' );