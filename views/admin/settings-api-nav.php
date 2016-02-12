<?php

echo '<ul class="cl-sections-menu">';

foreach ( CL_Settings_API::get_instance()->settings_sections as $section ) {

	$using_customizer = CL_Common::using_customizer();

	printf( '<li><a href="%s" data-tab-id="%s">%s</a></li>',
		isset( $section[ 'href' ] ) ? $using_customizer ? $section[ 'href' ] : 'javascript:;' : 'javascript:;',
		$section[ 'id' ],
		$section[ 'title' ]
	);
}

echo '</ul>';