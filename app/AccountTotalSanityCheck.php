<?php

namespace App;

class AccountTotalSanityCheck {

    private $actual;
    private $expected;
    private $account_id;
    private $account_name;

    public function diff(){
        return abs(round($this->actual - $this->expected, 2));
    }

    /**
     * Get the array representation of this object
     *
     * @return array
     */
    public function toArray(){
        return [
            'actual'=>$this->actual,
            'expected'=>$this->expected,
            'diff'=>$this->diff(),
            'account_id'=>$this->account_id,
            'account_name'=>$this->account_name,
        ];
    }

    public function __get($name){
        if(array_key_exists($name, $this->toArray()) && $name !== 'diff'){
            return $this->{$name};
        } else {
            return null;
        }
    }

    public function __set($name, $value){
        if(array_key_exists($name, $this->toArray()) && $name !== 'diff'){
            $this->{$name} = $value;
        }
    }

    public function __toString(){
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

}
