<?php

/**
 * Class CL_Admin_Notices
 */
class CL_Admin_Notices {

	public static $exception = null;

	/**
	 * @var string
	 */
	const ERROR_CODE = '|error';
	const UPDATED_CODE = '|updated';
	const NOTICE_CODE = '|update-nag';

	/**
	 * Notices constructor.
	 *
	 * @param null|Exception $e
	 */
	public function __construct( $e = null ) {

		if ( $e instanceof Exception ) {
			self::$exception = $e;
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 *
	 */
	public function admin_notices() {
		include CUSTOM_LOGIN_DIR . "/views/notices/notice.php";
	}
}
