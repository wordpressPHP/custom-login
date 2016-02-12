<?php

$content = sprintf( '%s: <a href="https://wordpress.org/support/view/plugin-reviews/custom-login" class="star-rating" target="_blank">
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			<i class="dashicons dashicons-star-filled"></i>
			</a>', _x( 'Rate', 'rate; as in rate this plugin', Custom_Login_Bootstrap::DOMAIN ) );

$content .= '<ul>';
$content .= sprintf( '<li>%s: <a href="http://austin.passy.co" target="_blank">Austin Passy</a></li>', _x( 'Author', 'the author of this plugin', Custom_Login_Bootstrap::DOMAIN ) );
$content .= sprintf( '<li>%s: <a href="https://twitter.com/TheFrosty" target="_blank">TheFrosty</a></li>', __( 'Twitter', Custom_Login_Bootstrap::DOMAIN ) );
$content .= '</ul>';

$content .= sprintf(
	__( 'If you have suggestions for a new add-on, feel free to open a support request on
<a href="%s" target="_blank">GitHub</a>. Want regular updates?
Follow me on <a href="%s" target="_blank">Twitter</a> or visit my <a href="%s" target="_blank">blog</a>'
	),
	'https://github.com/thefrosty/custom-login/issues',
	'https://twitter.com/TheFrosty',
	'http://austin.passy.co'
);

CL_Settings_API::create_postbox( 'frosty-media-author', __( 'Custom Login', Custom_Login_Bootstrap::DOMAIN ), $content );