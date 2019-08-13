<?php

namespace App\Providers;

use Laravel\Dusk\Browser;
use Laravel\Dusk\DuskServiceProvider as DuskServiceProviderBase;

class DuskServiceProvider extends DuskServiceProviderBase {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        parent::boot(); // needed to allow original DuskServiceProvider to do it's thing

        Browser::macro('scrollToElement', function ($element = null) {
            $this->script([
                "document.querySelector('$element').scrollIntoView();",
                "window.scrollBy(0, -52);"  // adjust for height of navbar
            ]);

            return $this;
        });

        Browser::macro('getBrowserLocale', function(){
            $browser_locale = $this->script('return (navigator.language || navigator.languages[0] || navigator.browserLanguage)');
            return $browser_locale[0];
        });

        Browser::macro('getDateFromLocale', function($locale, $date){
            $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
            return $formatter->format(new \DateTime($date));
        });

        Browser::macro('getBrowserLocaleDate', function(){
            $browser_locale_date = $this->script('return new Date().toLocaleDateString()');
            return $browser_locale_date[0];
        });

        Browser::macro('processLocaleDateForTyping', function($locale_date){
            $locale_date_components = [];
            if(strpos($locale_date, '/') !== false){
                $locale_date_components = explode('/', $locale_date);
            }elseif(strpos($locale_date, '-') !== false){
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
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws \Exception
     */
    public function register(){
        parent::register();  // needed to allow original DuskServiceProvider to do it's thing
    }

}