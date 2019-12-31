<?php

namespace App\Helpers;

use App\Currency;
use Illuminate\Support\Collection;

class CurrencyHelper {

    private static $_file_path = "json/currency.json";

    /**
     * @var Collection|null
     */
    private static $_currencies = null;

    /**
     * @return Collection
     */
    public static function fetchCurrencies(){
        $currency_json = \Storage::get(self::$_file_path);
        $raw_currency_data = json_decode($currency_json, true);
        $currency_collection = collect();
        foreach($raw_currency_data as $currency_data){
            $currency_object = new Currency($currency_data);
            $currency_collection->push($currency_object);
        }

        self::$_currencies =$currency_collection;
        return self::$_currencies;
    }

    /**
     * Currency codes are based on the ISO4217 standard
     * @link https://en.wikipedia.org/wiki/ISO_4217
     */
    public static function getCodesAsArray(){
        if(is_null(self::$_currencies)){
            $currencies = self::fetchCurrencies();
        } else {
            $currencies = self::$_currencies;
        }
        return $currencies->pluck('code')->all();
    }

}