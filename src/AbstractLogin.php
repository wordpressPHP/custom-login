<?php

namespace PassyCo\CustomLogin;

/**
 * Class AbstractLogin
 *
 * @package PassyCo\CustomLogin
 */
abstract class AbstractLogin implements LoginInterface {

    /** @var CustomLogin $custom_login */
    private $custom_login;

    /**
     * AbstractLogin constructor.
     *
     * @param CustomLogin $custom_login
     */
    public function __construct( CustomLogin $custom_login ) {
        $this->setCustomLogin( $custom_login );
    }

    /**
     * @return CustomLogin
     */
    public function getCustomLogin() {
        return $this->custom_login;
    }

    /**
     * @param CustomLogin $custom_login
     */
    public function setCustomLogin( CustomLogin $custom_login ) {
        $this->custom_login = $custom_login;
    }
}
