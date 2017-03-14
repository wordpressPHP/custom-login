<?php

namespace PassyCo\CustomLogin;

/**
 * Class AbstractPlugin
 *
 * Based on https://github.com/johnpbloch/wordpress-dev
 *
 * @package PassyCo\CustomLogin
 */
abstract class AbstractPlugin implements PluginInterface {

    use HooksTrait;

    /** @var string */
    protected $directory;

    /** @var string $file */
    protected $file;

    /** @var string */
    protected $url;

    /** @var string $version */
    protected $version;

    /**
     * Run the plugins main initialization routine
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function run() {
        $this->addAction( 'plugins_loaded', 'pluginsLoaded' );

        return $this;
    }

    public function disable() {
        $this->removeAction( 'plugins_loaded', 'pluginsLoaded' );
    }

    /**
     * Get the plugin directory path
     *
     * @return string
     */
    public function getDirectory() {
        return $this->directory;
    }

    /**
     * Set the plugins directory path
     *
     * @param string $directory
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setDirectory( $directory ) {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get the plugins url
     *
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Set the plugins file
     *
     * @param string $file
     *
     * @return $this
     */
    public function setFile( $file ) {
        $this->file = $file;

        return $this;
    }

    /**
     * Get the plugins url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set the plugins url
     *
     * @param string $url
     *
     * @return PluginInterface Returns itself for easier method chaining
     */
    public function setUrl( $url ) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the plugins url
     *
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Set the plugins file
     *
     * @param string $version
     *
     * @return $this
     */
    public function setVersion( $version ) {
        $this->version = $version;

        return $this;
    }

    /**
     * @return void
     */
    protected abstract function pluginsLoaded();
}
