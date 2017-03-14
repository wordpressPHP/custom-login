<?php

list( $id, $title, $content, $group ) = explode( '|', $args ); ?>
<div class="metabox-holder<?php echo ! empty( $group ) ? ' group' : ''; ?>" id="<?php echo $id; ?>">
	<div class="postbox">
		<h3><?php echo esc_html( $title ); ?></h3>
		<div class="inside"><?php echo wp_kses_post( $content ); ?></div>
	</div>
</div>
