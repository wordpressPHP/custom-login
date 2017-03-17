<?php

namespace PassyCo\CustomLogin\Api;

use PassyCo\CustomLogin\Common;

/**
 * Class FieldTypes
 *
 * @package PassyCo\CustomLogin\Api
 */
class FieldTypes {

    /**
     * @var SettingsApi $settings_api
     */
    private $settings_api;

    /**
     * @var string $id
     */
    private $id;

    /**
     * FieldTypes constructor.
     *
     * @param SettingsApi $settings_api
     */
    public function __construct( SettingsApi $settings_api ) {
        $this->settings_api = $settings_api;
        $this->id           = $settings_api::SETTING_ID;
    }

    /**
     * Get field description for display
     *
     * @param array $args settings field args
     *
     * @return string
     */
    private function get_field_description( array $args ) {
        return ! empty( $args['desc'] ) ?
            sprintf( '<p class="description">%s</p>', $args['desc'] ) : '';
    }

    /**
     * Helper function to return extra parameter as a string.
     *
     * @param array $args Default incoming arguments
     *
     * @return string
     */
    private function get_extra_field_params( array $args = [] ) {
        $return     = '';
        $attributes = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ?
            $args['attributes'] : null;

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
        $value = esc_attr( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = ! empty( $args['size'] ) ? $args['size'] : 'regular';
        $type  = ! empty( $args['type'] ) ? $args['type'] : 'text';
        $class = ! empty( $args['class'] ) ? $args['class'] : '';

        $html = sprintf( '<input type="%1$s" class="%2$s-text %3$s" id="%4$s[%5$s]" name="%4$s[%5$s]" value="%6$s"%7$s>',
            $type,
            $size,
            $class,
            $args['section'],
            $args['id'],
            $value,
            $this->get_extra_field_params( $args )
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
        $args['type']               = 'number';
        $args['attributes']['step'] = 'any';
        $this->text( $args );
    }

    /**
     * Displays a password field for a settings field
     *
     * @param array $args settings field args
     */
    public function password( array $args ) {
        $args['type'] = 'password';
        $this->text( $args );
    }

    /**
     * Displays a file upload field for a settings field
     *
     * @param array $args settings field args
     */
    public function file( array $args ) {
        $value = esc_attr( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';

        /* Localize the array */
        $this->settings_api->add_localize_array(
            'file',
            [
                'id' => $args['id'],
                'section' => $args['section'],
            ]
        );

        $html = '<p class="control is-grouped">';
        $html .= sprintf( '<input type="text" class="input %1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s">', $size, $args['section'], $args['id'], $value );
        $html .= '<input type="button" class="button ' . $args['id'] . '-browse" id="' . $id . '_button" value="Browse" style="margin-left:5px" >';
        $html .= '<input type="button" class="button ' . $args['id'] . '-clear" id="' . $id . '_clear" value="Clear" style="margin-left:5px" >';
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
        $value = Common::getOption( $args['id'], $args['section'], $args['default'] );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = '<ul style="margin-top:0">';

        if ( is_array( $value ) ) {
            foreach ( $value as $key => $val ) {
                $html .= '<li>';
                $html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s][]" value="%4$s" data-key="%5$s">', $size, $args['section'], $args['id'], esc_attr( $val ), $key );
                $html .= sprintf( '<a href="#" class="button dodelete-%1$s[%2$s]">-</a>', $args['section'], $args['id'] );
                $html .= '</li>';
            }
        } else {
            $html .= '<li>';
            $html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s][]" value="%4$s" data-key="0" data-array="false">', $size, $args['section'], $args['id'], esc_attr( $value ) );
            $html .= sprintf( '<a href="#" class="button dodelete-%1$s[%2$s]">-</a>', $args['section'], $args['id'] );
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= sprintf( '<a href="#" class="button docopy-%1$s[%2$s]">+</a>', $args['section'], $args['id'] );
        $html .= $this->get_field_description( $args );

        echo $html;
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array $args settings field args
     */
    public function colorpicker( array $args ) {
        $value = esc_attr( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'small';
        $param = $this->get_extra_field_params( $args );

        /* Enqueue our required script */
        $this->settings_api->addScriptsArray( 'wp-color-picker-alpha' );

        /* Localize the array */
        $this->settings_api->add_localize_array(
            'colorpicker',
            [
                'id' => $args['id'],
                'section' => $args['section'],
            ]
        );

        /* Color */
        $html = sprintf(
            '<input type="text" class="%1$s-text color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" %5$s>',
            $size,
            $args['section'],
            $args['id'],
            $value,
            $param
        );
        $html .= $this->get_field_description( $args );

        echo $html;
    }

    public function alphacolor( array $args ) {
        $this->colorpicker( $args );
    }

    /**
     * Displays a checkbox for a settings field
     *
     * @param array $args settings field args
     */
    public function checkbox( array $args ) {
        $value = esc_attr( Common::getOption( $args['id'], $args['section'], $args['default'] ) );

        $html = '<div class="checkbox-wrap">';
        $html .= sprintf( '<label class="cl-control checkbox" for="%1$s[%2$s]">', $args['section'], $args['id'] );
        $html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s>',
            $args['section'],
            $args['id'],
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
        $value = Common::getOption( $args['id'], $args['section'], $args['default'] );

        $html = '<div class="checkbox-wrap">';
        $html .= '<ul>';
        foreach ( $args['options'] as $key => $label ) {
            $checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
            $html .= '<li>';
            $html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s >', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
            $html .= sprintf( '<label for="%1$s[%2$s][%4$s]" title="%3$s"> %3$s</label>', $args['section'], $args['id'], $label, $key );
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
        $value = Common::getOption( $args['id'], $args['section'], $args['default'] );

        $html = '<div class="radio-wrap">';
        $html .= '<ul>';
        foreach ( $args['options'] as $key => $label ) {
            $html .= '<li>';
            $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s >', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '<label for="%1$s[%2$s][%4$s]" title="%3$s"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
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
        $value = esc_attr( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

        /* Localize the array */
        $this->settings_api->add_localize_array(
            'select',
            [
                'id' => $args['id'],
                'section' => $args['section'],
            ]
        );

        $html = '<p><span class="select">';
        $html .= sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
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
        $value = esc_textarea( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $param = $this->get_extra_field_params( $args );

        $html = sprintf(
            '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%5$s>%4$s</textarea>',
            $size,
            $args['section'],
            $args['id'],
            stripslashes( $value ),
            $param
        );
        $html .= $this->get_field_description( $args );

        echo $html;
    }

    /**
     * Displays a HTML for a settings field
     *
     * @param array $args settings field args
     */
    public function html_break( array $args ) {
        static $counter;

        if ( isset( $args['desc'] ) ) {

            printf( '<div class="section-%s-%d field-type-html-break">%s</div><hr>', $args['section'], $counter, $args['desc'] );
            $counter ++;
        }
    }

    /**
     * Displays raw HTML for a settings field
     *
     * @param array $args settings field args
     */
    public function raw( array $args ) {
        echo isset( $args['desc'] ) ? sprintf( '<div class="raw-html">%s</div>', $args['desc'] ) :
            '';
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array $args settings field args
     */
    public function wysiwyg( array $args ) {
        $value = wpautop( Common::getOption( $args['id'], $args['section'], $args['default'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

        $html = sprintf( '<div style="width: %s">', $size );

        ob_start();
        wp_editor( $value, $args['section'] . '[' . $args['id'] . ']', [
            'teeny' => true,
            'textarea_rows' => 10,
        ] );

        $html .= ob_get_clean();
        $html .= '</div>';
        $html .= $this->get_field_description( $args );

        echo $html;
    }
}
