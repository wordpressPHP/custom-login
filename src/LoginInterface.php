<?php

namespace PassyCo\CustomLogin;

/**
 * Interface LoginInterface
 *
 * @package PassyCo\CustomLogin
 */
interface LoginInterface {

    /**
     * @return CustomLogin
     */
    public function getCustomLogin();

    /**
     * @param CustomLogin $custom_login
     */
    public function setCustomLogin( CustomLogin $custom_login );
}
