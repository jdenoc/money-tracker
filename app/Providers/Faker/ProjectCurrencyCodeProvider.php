<?php

namespace App\Providers\Faker;

use Faker\Provider\Base as FakerProviderBase;

class ProjectCurrencyCodeProvider extends FakerProviderBase {

    /**
     * @link https://en.wikipedia.org/wiki/ISO_4217
     */
    protected static $currencyCode = array(
        'CAD', 'EUR', 'GBP', 'USD'
    );

    public static function currencyCode(){
        return static::randomElement(static::$currencyCode);
    }

}