<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountTypeFactory extends Factory {

    public function definition(): array {
        $bank_account_number = fake()->bankAccountNumber();
        $account_types = AccountType::getEnumValues();
        $account_type = $account_types[array_rand($account_types)];
        return [
            'type'=>$account_type,
            'last_digits'=>substr($bank_account_number, strlen($bank_account_number)-5, 4),
            'name'=>fake()->word().' '.$account_type,
            'account_id'=>fake()->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'create_stamp'=>fake()->date(FactoryConstants::DATE_FORMAT),
            'disabled_stamp'=>null,
        ];
    }

    /**
     * Indicate that an account-type is "disabled"
     */
    public function disabled(): Factory {
        return $this->state(function() {
            return [
                'disabled_stamp'=>fake()->date(FactoryConstants::DATE_FORMAT)
            ];
        });
    }

}
