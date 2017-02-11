<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * This is purely a base model that we can utilise to bring some useful helper methods
 * @package App
 */
class BaseModel extends Model{

    protected function serializeDate(DateTimeInterface $date){
        return $date->format(Carbon::ATOM);
    }

    public function __get($key){
        $value = parent::__get($key);
        // Makes sure all timestamps appear in the ATOM format
        if($value instanceof Carbon){
            $value = $value->toAtomString();
        }
        return $value;
    }

}
