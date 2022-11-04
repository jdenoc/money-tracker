<?php

namespace App\Helpers;

use App\Models\Currency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CurrencyHelper {

    /**
     * @var string
     */
    private static $CURRENCY_FILE_PATH = "json/currency.json";

    /**
     * @var Collection|null
     */
    private static $_currencies = null;

    /**
     * @return Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function fetchCurrencies() {
        $currency_json = Storage::get(self::$CURRENCY_FILE_PATH);
        $raw_currency_data = json_decode($currency_json, true);
        $currency_collection = collect();
        foreach ($raw_currency_data as $currency_data) {
            $currency_object = new Currency($currency_data);
            $currency_collection->push($currency_object);
        }

        self::$_currencies = $currency_collection;
        return self::$_currencies;
    }

    /**
     * Currency codes are based on the ISO4217 standard
     * @link https://en.wikipedia.org/wiki/ISO_4217
     */
    public static function getCodesAsArray() {
        if (is_null(self::$_currencies)) {
            $currencies = self::fetchCurrencies();
        } else {
            $currencies = self::$_currencies;
        }
        return $currencies->pluck('code')->all();
    }

    public static function getCurrencyHtmlFromCode(string $code): string {
        if (is_null(self::$_currencies)) {
            $currencies = self::fetchCurrencies();
        } else {
            $currencies = self::$_currencies;
        }
        return $currencies->where('code', $code)->first()->html;
    }

    /**
     * This returns a Currency object containing default values outlined in resources/js/currency.js
     * @return Currency
     */
    public static function getCurrencyDefaults() {
        return new Currency([
            "label"=>"dollarUs",
            "code"=>"USD",
            "class"=>"fas fa-dollar-sign",
            "html"=>"&dollar;"
        ]);
    }

    public static function convertCurrencyHtmlToCharacter($html): string {
        return html_entity_decode($html, ENT_HTML5);
    }

}
