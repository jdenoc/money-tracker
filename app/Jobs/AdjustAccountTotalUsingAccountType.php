<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\AccountType;
use Brick\Money\Money;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AdjustAccountTotalUsingAccountType implements ShouldQueue {
    use Dispatchable;

    // variables
    protected Account $account;
    protected int $rawValue;
    protected bool $isExpense;
    protected bool $addToAccount;

    /**
     * Create a new job instance.
     */
    public function __construct($accountTypeId, $rawEntryValue, $isExpense, $addToAccount) {
        $account_type = AccountType::withTrashed()->find($accountTypeId);
        $this->account = $account_type->account()->withTrashed()->first();
        $this->rawValue = $rawEntryValue;
        $this->isExpense = $isExpense;
        $this->addToAccount = $addToAccount;
    }

    /**
     * Execute the job.
     */
    public function handle() {
        Log::debug(class_basename(__CLASS__)." job running [accountId:{$this->account->id}]");
        $entry_value = Money::ofMinor($this->rawValue, $this->account->currency)
            ->multipliedBy(($this->isExpense) ? -1 : 1);

        if ($this->addToAccount) {
            Log::debug("adding to account total:".$entry_value);
            $this->account->addToTotal($entry_value);
        } else {
            Log::debug("subtracting from account total:".$entry_value);
            $this->account->subtractFromTotal($entry_value);
        }
    }

}
