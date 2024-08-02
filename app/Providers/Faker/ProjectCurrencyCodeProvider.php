<?php

namespace App\Providers\Faker;

use App\Helpers\CurrencyHelper;
use Faker\Provider\Base as FakerProviderBase;

class ProjectCurrencyCodeProvider extends FakerProviderBase {

    public static function currencyCode() {
        $currency_codes = CurrencyHelper::getCodesAsArray();
        return static::randomElement($currency_codes);
    }

}
