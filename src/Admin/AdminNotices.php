<?php

namespace PassyCo\CustomLogin\Admin;

use Exception;
use PassyCo\CustomLogin\AbstractStaticLogin;
use PassyCo\CustomLogin\Common;
use PassyCo\CustomLogin\CustomLogin;

/**
 * Class AdminNotices
 *
 * @package PassyCo\CustomLogin\Admin
 */
class AdminNotices extends AbstractStaticLogin {

    const NOTICE_PREFIX = '~';
    const ERROR_CODE = self::NOTICE_PREFIX . 'notice-error';
    const SUCCESS_CODE = self::NOTICE_PREFIX . 'notice-success';
    const NOTICE_CODE = self::NOTICE_PREFIX . 'notice-warning';

    /**
     * AdminNotices constructor.
     *
     * @param CustomLogin $custom_login
     */
    public function __construct( CustomLogin $custom_login ) {
        parent::__construct( $custom_login );
    }

    /**
     * The HTML view of the notices.
     *
     * @param Exception $exception
     */
    public static function renderNotice( Exception $exception ) {
        Common::renderView( 'notices/notice', $exception );
    }
}
