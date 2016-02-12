<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CL_Customize_Color_Alpha_Control extends WP_Customize_Color_Control {

	public $type = 'color-alpha';

	public $palette = true;

	public $default = '#FFFFFF';

	public function to_json() {
		parent::to_json();
		$this->json[ 'palette' ] = $this->palette;
	}

	public function render_content() {
	}

	public function content_template() { ?>
		<# if ( data.help ) { #>
			<a href="#" class="tooltip hint--left" data-hint="{{ data.help }}"><span
					class='dashicons dashicons-info'></span></a>
			<# } #>
				<label>
				<span class="customize-control-title">
					{{{ data.label }}}
					<# if ( data.description ) { #>
						<span class="description customize-control-description">{{ data.description }}</span>
						<# } #>
				</span>
					<input type="text" data-palette="{{ data.palette }}" data-default-color="{{ data.default }}"
					       data-alpha="true" value="{{ data.value }}" class="kirki-color-control color-picker" {{{
					       data.link }}}/>
				</label>
	<?php }
}
