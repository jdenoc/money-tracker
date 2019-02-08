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
            $this->script("$('html, body').animate({ scrollTop: $('$element').offset().top }, 0);");

            return $this;
        });

        Browser::macro('getBrowserLocale', function(){
            $browser_locale = $this->script('return (navigator.language || navigator.languages[0] || navigator.browserLanguage)');
            return $browser_locale[0];
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