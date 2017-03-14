<?php

namespace PassyCo\CustomLogin;

/**
 * Interface PluginInterface
 *
 * Based on https://github.com/johnpbloch/wordpress-dev
 *
 * @package PassyCo\CustomLogin
 */
interface PluginInterface {

    /**
     * Run the plugins main initialization routine
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function run();

    /**
     * Get the plugin directory path
     *
     * @return string
     */
    public function getDirectory();

    /**
     * Set the plugins directory path
     *
     * @param string $directory
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setDirectory( $directory );

    /**
     * Get the plugins url
     *
     * @return string
     */
    public function getFile();

    /**
     * Set the plugins file
     *
     * @param string $file
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setFile( $file );

    /**
     * Get the plugins url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set the plugins url
     *
     * @param string $url
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setUrl( $url );

    /**
     * Get the plugins version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Set the plugins version
     *
     * @param string $version
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setVersion( $version );
}
