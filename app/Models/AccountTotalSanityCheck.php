<?php

namespace App\Models;

use Brick\Money\Money;

class AccountTotalSanityCheck {

    private int $account_id;
    private string $account_name;
    private Money $actual;
    private Money $expected;

    private array $acceptible_parameters = [
        'account_id',
        'account_name',
        'actual',
        'expected',
    ];

    public function diff() {
        return $this->expected->minus($this->actual)->abs();
    }

    /**
     * Get the array representation of this object
     *
     * @return array
     */
    public function toArray() {
        return [
            'account_id'=>$this->account_id,
            'account_name'=>$this->account_name,
            'actual'=>$this->actual,
            'diff'=>$this->diff(),
            'expected'=>$this->expected,
        ];
    }

    public function __get($name) {
        if (in_array($name, $this->acceptible_parameters)) {
            return $this->{$name};
        } else {
            return null;
        }
    }

    public function __set($name, $value) {
        if (in_array($name, $this->acceptible_parameters)) {
            $this->{$name} = $value;
        }
    }

    public function __toString() {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

}
