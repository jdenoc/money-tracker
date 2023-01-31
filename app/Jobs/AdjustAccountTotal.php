<?php

namespace App\Jobs;

use App\Models\Account;
use Brick\Money\Money;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AdjustAccountTotal {
    use Dispatchable;

    protected Account $account;
    protected Money $entryValue;
    protected bool $addToAccount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $accountId, Money $entryValue, bool $addToAccount=true) {
        $this->account = Account::find($accountId);
        $this->entryValue = $entryValue;
        $this->addToAccount = $addToAccount;
    }

    /**
     * Execute the job.
     */
    public function handle() {
        Log::debug("AdjustAccountTotal job running [accountId:{$this->account->id}]");
        if ($this->addToAccount) {
            Log::debug("adding to account total:".$this->entryValue);
            $this->account->AddToTotal($this->entryValue);
        } else {
            Log::debug("subtracting from account total:".$this->entryValue);
            $this->account->subtractFromTotal($this->entryValue);
        }
    }

}
