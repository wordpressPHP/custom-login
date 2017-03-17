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

namespace PassyCo;

use PassyCo\CustomLogin\CustomLogin;
use PassyCo\CustomLogin\Psr4Autoloader;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/src/Psr4Autoloader.php';

( new Psr4Autoloader() )->addNamespace( 'PassyCo\\CustomLogin', __DIR__ . '/src' )->register();

( new CustomLogin() )
    ->setDirectory( plugin_dir_path( __FILE__ ) )
    ->setFile( __FILE__ )
    ->setUrl( plugins_url( '', __FILE__ ) )
    ->setVersion( '4.0.0-20170302' )
    ->run();
