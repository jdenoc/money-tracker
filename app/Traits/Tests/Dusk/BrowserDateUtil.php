<?php

namespace App\Traits\Tests\Dusk;

use IntlDateFormatter;
use Laravel\Dusk\Browser;

trait BrowserDateUtil {

    public function getBrowserLocale(Browser $browser){
        $browser_locale = $browser->script('return (navigator.language || navigator.languages[0] || navigator.browserLanguage)');
        return $browser_locale[0];
    }

    public function getDateFromLocale(string $locale, string $date){
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
        return $formatter->format(new \DateTime($date));
    }

    public function getBrowserLocaleDate(Browser $browser){
        $browser_locale_date = $browser->script('return new Date().toLocaleDateString()');
        return $browser_locale_date[0];
    }

    public function processLocaleDateForTyping(string $locale_date){
        $locale_date_components = [];
        if(str_contains($locale_date, '/')){
            $locale_date_components = explode('/', $locale_date);
        }elseif(str_contains($locale_date, '-')){
            $locale_date_components = explode('-', $locale_date);
        }

        // we're assuming the year in the date provided will always be the last component
        $year = end($locale_date_components);
        if(strlen($year) == 2){    // this is assuming the year is the last component of the date
            if($year >= 70 && $year <= 99){   // should never be a value before 1970
                $locale_date_components[count($locale_date_components)-1] = "19".$year;
            } else {
                $locale_date_components[count($locale_date_components)-1] = "20".$year;
            }
        }
        foreach($locale_date_components as $key=>$date_component){
            $locale_date_components[$key] = ($date_component < 10) ? '0'.$date_component : $date_component;
        }
        return implode('', $locale_date_components);
    }

}
