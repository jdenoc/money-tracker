<?php

namespace Tests\Feature\Console;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class AccountTotalSanityCheckTest extends TestCase{

    private $_command = 'sanity-check:account-total';

    public function testForceFailureOutputtingToScreenAndWithoutNotifyingDiscord(){
        Artisan::call($this->_command, ['--force-failure'=>true, '--notify-screen'=>true, '--dont-notify-discord'=>true]);
        
        $result_as_text = trim(Artisan::output());
        $this->assertContains("Forcing Failure", $result_as_text);
        $this->assertContains("Sanity check has failed", $result_as_text);
    }

}
