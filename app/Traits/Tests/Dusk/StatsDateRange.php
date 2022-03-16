<?php

namespace App\Traits\Tests\Dusk;

use Exception;
use InvalidArgumentException;
use Laravel\Dusk\Browser;

trait StatsDateRange {

    private static $SELECTOR_DATERANGE_START = '#%s-start-date';
    private static $SELECTOR_DATERANGE_END = '#%s-end-date';
    private static $SELECTOR_LABEL_DATERANGE = "label[for='%s']";

    private static $LABEL_DATERANGE_START = "Start Date:";
    private static $LABEL_DATERANGE_END = "End Date:";

    protected $date_range_chart_name = '';

    private function hasDateRangeChartNameBeenSet(){
        if(!$this->date_range_chart_name){
            throw new Exception("variable \$date_range_chart_name has not been set");
        }
    }

    public function getChartDateStartRangeId():string{
        $this->hasDateRangeChartNameBeenSet();
        return sprintf(self::$SELECTOR_DATERANGE_START, $this->date_range_chart_name);
    }

    public function getChartDateEndRangeId():string{
        $this->hasDateRangeChartNameBeenSet();
        return sprintf(self::$SELECTOR_DATERANGE_END, $this->date_range_chart_name);
    }

    public function getChartDateStartRangeLabel():string{
        return sprintf(self::$SELECTOR_LABEL_DATERANGE, ltrim($this->getChartDateStartRangeId(), '#'));
    }

    public function getChartDateEndRangeLabel():string{
        return sprintf(self::$SELECTOR_LABEL_DATERANGE, ltrim($this->getChartDateEndRangeId(), '#'));
    }

    public function setDateRangeDate(Browser $browser, string $start_or_end, string $date){
        switch($start_or_end){
            case 'start':
                $selector = $this->getChartDateStartRangeId();
                break;
            case 'end':
                $selector = $this->getChartDateEndRangeId();
                break;
            default:
                throw new InvalidArgumentException("[$start_or_end] is not valid");
        }

        // get locale date string from browser
        $locale = $browser->getBrowserLocale();
        $date_in_locale_format = $browser->getDateFromLocale($locale, $date);
        $browser_locale_date_for_typing = $browser->processLocaleDateForTyping($date_in_locale_format);

        $browser
            ->type($selector, $browser_locale_date_for_typing)
            ->assertValue($selector, $date);
    }

    public function assertDefaultStateDateRange(Browser $browser){
        $browser
            ->assertVisible($this->getChartDateStartRangeLabel())
            ->assertSeeIn($this->getChartDateStartRangeLabel(), self::$LABEL_DATERANGE_START)
            ->assertVisible($this->getChartDateStartRangeId())
            ->assertValue($this->getChartDateStartRangeId(), date('Y-m-01'))
            ->assertVisible($this->getChartDateEndRangeLabel())
            ->assertSeeIn($this->getChartDateEndRangeLabel(), self::$LABEL_DATERANGE_END)
            ->assertVisible($this->getChartDateEndRangeId())
            ->assertValue($this->getChartDateEndRangeId(), date('Y-m-t'));
    }

}
