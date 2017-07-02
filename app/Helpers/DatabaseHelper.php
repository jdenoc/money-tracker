<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper extends DB {

    /**
     * Retrieves enum values from a provided database table column
     * Adapted from here: http://stackoverflow.com/a/26992280
     * @param string $table
     * @param string $column
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function get_enum_values($table, $column){
        $column_types = DB::select("SHOW COLUMNS FROM ".$table." WHERE Field='".$column."'");
        if(empty($column_types) || !is_array($column_types)){
            throw new \InvalidArgumentException($table.'.'.$column.' does not exist');
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

}