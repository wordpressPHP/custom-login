<?php
//@formatter:off
/**
 * Custom Login
 *
 * @package     CustomLogin
 * @author      Austin Passy
 * @copyright   2012 - 2016 Frosty Media
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Custom Login
 * Plugin URI: https://frosty.media/plugins/custom-login
 * Description: A simple way to customize your WordPress <code>/wp-login.php</code> screen! A <a href="https://frosty.media/?ref=wp-admin/plugins.php">Frosty Media</a> plugin.
 * Version: 4.0.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: custom-login
 * GitHub Plugin URI: https://github.com/thefrosty/custom-login
 * GitHub Branch: dev
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//@formatter:on

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-custom-login.php';

defined( 'CUSTOM_LOGIN_FILE' ) || define( 'CUSTOM_LOGIN_FILE', __FILE__ );
defined( 'CUSTOM_LOGIN_VERSION' ) || define( 'CUSTOM_LOGIN_VERSION', '4.0.0-20160828' );

new Custom_Login_Bootstrap( CUSTOM_LOGIN_FILE, CUSTOM_LOGIN_VERSION );
