<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Should we cache this?
$use_cache = ! ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV );

// Extract our variables
extract( CL_WP_Login::$css_atts, EXTR_SKIP );

// Cache ALL THE THINGS!
if ( false === ( $css = get_transient( $trans_key ) ) ) {

	$css        = '';
	$close_rule = "}\n";
	
	if ( ! $use_cache ) {
		$css .= "/**\n *\n" . print_r( CL_WP_Login::$css_atts, true ) . " */\n\n";
	}

	$css .= "
/**
 * Custom Login by Austin Passy
 *
 * Plugin URI  : https://frosty.media/plugins/custom-login/
 * Version     : $version
 * Author URI  : http://austin.passy.co/
 * Extensions  : https://frosty.media/plugin/tag/custom-login-extension/
 */\n\n";
	
	/* Custom user input */
	if ( ! empty( $custom_css ) ) {
		$custom_css = wp_specialchars_decode( stripslashes( $custom_css ), 1 );
		
		$css .= "/* START Custom CSS */\n";
		$css .= str_replace(
			array( '{BSLASH}' ),
			array( '\\' ),
			$custom_css
		);
		$css .= "\n/* END Custom CSS */\n";
		$css .= "\n\n";
	}
	
	/**
	 * html
	 */
	$css .= CL_Scripts_Styles::cssrule( 'html' );
	
	if ( ! empty( $html_background_color ) ) {
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-color: {$html_background_color}" );
	}

	if ( ! empty( $html_background_url ) ) {
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-image: url('{$html_background_url}')" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-position: {$html_background_position}" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-repeat: {$html_background_repeat}" );

		if ( ! empty( $html_background_size ) && 'none' !== $html_background_size ) {
			$css .= CL_Scripts_Styles::prefixit( 'background-size', $html_background_size );
		}
	}

	$css .= $close_rule;
	
	/**
	 * body.login
	 */
	if ( ! empty( $html_background_color ) || ! empty( $html_background_url ) ) {
		$css .= CL_Scripts_Styles::cssrule( 'body.login' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background: transparent" );
		$css .= $close_rule;
	}
	
	/**
	 * #login
	 */
	if ( ! empty( $login_form_width ) ) {
		$css .= CL_Scripts_Styles::cssrule( '#login' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "width: {$login_form_width}px" );
		$css .= $close_rule;
	}
	
	/**
	 * #login form
	 */
	$css .= CL_Scripts_Styles::cssrule( '#login form' );

	if ( ! empty( $login_form_background_color ) ) {
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-color: {$login_form_background_color}" );
	}
	
	if ( ! empty( $login_form_background_url ) ) {
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-image: url('{$login_form_background_url}')" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-position: {$login_form_background_position}" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-repeat: {$login_form_background_repeat}" );

		if ( ! empty( $login_form_background_size ) && 'none' != $login_form_background_size ) {
			$login_form_background_size = 'flex' != $login_form_background_size ? $login_form_background_size : '100% auto';
			$css .= CL_Scripts_Styles::prefixit( 'background-size', $login_form_background_size );
		}
	}
	
	if ( ! empty( $login_form_border_size ) && ! empty( $login_form_border_color ) ) {
		$login_form_border_size = rtrim( $login_form_border_size, 'px' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "border: {$login_form_border_size}px solid {$login_form_border_color}" );
	}
	
	if ( ! empty( $login_form_border_radius ) ) {
		$login_form_border_radius = rtrim( $login_form_border_radius, 'px' ) . 'px';
		$css .= CL_Scripts_Styles::prefixit( 'border-radius', $login_form_border_radius );
	}
	
	if ( ! empty( $login_form_box_shadow ) ) {

		if ( empty( $login_form_box_shadow_color ) ) {
			$login_form_box_shadow_color = '#121212';
		}
		$box_shadow = $login_form_box_shadow . ' ' . $login_form_box_shadow_color;
		$css .= CL_Scripts_Styles::prefixit( 'box-shadow', trim( $box_shadow ) );
	}

	$css .= $close_rule;
	
	/**
	 * #login h1
	 */
	if ( ( ! empty( $hide_wp_logo ) && 'on' === $hide_wp_logo ) && empty( $logo_background_url ) ) {
		$css .= CL_Scripts_Styles::cssrule( '#login h1' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "display: none" );
		$css .= $close_rule;
	}
	
	/**
	 * .login h1
	 */
	if ( ( ! empty( $logo_force_form_max_width ) && 'on' === $logo_force_form_max_width ) && ! empty( $login_form_width ) ) {
		
		$css .= CL_Scripts_Styles::cssrule( '.login h1' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "width: {$login_form_width}px" );
		$css .= $close_rule;
	}
	
	/**
	 * .login h1 a
	 */
	if ( ! empty( $logo_background_url ) ) {

		$css .= CL_Scripts_Styles::cssrule( '.login h1 a' );
		
		if ( ! empty( $logo_background_size_width ) ) {
			$css .= CL_Scripts_Styles::trailingsemicolonit( "width: {$logo_background_size_width}px !important" );
		}

		if ( ! empty( $logo_background_size_height ) ) {
			$css .= CL_Scripts_Styles::trailingsemicolonit( "height: {$logo_background_size_height}px !important" );
		}
		
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-image: url('{$logo_background_url}')" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-position: {$logo_background_position}" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "background-repeat: {$logo_background_repeat}" );

		if ( ! empty( $logo_background_size ) && 'none' !== $logo_background_size ) {
			$css .= CL_Scripts_Styles::prefixit( 'background-size', $logo_background_size );
		} else {
			$css .= CL_Scripts_Styles::prefixit( 'background-size', 'inherit' );
		}

		$css .= $close_rule;
		
	}
	
	/**
	 * .login label | #loginform label, #lostpasswordform label
	 */
	if ( ! empty( $label_color ) ) {
		$css .= CL_Scripts_Styles::cssrule( '.login label' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "color: {$label_color}" );
		$css .= $close_rule;
	}
	
	/**
	 * .login #nav a, .login #backtoblog a
	 */
	if ( ! empty( $nav_color ) ) {
		
		$css .= CL_Scripts_Styles::cssrule( '.login #nav a, .login #backtoblog a' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "color: {$nav_color} !important" );
		
		if ( ! empty( $nav_text_shadow_color ) ) {
			$css .= CL_Scripts_Styles::trailingsemicolonit( "text-shadow: 0 1px 0 {$nav_text_shadow_color}" );
		}

		$css .= $close_rule;
		
	}
	
	/**
	 * .login #nav a:hover, .login #backtoblog a:hover
	 */
	if ( ! empty( $nav_hover_color ) ) {
		$css .= CL_Scripts_Styles::cssrule( '.login #nav a:hover, .login #backtoblog a:hover' );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "color: {$nav_hover_color} !important" );
		$css .= CL_Scripts_Styles::trailingsemicolonit( "text-shadow: 0 1px 0 {$nav_text_shadow_hover_color}" );
		$css .= $close_rule;
	}
	
	// Cache ALL THE CSS!
	if ( $use_cache ) {
		set_transient( $trans_key, $css, ( YEAR_IN_SECONDS / 3 ) );
	}
}

echo $css;