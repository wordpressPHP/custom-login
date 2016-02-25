<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Customize_Alpha_Color_Control
 */
class CL_Customize_Alpha_Color_Control extends WP_Customize_Color_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'alphacolor';
	public $palette = true;
	public $default = 'rgba(0,0,0,100)';

	protected function render() {
		$id    = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'customize-control customize-control-' . $this->type; ?>
		<li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php $this->render_content(); ?>
		</li>
		<?php
	}

	public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<input type="text" data-palette="<?php echo $this->palette; ?>"
			       data-default-color="<?php echo $this->default; ?>" value="<?php echo intval( $this->value() ); ?>"
			       class="cl-alpha-color-control" <?php $this->link(); ?> />
		</label>
		<?php
	}

}
