<?php

$exception = CL_Admin_Notices::$exception;
list( $message, $class ) = explode( '|',  $exception->getMessage() );
$class = ! empty( $class ) ? $class : 'update-nag'; ?>
<div class="<?php echo sanitize_html_class( $class ); ?>"><p><?php echo $message; ?></p></div>