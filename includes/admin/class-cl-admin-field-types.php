<?php

use Custom_Login_Bootstrap as Custom_Login;

class CL_Admin_Field_Types {

	/**
	 * @var CL_Settings_API
	 */
	private $cl_settings_api;
	private $id;

	/**
	 * CL_Admin_Field_Types constructor.
	 *
	 * @param CL_Settings_API $settings_api
	 */
	public function __construct( CL_Settings_API $settings_api ) {
		$this->cl_settings_api = $settings_api;
		$this->id = CL_Settings_API::SETTING_ID;
	}

	/**
	 * Get field description for display
	 *
	 * @param array $args settings field args
	 *
	 * @return string
	 */
	private function get_field_description( array $args ) {
		return ! empty( $args[ 'desc' ] ) ? sprintf( '<p class="description">%s</p>', $args[ 'desc' ] ) : '';
	}

	/**
	 * Helper function to return extra parameter as a string.
	 *
	 * @param array $args Default incoming arguments
	 *
	 * @return string
	 */
	private function get_extra_field_params( array $args = array() ) {

		$return     = '';
		$attributes = isset( $args[ 'attributes' ] ) && is_array( $args[ 'attributes' ] ) ? $args[ 'attributes' ] : null;

		if ( null !== $attributes ) {
			foreach ( $attributes as $key => $value ) {
				$return .= sprintf( ' %s="%s"', $key, $value );
			}
		}

		return $return;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args Settings field args
	 */
	public function text( array $args ) {
		
		$value = esc_attr( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$size  = ! empty( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
		$type  = ! empty( $args[ 'type' ] ) ? $args[ 'type' ] : 'text';
		$class = ! empty( $args[ 'class' ] ) ? $args[ 'class' ] : '';

		$html = sprintf( '<input type="%1$s" class="%2$s-text %3$s" id="%4$s[%5$s]" name="%4$s[%5$s]" value="%6$s">',
			$type,
			$size,
			$class,
			$args[ 'section' ],
			$args[ 'id' ],
			$value
		);
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function text_number( array $args ) {

		$args[ 'type' ]                 = 'number';
		$args[ 'attributes' ][ 'step' ] = 'any';
		$this->text( $args );
	}

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function password( array $args ) {

		$args[ 'type' ] = 'password';
		$this->text( $args );
	}

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function file( array $args ) {

		$value = esc_attr( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
		$id    = $args[ 'section' ] . '[' . $args[ 'id' ] . ']';

		/* Localize the array */
		$this->cl_settings_api->add_localize_array(
			'file',
			array(
				'id'      => $args[ 'id' ],
				'section' => $args[ 'section' ],
			)
		);

		$html = '<p class="control is-grouped">';
		$html .= sprintf( '<input type="text" class="input %1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s">', $size, $args[ 'section' ], $args[ 'id' ], $value );
		$html .= '<input type="button" class="button ' . $args[ 'id' ] . '-browse" id="' . $id . '_button" value="Browse" style="margin-left:5px" >';
		$html .= '<input type="button" class="button ' . $args[ 'id' ] . '-clear" id="' . $id . '_clear" value="Clear" style="margin-left:5px" >';
		$html .= '</p>';
		$html .= $this->get_field_description( $args );

		/* Image */
		$html .= '<div id="' . $id . '_preview" class="' . $id . '_preview">';
		if ( $value != '' ) {
			$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
			if ( $check_image ) {
				$html .= '<div class="img-wrapper">';
				$html .= '<img src="' . $value . '" alt="" >';
				$html .= '<a href="#" class="remove_file_button" rel="' . $id . '">Remove Image</a>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function text_array( array $args ) {

		$value = CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] );
		$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';

		$html = '<ul style="margin-top:0">';

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$html .= '<li>';
				$html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s][]" value="%4$s" data-key="%5$s">', $size, $args[ 'section' ], $args[ 'id' ], esc_attr( $val ), $key );
				$html .= sprintf( '<a href="#" class="button dodelete-%1$s[%2$s]">-</a>', $args[ 'section' ], $args[ 'id' ] );
				$html .= '</li>';
			}
		} else {
			$html .= '<li>';
			$html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s][]" value="%4$s" data-key="0" data-array="false">', $size, $args[ 'section' ], $args[ 'id' ], esc_attr( $value ) );
			$html .= sprintf( '<a href="#" class="button dodelete-%1$s[%2$s]">-</a>', $args[ 'section' ], $args[ 'id' ] );
			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= sprintf( '<a href="#" class="button docopy-%1$s[%2$s]">+</a>', $args[ 'section' ], $args[ 'id' ] );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function colorpicker( array $args ) {

		$value   = esc_attr( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$check   = esc_attr( CL_Common::get_option( $args[ 'id' ] . '_checkbox', $args[ 'section' ], $args[ 'default' ] ) );
		$opacity = esc_attr( CL_Common::get_option( $args[ 'id' ] . '_opacity', $args[ 'section' ], $args[ 'default' ] ) );
		$size    = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'small';
		$options = array( '1', '0.9', '0.8', '0.7', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1', '0', );
		$class   = 'on' != $check ? ' hidden' : '';

		/* Localize the array */

		$this->cl_settings_api->add_localize_array(
			'colorpicker',
			array(
				'id'      => $args[ 'id' ],
				'section' => $args[ 'section' ],
			)
		);

		/* Color */
		$html = '<div class="cl-colorpicker-wrap">';
		$html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" style="float:left">', $size, $args[ 'section' ], $args[ 'id' ], $value );

		/* Allow Opacity */
		$html .= '<div class="checkbox-wrap">';
		$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" >', $args[ 'section' ], $args[ 'id' ] . '_checkbox' );
		$html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s >', $args[ 'section' ], $args[ 'id' ] . '_checkbox', $check, checked( $check, 'on', false ) );
		$html .= sprintf( __( '<label for="%1$s[%2$s]">Opacity</label>', Custom_Login::DOMAIN ), $args[ 'section' ], $args[ 'id' ] . '_checkbox' );
		$html .= '</div>';

		/* Opacity */
		$html .= sprintf( '<select class="%1$s%4$s" name="%2$s[%3$s]" id="%2$s[%3$s]" style="margin-left:70px;">', $size, $args[ 'section' ], $args[ 'id' ] . '_opacity', $class );
		foreach ( $options as $key ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $opacity, $key, false ), $key );
		}
		$html .= '</select>';
		$html .= '<br class="clear">';
		$html .= '</div>';
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function checkbox( array $args ) {

		$value = esc_attr( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );

		$html = '<div class="checkbox-wrap">';
		$html .= sprintf( '<label class="cl-control checkbox" for="%1$s[%2$s]">', $args[ 'section' ], $args[ 'id' ] );
		$html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s>',
			$args[ 'section' ],
			$args[ 'id' ],
			$value,
			checked( $value, 'on', false )
		);
		$html .= '<span class="control-indicator"></span>';
		$html .= $this->get_field_description( $args );
		$html .= '</label></div>';

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function multicheck( array $args ) {

		$value = CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] );

		$html = '<div class="checkbox-wrap">';
		$html .= '<ul>';
		foreach ( $args[ 'options' ] as $key => $label ) {
			$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
			$html .= '<li>';
			$html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s >', $args[ 'section' ], $args[ 'id' ], $key, checked( $checked, $key, false ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%4$s]" title="%3$s"> %3$s</label>', $args[ 'section' ], $args[ 'id' ], $label, $key );
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</div>';

		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function radio( array $args ) {

		$value = CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] );

		$html = '<div class="radio-wrap">';
		$html .= '<ul>';
		foreach ( $args[ 'options' ] as $key => $label ) {
			$html .= '<li>';
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s >', $args[ 'section' ], $args[ 'id' ], $key, checked( $value, $key, false ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%4$s]" title="%3$s"> %3$s</label><br>', $args[ 'section' ], $args[ 'id' ], $label, $key );
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</div>';

		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function select( array $args ) {

		$value = esc_attr( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';

		/* Localize the array */
		$this->cl_settings_api->add_localize_array(
			'select',
			array(
				'id'      => $args[ 'id' ],
				'section' => $args[ 'section' ],
			)
		);

		$html = '<p><span class="select">';
		$html .= sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args[ 'section' ], $args[ 'id' ] );
		foreach ( $args[ 'options' ] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}
		$html .= '</select></span></p>';
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function textarea( array $args ) {

		$value = esc_textarea( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
		$extra = isset( $args[ 'extra' ] ) && is_array( $args[ 'extra' ] ) ? $args[ 'extra' ] : null;
		$param = '';

		if ( null !== $extra ) {
			foreach ( $extra as $p_key => $p_value ) {
				$param .= $p_key . '="' . $p_value . '"';
			}
		}

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%5$s>%4$s</textarea>', $size, $args[ 'section' ], $args[ 'id' ], stripslashes( $value ), $param );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a HTML for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function html_break( array $args ) {
		static $counter = 0;

		if ( isset( $args[ 'desc' ] ) ) {
			printf( '<div class="section-%s-%d"><h4>%s</h4></div><hr>', $args[ 'section' ], $counter, $args[ 'desc' ] );
			$counter ++;
		}
	}

	/**
	 * Displays raw HTML for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function raw( array $args ) {

		$html = isset( $args[ 'desc' ] ) ? sprintf( '<div class="raw-html">%s</div>', $args[ 'desc' ] ) : '';

		echo $html;
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function wysiwyg( array $args ) {

		$value = wpautop( CL_Common::get_option( $args[ 'id' ], $args[ 'section' ], $args[ 'default' ] ) );
		$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : '500px';

		$html = sprintf( '<div style="width: %s">', $size );

		ob_start();
		wp_editor( $value, $args[ 'section' ] . '[' . $args[ 'id' ] . ']', array(
			'teeny'         => true,
			'textarea_rows' => 10,
		) );

		$html .= ob_get_clean();
		$html .= '</div>';
		$html .= $this->get_field_description( $args );

		echo $html;
	}
}
