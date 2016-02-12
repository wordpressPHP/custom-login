<?php

use CL_Login_Customizer as Loginizer; ?>
<!-- Custom Login CSS -->
<style type="text/css">
	<?php Loginizer::generate_css('#site-title a', 'color', 'header_textcolor', '#'); ?>
	<?php Loginizer::generate_css('body', 'background-color', 'background_color', '#'); ?>
	<?php Loginizer::generate_css('a', 'color', 'link_textcolor'); ?>
</style>
<!-- /Custom Login CSS -->