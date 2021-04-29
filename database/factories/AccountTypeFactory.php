<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountTypeFactory extends Factory {

    public function definition():array{
        $bank_account_number = $this->faker->bankAccountNumber();
        $account_types = AccountType::getEnumValues();
        $account_type = $account_types[array_rand($account_types)];

        return [
            'type'=>$account_type,
            'last_digits'=>substr($bank_account_number, strlen($bank_account_number)-5, 4),
            'name'=>$this->faker->word().' '.$account_type,
            'account_id'=>$this->faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'disabled'=>false,
            'create_stamp'=>$this->faker->date(FactoryConstants::DATE_FORMAT),
            'modified_stamp'=>$this->faker->date(FactoryConstants::DATE_FORMAT),
            'disabled_stamp'=>null,
        ];
    }

}
