<?php

use PassyCo\CustomLogin\ScriptsStyles;
use PassyCo\CustomLogin\WpLogin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Should we cache this?
$use_cache = ! ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV );

// Extract our variables
extract( WpLogin::$css_atts, EXTR_SKIP );

// Cache ALL THE THINGS!
if ( false === ( $css = get_transient( $trans_key ) ) ) {

	$css        = '';
	$close_rule = "}\n";
	
//	if ( ! $use_cache ) {
//		$css .= "/**\n *\n" . print_r( WpLogin::$css_atts, true ) . " */\n\n";
//	}

	$css .= "
/**
 * Custom Login by Austin Passy
 *
 * Plugin URI  : https://frosty.media/plugins/custom-login/
 * Version     : $version
 * Author URI  : http://austin.passy.co/
 * Add-on Plugins  : https://frosty.media/plugin/tag/custom-login-extension/
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
	$css .= ScriptsStyles::cssrule( 'html' );
	
	if ( ! empty( $html_background_color ) ) {
		$css .= ScriptsStyles::trailingsemicolonit( "background-color: {$html_background_color}" );
	}

	if ( ! empty( $html_background_url ) ) {
		$css .= ScriptsStyles::trailingsemicolonit( "background-image: url('{$html_background_url}')" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-position: {$html_background_position}" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-repeat: {$html_background_repeat}" );

		if ( ! empty( $html_background_size ) && 'none' !== $html_background_size ) {
			$css .= ScriptsStyles::prefixit( 'background-size', $html_background_size );
		}
	}

	$css .= $close_rule;
	
	/**
	 * body.login
	 */
	if ( ! empty( $html_background_color ) || ! empty( $html_background_url ) ) {
		$css .= ScriptsStyles::cssrule( 'body.login' );
		$css .= ScriptsStyles::trailingsemicolonit( "background: transparent" );
		$css .= $close_rule;
	}
	
	/**
	 * #login
	 */
	if ( ! empty( $login_form_width ) ) {
		$css .= ScriptsStyles::cssrule( '#login' );
		$css .= ScriptsStyles::trailingsemicolonit( "width: {$login_form_width}px" );
		$css .= $close_rule;
	}
	
	/**
	 * #login form
	 */
	$css .= ScriptsStyles::cssrule( '#login form' );

	if ( ! empty( $login_form_background_color ) ) {
		$css .= ScriptsStyles::trailingsemicolonit( "background-color: {$login_form_background_color}" );
	}
	
	if ( ! empty( $login_form_background_url ) ) {
		$css .= ScriptsStyles::trailingsemicolonit( "background-image: url('{$login_form_background_url}')" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-position: {$login_form_background_position}" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-repeat: {$login_form_background_repeat}" );

		if ( ! empty( $login_form_background_size ) && 'none' != $login_form_background_size ) {
			$login_form_background_size = 'flex' != $login_form_background_size ? $login_form_background_size : '100% auto';
			$css .= ScriptsStyles::prefixit( 'background-size', $login_form_background_size );
		}
	}
	
	if ( ! empty( $login_form_border_size ) && ! empty( $login_form_border_color ) ) {
		$login_form_border_size = rtrim( $login_form_border_size, 'px' );
		$css .= ScriptsStyles::trailingsemicolonit( "border: {$login_form_border_size}px solid {$login_form_border_color}" );
	}
	
	if ( ! empty( $login_form_border_radius ) ) {
		$login_form_border_radius = rtrim( $login_form_border_radius, 'px' ) . 'px';
		$css .= ScriptsStyles::prefixit( 'border-radius', $login_form_border_radius );
	}
	
	if ( ! empty( $login_form_box_shadow ) ) {

		if ( empty( $login_form_box_shadow_color ) ) {
			$login_form_box_shadow_color = '#121212';
		}
		$box_shadow = $login_form_box_shadow . ' ' . $login_form_box_shadow_color;
		$css .= ScriptsStyles::prefixit( 'box-shadow', trim( $box_shadow ) );
	}

	$css .= $close_rule;
	
	/**
	 * #login h1
	 */
	if ( ( ! empty( $hide_wp_logo ) && 'on' === $hide_wp_logo ) && empty( $logo_background_url ) ) {
		$css .= ScriptsStyles::cssrule( '#login h1' );
		$css .= ScriptsStyles::trailingsemicolonit( "display: none" );
		$css .= $close_rule;
	}
	
	/**
	 * .login h1
	 */
	if ( ( ! empty( $logo_force_form_max_width ) && 'on' === $logo_force_form_max_width ) && ! empty( $login_form_width ) ) {
		
		$css .= ScriptsStyles::cssrule( '.login h1' );
		$css .= ScriptsStyles::trailingsemicolonit( "width: {$login_form_width}px" );
		$css .= $close_rule;
	}
	
	/**
	 * .login h1 a
	 */
	if ( ! empty( $logo_background_url ) ) {

		$css .= ScriptsStyles::cssrule( '.login h1 a' );
		
		if ( ! empty( $logo_background_size_width ) ) {
			$css .= ScriptsStyles::trailingsemicolonit( "width: {$logo_background_size_width}px !important" );
		}

		if ( ! empty( $logo_background_size_height ) ) {
			$css .= ScriptsStyles::trailingsemicolonit( "height: {$logo_background_size_height}px !important" );
		}
		
		$css .= ScriptsStyles::trailingsemicolonit( "background-image: url('{$logo_background_url}')" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-position: {$logo_background_position}" );
		$css .= ScriptsStyles::trailingsemicolonit( "background-repeat: {$logo_background_repeat}" );

		if ( ! empty( $logo_background_size ) && 'none' !== $logo_background_size ) {
			$css .= ScriptsStyles::prefixit( 'background-size', $logo_background_size );
		} else {
			$css .= ScriptsStyles::prefixit( 'background-size', 'inherit' );
		}

		$css .= $close_rule;
		
	}
	
	/**
	 * .login label | #loginform label, #lostpasswordform label
	 */
	if ( ! empty( $label_color ) ) {
		$css .= ScriptsStyles::cssrule( '.login label' );
		$css .= ScriptsStyles::trailingsemicolonit( "color: {$label_color}" );
		$css .= $close_rule;
	}
	
	/**
	 * .login #nav a, .login #backtoblog a
	 */
	if ( ! empty( $nav_color ) ) {
		
		$css .= ScriptsStyles::cssrule( '.login #nav a, .login #backtoblog a' );
		$css .= ScriptsStyles::trailingsemicolonit( "color: {$nav_color} !important" );
		
		if ( ! empty( $nav_text_shadow_color ) ) {
			$css .= ScriptsStyles::trailingsemicolonit( "text-shadow: 0 1px 0 {$nav_text_shadow_color}" );
		}

		$css .= $close_rule;
		
	}
	
	/**
	 * .login #nav a:hover, .login #backtoblog a:hover
	 */
	if ( ! empty( $nav_hover_color ) ) {
		$css .= ScriptsStyles::cssrule( '.login #nav a:hover, .login #backtoblog a:hover' );
		$css .= ScriptsStyles::trailingsemicolonit( "color: {$nav_hover_color} !important" );
		$css .= ScriptsStyles::trailingsemicolonit( "text-shadow: 0 1px 0 {$nav_text_shadow_hover_color}" );
		$css .= $close_rule;
	}
	
	// Cache ALL THE CSS!
//	if ( $use_cache ) {
//		set_transient( $trans_key, $css, ( YEAR_IN_SECONDS / 3 ) );
//	}
}

echo $css;