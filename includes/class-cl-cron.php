<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class CL_Cron
 */
class CL_Cron {

	const DAILY_ID = 'custom_login_daily_scheduled_events';
	const WEEKLY_ID = 'custom_login_weekly_scheduled_events';

	/**
	 * Add our actions and filters.
	 *
	 * @since 1.6
	 */
	public function add_hooks() {

		add_action( 'wp', array( $this, 'schedule_events' ) );
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
	}

	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since 1.6
	 * @return void
	 */
	public function schedule_events() {

		$this->daily_events();
		$this->weekly_events();
	}

	/**
	 * Registers new cron schedules
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function add_schedules( $schedules ) {

		// Adds once weekly to the existing schedules.
		$schedules[ 'weekly' ] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', Custom_Login_Bootstrap::DOMAIN ),
		);

		return $schedules;
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function daily_events() {

		if ( ! wp_next_scheduled( self::DAILY_ID ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', self::DAILY_ID );
		}
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 1.6
	 * @return void
	 */
	private function weekly_events() {

		if ( ! wp_next_scheduled( self::WEEKLY_ID ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', self::WEEKLY_ID );
		}
	}
}
