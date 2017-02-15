<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseModel
 * This is purely a base model that we can utilise to bring some useful helper methods
 * @package App
 */
class BaseModel extends Model{

    /**
     * @return string
     */
    public static function getTableName(){
        return with(new static)->getTable();
    }

    /**
     * Retrieves enum values from a provided database table column
     * Adapted from here: http://stackoverflow.com/a/26992280
     * @param string $column
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function get_enum_values($column){
        $column_types = DB::select("SHOW COLUMNS FROM ".self::getTableName()." WHERE Field='".$column."'");
        if(empty($column_types) || !is_array($column_types)){
            throw new \InvalidArgumentException(self::getTableName().'.'.$column.' does not exist');
        }

        $column_type = $column_types[0];
        preg_match('/^enum\((.*)\)$/', $column_type->Type, $matches);

        $values = [];
        if(isset($matches[1])){
            $enum_values = explode(',', $matches[1]);
            if(is_array($enum_values) && !empty($enum_values)){
                foreach($enum_values as $enum_value){
                    $values[] = trim($enum_value, "'");
                }
            }
        }
        return $values;
    }

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
