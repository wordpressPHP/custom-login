<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use PassyCo\CustomLogin\Admin\AdminNotices;

// Return if we don't have the correct object
if ( ! isset( $object ) || ! ( $object instanceof Exception ) ) {
    return;
}

/** @var Exception $object */
list( $message, $class ) = explode( AdminNotices::NOTICE_PREFIX, $object->getMessage() );
$class = ! empty( $class ) ? $class : 'update-nag'; ?>
<div class="notice <?php echo sanitize_html_class( $class ); ?> is-dismissible">
    <p><?php echo wp_kses_post( $message ); ?></p>
</div>
