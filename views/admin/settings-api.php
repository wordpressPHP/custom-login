<div class="wrap">

	<div class="cl-container">

		<div class="cl-header">
			<h3><?php _e( 'Custom Login', Custom_Login_Bootstrap::DOMAIN ); ?></h3>
			<span><?php echo CUSTOM_LOGIN_VERSION; ?></span>
			<div>
				<?php echo sprintf( __( 'A %s plugin', Custom_Login_Bootstrap::DOMAIN ),
					'<strong><a href="https://frosty.media/" target="_blank">Frosty Media</a></strong>' ); ?>
					&nbsp;&nbsp;|&nbsp;&nbsp;<a href="https://twitter.com/Frosty_Media"><span
						class="dashicons dashicons-twitter"></span></a>
			</div>
		</div><!-- #cl-header -->

		<div id="cl-notices">
			<h2></h2>
		</div><!-- #cl-text -->

		<div id="cl-sticky">
			<div class="wrap">
				<div id="sticky-admin-notice">
					<?php do_action( CL_Settings_API::SETTING_ID . '_sticky_admin_notice' ); ?>
				</div>
				<div class="alignright">
					<?php do_action( CL_Settings_API::SETTING_ID . '_before_submit_button' ); ?>
					<?php submit_button(
						__( 'Save Changes', Custom_Login_Bootstrap::DOMAIN ),
						'primary',
						'cl_save',
						false
					); ?>
				</div>
				<br class="clear">
			</div>
		</div><!-- #cl-sticky -->

		<div class="cl-sidebar">
			<?php CL_Settings_API::show_navigation(); ?>
			<?php do_action( CL_Settings_API::SETTING_ID . '_settings_sidebars', CL_Settings_API::get_instance()->settings_sidebars ); ?>
		</div><!-- #cl-header -->

		<div class="cl-main">
			<?php CL_Settings_API::show_forms(); ?>
		</div><!-- #cl-header -->

	</div><!-- #cl-wrapper -->

</div>