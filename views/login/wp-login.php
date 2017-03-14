<?php

use PassyCo\CustomLogin\LoginCustomizer;

?>
<!-- Custom Login CSS -->
<style type="text/css">
    <?php
        LoginCustomizer::generate_css( '#site-title a', 'color', 'header_textcolor', '#' );
        LoginCustomizer::generate_css( 'body', 'background-color', 'background_color', '#' );
        LoginCustomizer::generate_css( 'a', 'color', 'link_textcolor' );
        ?>
</style>
<!-- /Custom Login CSS -->