<?php

namespace PassyCo\CustomLogin;

/**
 * Class AbstractStaticLogin
 *
 * @package PassyCo\CustomLogin
 */
abstract class AbstractStaticLogin extends AbstractLogin {

    /** @var string $dir */
    protected static $dir;

    /** @var string $url */
    protected static $url;

    /**
     * AbstractStaticLogin constructor.
     *
     * @param CustomLogin $custom_login
     */
    public function __construct( CustomLogin $custom_login ) {
        parent::__construct( $custom_login );
        $this->setDir( $custom_login );
        $this->setUrl( $custom_login );
    }

    /**
     * @return string
     */
    public static function getDir() {
        return self::$dir;
    }

    /**
     * @param CustomLogin $custom_login
     */
    protected function setDir( CustomLogin $custom_login ) {
        self::$dir = $custom_login->getDirectory();
    }

    /**
     * @return string
     */
    public static function getUrl() {
        return self::$url;
    }

    /**
     * @param CustomLogin $custom_login
     */
    protected function setUrl( CustomLogin $custom_login ) {
        self::$url = $custom_login->getUrl();
    }

}
