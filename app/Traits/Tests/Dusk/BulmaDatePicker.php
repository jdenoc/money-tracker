<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait BulmaDatePicker {

    private static $SELECTOR_DATEPICKER = '.datetimepicker-dummy';
    private static $SELECTOR_DATEPICKER_DATE_START = '.datetimepicker-dummy-wrapper .datetimepicker-dummy-input:nth-child(1)';
    private static $SELECTOR_DATEPICKER_DATE_END = '.datetimepicker-dummy-wrapper .datetimepicker-dummy-input:nth-child(2)';
    private static $SELECTOR_DATEPICKER_CALENDAR = '.datetimepicker-wrapper .datetimepicker';

    public function assertDefaultStateBulmaDatePicker(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_DATEPICKER)
            ->with(self::$SELECTOR_DATEPICKER, function(Browser $datepicker_input){
                $datepicker_input
                ->assertVisible(self::$SELECTOR_DATEPICKER_DATE_START)
                ->assertValue(self::$SELECTOR_DATEPICKER_DATE_START, date('Y-m-01'))
                ->assertVisible(self::$SELECTOR_DATEPICKER_DATE_END)
                ->assertValue(self::$SELECTOR_DATEPICKER_DATE_END, date('Y-m-t'));
            })
            ->assertMissing(self::$SELECTOR_DATEPICKER_CALENDAR);
    }

    /**
     * @param Browser $browser
     * @param string $start_date
     * @param string $end_date
     */
    public function setDateRange(Browser $browser, $start_date, $end_date){
        $browser
            ->with(self::$SELECTOR_DATEPICKER, function(Browser $datepicker_input){
                $datepicker_input->click(self::$SELECTOR_DATEPICKER_DATE_START);
            })
            ->assertVisible(self::$SELECTOR_DATEPICKER_CALENDAR)

            ->with(self::$SELECTOR_DATEPICKER_CALENDAR, function(Browser $datepicker_calendar) use ($start_date, $end_date){
                // set start date
                $this->clickYear($datepicker_calendar, $start_date);
                $this->clickMonth($datepicker_calendar, $start_date);
                $this->clickDate($datepicker_calendar, $start_date);
                // set end date
                $this->clickYear($datepicker_calendar, $end_date);
                $this->clickMonth($datepicker_calendar, $end_date);
                $this->clickDate($datepicker_calendar, $end_date);
            })
            ->assertMissing(self::$SELECTOR_DATEPICKER_CALENDAR)
            ->assertValue(self::$SELECTOR_DATEPICKER_DATE_START, $start_date)
            ->assertValue(self::$SELECTOR_DATEPICKER_DATE_END, $end_date);
    }

    /**
     * @param Browser $datepicker
     * @param string $new_date
     */
    private function clickYear(Browser $datepicker, string $new_date){
        $selector_datepicker_nav_year = '.datepicker-nav .datepicker-nav-year';
        $selector_datepicker_body_years = '.datepicker-body .datepicker-years.is-active';
        $pattern_datepicker_body_years_year = ".datepicker-body .datepicker-years.is-active .datepicker-year[data-year='%s']";

        $datepicker
            ->click($selector_datepicker_nav_year)
            ->assertVisible($selector_datepicker_body_years)
            ->click(sprintf($pattern_datepicker_body_years_year, date("Y", strtotime($new_date))))
            ->assertMissing($selector_datepicker_body_years);
    }

    /**
     * @param Browser $datepicker
     * @param string $new_date
     */
    private function clickMonth(Browser $datepicker, string $new_date){
        $selector_datepicker_nav_month = '.datepicker-nav .datepicker-nav-month';
        $selector_datepicker_body_months = '.datepicker-body .datepicker-months.is-active';
        $pattern_datepicker_body_months_month = ".datepicker-body .datepicker-months.is-active .datepicker-month[data-month='%s']";

        $datepicker
            ->click($selector_datepicker_nav_month)
            ->assertVisible($selector_datepicker_body_months)
            ->click(sprintf($pattern_datepicker_body_months_month, date("m", strtotime($new_date))))
            ->assertMissing($selector_datepicker_body_months);
    }

    /**
     * @param Browser $datepicker
     * @param string $new_date
     */
    private function clickDate(Browser $datepicker, string $new_date){
        $selector_datepicker_body_dates = '.datepicker-body .datepicker-dates.is-active';
        $pattern_datepicker_body_dates_date = ".datepicker-body .datepicker-dates.is-active .datepicker-days .datepicker-date[data-date^='%s']";

        $datepicker
            ->assertVisible($selector_datepicker_body_dates)
            ->click(sprintf($pattern_datepicker_body_dates_date, date("D M d Y", strtotime($new_date))));
    }

}
