<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use PassyCo\CustomLogin\Admin\AdminNotices;

// Return if we don't have an exception
if ( ! isset( $_exception ) || ! ( $_exception instanceof Exception ) ) {
    return;
}

/** @var Exception $_exception */
list( $message, $class ) = explode( AdminNotices::NOTICE_PREFIX, $_exception->getMessage() );
$class = ! empty( $class ) ? $class : 'update-nag'; ?>
<div class="notice <?php echo sanitize_html_class( $class ); ?> is-dismissible">
    <p><?php echo wp_kses_post( $message ); ?></p>
</div>
