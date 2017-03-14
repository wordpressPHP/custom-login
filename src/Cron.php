<?php

namespace PassyCo\CustomLogin;

/**
 * Class Cron
 *
 * @package PassyCo\CustomLogin
 */
class Cron implements WpHooksInterface {

    use HooksTrait;

    const DAILY_ID = 'custom_login_daily_scheduled_events';
    const WEEKLY_ID = 'custom_login_weekly_scheduled_events';

    /**
     * Add our actions and filters.
     *
     * @since 1.6
     */
    public function addHooks() {
        $this->addAction( 'wp', [ $this, 'triggerEvents' ] );
        $this->addFilter( 'cron_schedules', [ $this, 'addCronSchedules' ] );
    }

    /**
     * Schedules our events
     *
     * @access public
     * @since 1.6
     */
    protected function triggerEvents() {
        $this->doDailyEvents();
        $this->doWeeklyEvents();
    }

    /**
     * Registers new cron schedules
     *
     * @param array $schedules
     *
     * @return array
     */
    protected function addCronSchedules( array $schedules ) {
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display' => esc_attr__( 'Once Weekly', CustomLogin::DOMAIN ),
        ];

        return $schedules;
    }

    /**
     * Schedule daily events
     *
     * @since 1.6
     */
    private function doDailyEvents() {
        if ( ! wp_next_scheduled( self::DAILY_ID ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'daily', self::DAILY_ID );
        }
    }

    /**
     * Schedule weekly events
     *
     * @since 1.6
     */
    private function doWeeklyEvents() {
        if ( ! wp_next_scheduled( self::WEEKLY_ID ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'weekly', self::WEEKLY_ID );
        }
    }
}
