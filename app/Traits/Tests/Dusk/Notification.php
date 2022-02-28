<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait Notification {

    use WaitTimes;

    protected static $NOTIFICATION_TYPE_ERROR = 'error';
    protected static $NOTIFICATION_TYPE_INFO = 'info';
    protected static $NOTIFICATION_TYPE_SUCCESS = 'success';
    protected static $NOTIFICATION_TYPE_WARNING = 'warning';

    private static $SELECTOR_NAVBAR = '.navbar';
    private static $SELECTOR_NOTIFICATION = '.snotifyToast';
    private static $NOTIFICATION_CLASS_INFO = "snotify-info";
    private static $NOTIFICATION_CLASS_ERROR = "snotify-error";
    private static $NOTIFICATION_CLASS_SUCCESS = "snotify-success";
    private static $NOTIFICATION_CLASS_WARNING = "snotify-warning";

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
            ->waitFor(self::$SELECTOR_NOTIFICATION, 1.5*self::$WAIT_SECONDS)
            ->with(self::$SELECTOR_NOTIFICATION, function(Browser $notification) use ($notification_class, $notification_message){
                Assert::assertStringContainsString(
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

    /**
     * @param Browser $browser
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function dismissNotification(Browser $browser){
        $browser
            ->click(self::$SELECTOR_NOTIFICATION)
            ->waitUntilMissing(self::$SELECTOR_NOTIFICATION, self::$WAIT_SECONDS);
    }

}
