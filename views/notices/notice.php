<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

$exception = CL_Admin_Notices::$exception;

list( $message, $class ) = explode( '~', $exception->getMessage() );
$class = ! empty( $class ) ? $class : 'update-nag'; ?>
<div class="notice <?php echo sanitize_html_class( $class ); ?> is-dismissible"><p><?php echo $message; ?></p></div>