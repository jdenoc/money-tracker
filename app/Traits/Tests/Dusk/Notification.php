<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait Notification {

    use WaitTimes;

    private static $NOTIFICATION_TYPE_ERROR = 'error';
    private static $NOTIFICATION_TYPE_INFO = 'info';
    private static $NOTIFICATION_TYPE_SUCCESS = 'success';
    private static $NOTIFICATION_TYPE_WARNING = 'warning';

    private static $SELECTOR_NAVBAR = '.navbar';
    private static $SELECTOR_NOTIFICATION = '.snotifyToast';
    private static $CLASS_INFO = "snotify-info";
    private static $CLASS_ERROR = "snotify-error";
    private static $CLASS_SUCCESS = "snotify-success";
    private static $CLASS_WARNING = "snotify-warning";

    public function assertNotificationContents(Browser $browser, $notification_type, $notification_message){
        switch($notification_type){
            case self::$NOTIFICATION_TYPE_ERROR:
                $notification_class = self::$CLASS_ERROR;
                break;
            case self::$NOTIFICATION_TYPE_INFO:
            default:
                $notification_class = self::$CLASS_INFO;
                break;
            case self::$NOTIFICATION_TYPE_SUCCESS:
                $notification_class = self::$CLASS_SUCCESS;
                break;
            case self::$NOTIFICATION_TYPE_WARNING:
                $notification_class = self::$CLASS_WARNING;
                break;
        }

        $browser
            ->waitFor(self::$SELECTOR_NOTIFICATION, 1.5*self::$WAIT_SECONDS)
            ->with(self::$SELECTOR_NOTIFICATION, function(Browser $notification) use ($notification_class, $notification_message){
                Assert::assertContains(
                    $notification_class,
                    $notification->attribute('', 'class')
                );

                $notification->assertSeeIn('', $notification_message);
            })
            // Selenium has issues on some tests.
            // We need to mouse over the navbar to make sure that notification continues its progress of dismissal.
            ->mouseover(self::$SELECTOR_NAVBAR)
            ->waitUntilMissing($notification_class, 1.5*self::$WAIT_SECONDS)
            ->pause(self::$WAIT_QUARTER_SECONDS_IN_MILLISECONDS);
    }

}
