<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Admin_Notices
 */
class CL_Admin_Notices {

	public static $exception;

	/**
	 * @var string
	 */
	const ERROR_CODE = '~notice-error';
	const SUCCESS_CODE = '~notice-success';
	const NOTICE_CODE = '~notice-warning';

	/**
	 * CL_Admin_Notices constructor.
	 *      If $e is an instance of \Exception call our view method.
	 *
	 * @param null|Exception $e
	 */
	public function __construct( $e = null ) {

		if ( ! is_null( $e ) && $e instanceof Exception ) {
			self::$exception = $e;
			$this->render_view();
		}
	}

	/**
	 * The HTML view of the notices.
	 */
	private function render_view() {
		include CUSTOM_LOGIN_DIR . "views/notices/notice.php";
	}
}
