<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait Notification {

    use WaitTimes;

    protected static $NOTIFICATION_TYPE_ERROR = 'error';
    protected static $NOTIFICATION_TYPE_INFO = 'info';
    protected static $NOTIFICATION_TYPE_SUCCESS = 'success';
    protected static $NOTIFICATION_TYPE_WARNING = 'warning';

    private static $SELECTOR_NAVBAR = '.navbar';
    private static $SELECTOR_NOTIFICATION_GROUP = '.vue-notification-group';
    private static $SELECTOR_NOTIFICATION = '.vue-notification';
    private static $NOTIFICATION_CLASS_INFO = ".info";
    private static $NOTIFICATION_CLASS_ERROR = ".error";
    private static $NOTIFICATION_CLASS_SUCCESS = ".success";
    private static $NOTIFICATION_CLASS_WARNING = ".warn";

    /**
     * @param Browser $browser
     * @param string $notification_type
     * @param string $notification_message
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function assertNotificationContents(Browser $browser, string $notification_type, string $notification_message){
        switch($notification_type){
            case self::$NOTIFICATION_TYPE_ERROR:
                $notification_class = self::$NOTIFICATION_CLASS_ERROR;
                break;
            case self::$NOTIFICATION_TYPE_INFO:
            default:
                $notification_class = self::$NOTIFICATION_CLASS_INFO;
                break;
            case self::$NOTIFICATION_TYPE_SUCCESS:
                $notification_class = self::$NOTIFICATION_CLASS_SUCCESS;
                break;
            case self::$NOTIFICATION_TYPE_WARNING:
                $notification_class = self::$NOTIFICATION_CLASS_WARNING;
                break;
        }

        $browser
            ->waitFor(self::$SELECTOR_NOTIFICATION.$notification_class, 1.5*self::$WAIT_SECONDS)
            ->assertSeeIn(self::$SELECTOR_NOTIFICATION_GROUP, $notification_message)
            // Selenium has issues on some tests.
            // We need to mouse over the navbar to make sure that notification continues its progress of dismissal.
            ->mouseover(self::$SELECTOR_NAVBAR)
            ->pause(self::$WAIT_QUARTER_SECONDS_IN_MILLISECONDS);
    }

}
