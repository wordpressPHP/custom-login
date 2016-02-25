<?php

$content = sprintf( '%s: <a href="https://wordpress.org/support/view/plugin-reviews/custom-login" class="star-rating" target="_blank">
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			</a>', _x( 'Rate', 'Rate; as in rate this plugin.', Custom_Login_Bootstrap::DOMAIN ) );

$content .= '<ul>';
$content .= sprintf( '<li>%s: <a href="http://austin.passy.co" target="_blank">Austin Passy</a></li>', _x( 'Author', 'the author of this plugin', Custom_Login_Bootstrap::DOMAIN ) );
$content .= '<li>Twitter: <a href="https://twitter.com/TheFrosty" target="_blank">TheFrosty</a></li>';
$content .= '</ul>';

$content .= sprintf( __( 'If you have suggestions for a new extension, feel free to submit an enhancement request on
<a href="%s" target="_blank">GitHub</a>.', Custom_Login_Bootstrap::DOMAIN ),
	'https://github.com/thefrosty/custom-login/issues'
);

CL_Settings_API::create_postbox( 'frosty-media-author', sprintf( __( 'Custom Login v%s', Custom_Login_Bootstrap::DOMAIN ), CUSTOM_LOGIN_VERSION ), $content );