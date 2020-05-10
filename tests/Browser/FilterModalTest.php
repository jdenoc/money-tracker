<?php

namespace Tests\Browser;

use App\Account;
use App\AccountType;
use App\Helpers\CurrencyHelper;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * Class FilterModalTest
 *
 * @package Tests\Browser
 *
 * @group filter-modal
 * @group modal
 * @group home
 */
class FilterModalTest extends DuskTestCase {

    use HomePageSelectors;
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitLoading;
    use DuskTraitNavbar;

    use WithFaker;

    private $_partial_selector_filter_tag = "#filter-tag-";

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 1/25
     */
    public function testModalHeaderHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal){
                    $modal
                        ->assertSee("Filter Entries")
                        ->assertVisible($this->_selector_modal_btn_close);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 2/25
     */
    public function testModalBodyHasCorrectElements(){
        $accounts = $this->getApiAccounts();
        $tags = $this->getApiTags();

        $this->browse(function(Browser $browser) use ($accounts, $tags){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($accounts, $tags){
                    $modal
                        // start date - input
                        ->assertSee("Start Date:")
                        ->assertVisible($this->_selector_modal_filter_field_start_date)
                        ->assertInputValue($this->_selector_modal_filter_field_start_date, "");
                    $this->assertEquals(
                        'date',
                        $modal->attribute($this->_selector_modal_filter_field_start_date, 'type'),
                        $this->_selector_modal_filter_field_start_date.' is not type="date"'
                    );

                    $modal
                        // end date - input
                        ->assertSee("End Date:")
                        ->assertVisible($this->_selector_modal_filter_field_end_date)
                        ->assertInputValue($this->_selector_modal_filter_field_end_date, "");
                    $this->assertEquals(
                        'date',
                        $modal->attribute($this->_selector_modal_filter_field_end_date, 'type'),
                        $this->_selector_modal_filter_field_end_date.' is not type="date"'
                    );

                    // account/account-type selector
                    $this->_id_label = 'filter-modal';
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($modal, $accounts);

                    // tags - button(s)
                    $modal
                        ->assertSee("Tags:")
                        ->assertVisible($this->_selector_modal_filter_field_tags);

                    foreach($tags as $tag){
                        $tag_selector = $this->_selector_modal_filter_field_tags.' '.$this->_partial_selector_filter_tag.$tag['id'];
                        $modal
                            ->assertNotChecked($tag_selector)
                            ->assertSeeIn($tag_selector.'+label', $tag['name']);
                    }

                    $modal
                        // income - switch
                        ->assertSee("Income:")
                        ->assertVisible($this->_selector_modal_filter_field_switch_income)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_income, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_income.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // expense - switch
                    $modal
                        ->assertSee("Expense:")
                        ->assertVisible($this->_selector_modal_filter_field_switch_expense)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_expense, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_expense.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // has attachment - switch
                    $modal
                        ->assertSee("Has Attachment(s):")
                        ->assertVisible($this->_selector_modal_filter_field_switch_has_attachment)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_has_attachment, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_has_attachment.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // no attachment - switch
                    $modal
                        ->assertSee("No Attachment(s):")
                        ->assertVisible($this->_selector_modal_filter_field_switch_no_attachment)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_no_attachment, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_no_attachment.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // is transfer - switch
                    $modal
                        ->assertSee("Transfer:")
                        ->assertVisible($this->_selector_modal_filter_field_switch_transfer)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_transfer, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_transfer.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // unconfirmed - switch
                    $modal
                        ->assertSee("Not Confirmed:")
                        ->assertVisible($this->_selector_modal_filter_field_switch_unconfirmed)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_unconfirmed, $this->_label_switch_disabled);
                    $this->assertElementColour(
                        $modal,
                        $this->_selector_modal_filter_field_switch_unconfirmed.' '.$this->_class_switch_core,
                        $this->_color_filter_switch_default
                    );

                        // min range - input
                    $modal
                        ->assertSee("Min Range:")
                        ->assertVisible($this->_selector_modal_filter_field_min_value)
                        ->assertInputValue($this->_selector_modal_filter_field_min_value, "");
                    $this->assertEquals(
                        'text',
                        $modal->attribute($this->_selector_modal_filter_field_min_value, 'type'),
                        $this->_selector_modal_filter_field_min_value.' is not type="text"'
                    );

                    $modal
                        // max range - input
                        ->assertSee("Max Range:")
                        ->assertVisible($this->_selector_modal_filter_field_max_value)
                        ->assertInputValue($this->_selector_modal_filter_field_max_value, "");
                    $this->assertEquals(
                        'text',
                        $modal->attribute($this->_selector_modal_filter_field_max_value, 'type'),
                        $this->_selector_modal_filter_field_max_value.' is not type="text"'
                    );
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 3/25
     */
    public function testModalFooterHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal
                        ->assertVisible($this->_selector_modal_filter_btn_cancel)
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_filter_btn_reset)
                        ->assertSee($this->_label_btn_reset)
                        ->assertVisible($this->_selector_modal_filter_btn_filter)
                        ->assertSee($this->_label_btn_filter);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 4/25
     */
    public function testModalHasTheCorrectNumberOfInputs(){
        $filter_modal_field_selectors = [
            $this->_selector_modal_filter_field_start_date,
            $this->_selector_modal_filter_field_end_date,
            $this->_selector_modal_filter_field_account_and_account_type,
            $this->_selector_modal_filter_field_tags,
            $this->_selector_modal_filter_field_switch_income,
            $this->_selector_modal_filter_field_switch_expense,
            $this->_selector_modal_filter_field_switch_has_attachment,
            $this->_selector_modal_filter_field_switch_no_attachment,
            $this->_selector_modal_filter_field_switch_transfer,
            $this->_selector_modal_filter_field_switch_unconfirmed,
            $this->_selector_modal_filter_field_min_value,
            $this->_selector_modal_filter_field_max_value,
        ];

        $this->browse(function(Browser $browser) use ($filter_modal_field_selectors){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_modal_field_selectors){
                    $filter_modal_elements = $modal->elements('div.field.is-horizontal');
                    $this->assertCount(count($filter_modal_field_selectors), $filter_modal_elements);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 5/25
     */
    public function testCloseTransferModalWithXButtonInHeader(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal){
                    $modal->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_filter);
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 6/25
     */
    public function testCloseTransferModalWithCancelButtonInFooter(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal->click($this->_selector_modal_filter_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_filter);
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 7/25
     */
    public function testCloseFilterModalWithHotkey(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->keys('', "{control}", "{escape}") // ["{control}", "{escape}"] didn't work
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function providerFlipAccountAndAccountTypeSwitch(){
        return [
            // [account.disabled, account-type.disabled]
            ['account'=>false, 'account-type'=>true],   // test 8/25
            ['account'=>false, 'account-type'=>false],  // test 9/25
            ['account'=>true, 'account-type'=>false],   // test 10/25
            ['account'=>true, 'account-type'=>true],    // test 11/25
        ];
    }

    /**
     * @dataProvider providerFlipAccountAndAccountTypeSwitch
     * @param boolean $has_disabled_account
     * @param boolean $has_disabled_account_type
     *
     * @throws Throwable
     *
     * @group filter-modal-1
     * test (see provider)/25
     */
    public function testFlipAccountAndAccountTypeSwitch($has_disabled_account, $has_disabled_account_type){
        DB::statement("TRUNCATE accounts");
        DB::statement("TRUNCATE account_types");

        $institutions = $this->getApiInstitutions();
        $institution_id = collect($institutions)->pluck('id')->random(1)->first();

        factory(Account::class, 3)->create(['disabled'=>0, 'institution_id'=>$institution_id]);
        if($has_disabled_account){
            factory(Account::class, 1)->create(['disabled'=>1, 'institution_id'=>$institution_id]);
        }
        $accounts = $this->getApiAccounts();

        factory(AccountType::class, 3)->create(['disabled'=>0, 'account_id'=>collect($accounts)->pluck('id')->random(1)->first()]);
        if($has_disabled_account_type){
            factory(AccountType::class, 1)->create(['disabled'=>1, 'account_id'=>collect($accounts)->pluck('id')->random(1)->first()]);
        }
        $account_types = $this->getApiAccountTypes();

        $this->browse(function(Browser $browser) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types){
                    $modal
                        ->assertVisible($this->getSwitchAccountAndAccountTypeId())
                        ->assertVisible($this->_selector_modal_filter_field_account_and_account_type)

                        ->assertSeeIn($this->getSwitchAccountAndAccountTypeId(), "Account");
                    $this->assertElementColour($modal, $this->getSwitchAccountAndAccountTypeId().' '.$this->_class_switch_core, $this->_color_filter_switch_default);

                    if($has_disabled_account){
                        $modal
                            ->assertVisible($this->_selector_modal_filter_field_checkbox_show_disabled_label)
                            ->assertSeeIn($this->_selector_modal_filter_field_checkbox_show_disabled_label, $this->_label_checkbox_show_disabled);
                    } else {
                        $modal->assertMissing($this->_selector_modal_filter_field_checkbox_show_disabled);
                    }

                    $modal
                        ->assertSelected($this->_selector_modal_filter_field_account_and_account_type, "")
                        ->assertSeeIn($this->_selector_modal_filter_field_account_and_account_type, $this->_label_select_option_filter_default)
                        ->assertSelectHasOption($this->_selector_modal_filter_field_account_and_account_type, "")
                        ->assertSelectHasOptions($this->_selector_modal_filter_field_account_and_account_type, collect($accounts)->where('disabled', false)->pluck('id')->toArray());

                    if($has_disabled_account){
                        $modal
                            ->click($this->_selector_modal_filter_field_checkbox_show_disabled_label)
                            ->assertSelectHasOptions(
                                $this->_selector_modal_filter_field_account_and_account_type,
                                collect($accounts)->pluck('id')->toArray()
                            )
                            ->click($this->_selector_modal_filter_field_checkbox_show_disabled_label);  // click again to reset state for account-types
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_min_value_icon, 'class'));
                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_max_value_icon, 'class'));

                    // select an account and confirm the name in the select changes
                    $account_to_select = collect($accounts)->where('disabled', 0)->random(1)->first();
                    $modal
                        ->select($this->_selector_modal_filter_field_account_and_account_type, $account_to_select['id'])
                        ->assertSeeIn($this->_selector_modal_filter_field_account_and_account_type, $account_to_select['name']);

                    // test select account changes currency displayed in "Min Range" & "Max Range" fields
                    $fail_message = "account data:".json_encode($account_to_select);
                    $this->assertContains(
                        $this->getCurrencyClassFromCurrency($account_to_select['currency']),
                        $modal->attribute($this->_selector_modal_filter_field_min_value_icon, 'class'),
                        $fail_message
                    );
                    $this->assertContains($this->getCurrencyClassFromCurrency(
                        $account_to_select['currency']),
                        $modal->attribute($this->_selector_modal_filter_field_max_value_icon, 'class'),
                        $fail_message
                    );

                    $modal
                        ->click($this->getSwitchAccountAndAccountTypeId())
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS)

                        ->assertSeeIn($this->getSwitchAccountAndAccountTypeId(), "Account Type");
                    $this->assertElementColour($modal, $this->getSwitchAccountAndAccountTypeId().' '.$this->_class_switch_core, $this->_color_filter_switch_default);

                    if($has_disabled_account_type){
                        $modal
                            ->assertVisible($this->_selector_modal_filter_field_checkbox_show_disabled_label)
                            ->assertSeeIn($this->_selector_modal_filter_field_checkbox_show_disabled_label, $this->_label_checkbox_show_disabled);
                    } else {
                        $modal->assertMissing($this->_selector_modal_filter_field_checkbox_show_disabled);
                    }

                    $modal
                        ->assertSelected($this->_selector_modal_filter_field_account_and_account_type, "")
                        ->assertSeeIn($this->_selector_modal_filter_field_account_and_account_type, $this->_label_select_option_filter_default)
                        ->assertSelectHasOption($this->_selector_modal_filter_field_account_and_account_type, "")
                        ->assertSelectHasOptions(
                            $this->_selector_modal_filter_field_account_and_account_type,
                            collect($account_types)->where('disabled', false)->pluck('id')->toArray()
                        );

                    if($has_disabled_account_type){
                        $modal
                            ->click($this->_selector_modal_filter_field_checkbox_show_disabled_label)
                            ->assertSelectHasOptions(
                                $this->_selector_modal_filter_field_account_and_account_type,
                                collect($account_types)->pluck('id')->toArray()
                            );
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_min_value_icon, 'class'));
                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_max_value_icon, 'class'));

                    $account_type_to_select = $this->faker->randomElement($account_types);
                    $modal
                        // select an account and confirm the name in the select changes
                        ->select($this->_selector_modal_filter_field_account_and_account_type, $account_type_to_select['id'])
                        ->assertSeeIn($this->_selector_modal_filter_field_account_and_account_type, $account_type_to_select['name']);

                    // test select account-type changes currency displayed in "Min Range" & "Max Range" fields
                    $account_from_account_type = collect($accounts)->where('id', '=', $account_type_to_select['account_id'])->first();
                    $fail_message = "account-type data:".json_encode($account_type_to_select)."\naccount data:".json_encode($account_from_account_type);
                    $this->assertContains(
                        $this->getCurrencyClassFromCurrency($account_from_account_type['currency']),
                        $modal->attribute($this->_selector_modal_filter_field_min_value_icon, 'class'),
                        $fail_message
                    );
                    $this->assertContains($this->getCurrencyClassFromCurrency($account_from_account_type['currency']),
                        $modal->attribute($this->_selector_modal_filter_field_max_value_icon, 'class'),
                        $fail_message
                    );
                });
        });
    }

    private function getCurrencyClassFromCurrency($currency){
        $currency_class = CurrencyHelper::fetchCurrencies()->where('code', $currency)->first()->class;
        if(is_null($currency_class)){
            return $this->_class_icon_dollar;
        } else {
            return $currency_class;
        }
    }

    public function providerFlipSwitch(){
        return [
            "flip income"=>['#filter-is-income'],               // test 12/25
            "flip expense"=>['#filter-is-expense'],             // test 13/25
            "flip has-attachment"=>['#filter-has-attachment'],  // test 14/25
            "flip no-attachment"=>['#filter-no-attachment'],    // test 15/25
            "flip transfer"=>['#filter-is-transfer'],           // test 16/25
            "flip not confirmed"=>['#filter-unconfirmed']       // test 17/25
        ];
    }

    /**
     * @dataProvider providerFlipSwitch
     * @param string $switch_selector
     *
     * @throws Throwable
     *
     * @group filter-modal-1
     * test (see provider)/25
     */
    public function testFlipSwitch($switch_selector){
        $this->browse(function(Browser $browser) use ($switch_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter, function(Browser $modal) use ($switch_selector){
                    $modal
                        ->assertVisible($switch_selector)
                        ->assertSeeIn($switch_selector, $this->_label_switch_disabled);
                    $this->assertElementColour($modal, $switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_default);
                    $modal
                        ->click($switch_selector)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS)
                        ->assertSeeIn($switch_selector, $this->_label_switch_enabled);
                    $this->assertElementColour($modal, $switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_active);
                });
        });
    }

    public function providerFlippingCompanionSwitches(){
        return [
            "flip income with expense"=>['#filter-is-income', '#filter-is-expense'],                        // test 18/25
            "flip expense with income"=>['#filter-is-expense', '#filter-is-income'],                        // test 19/25
            "flip has-attachment with no-attachment"=>['#filter-has-attachment', '#filter-no-attachment'],  // test 20/25
            "flip no-attachment with has-attachment"=>['#filter-no-attachment', '#filter-has-attachment']   // test 21/25
        ];
    }

    /**
     * @dataProvider providerFlippingCompanionSwitches
     * @param string $init_switch_selector
     * @param string $companion_switch_selector
     *
     * @throws Throwable
     *
     * @group filter-modal-1
     * test (see provider)/25
     */
    public function testFlippingCompanionSwitches($init_switch_selector, $companion_switch_selector){
        $this->browse(function(Browser $browser) use ($init_switch_selector, $companion_switch_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter, function(Browser $modal) use ($init_switch_selector, $companion_switch_selector){
                    $modal
                        ->assertVisible($init_switch_selector)
                        ->assertSeeIn($init_switch_selector, $this->_label_switch_disabled);
                    $this->assertElementColour($modal, $init_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_default);
                    $modal
                        ->assertVisible($companion_switch_selector)
                        ->assertSeeIn($companion_switch_selector, $this->_label_switch_disabled);
                    $this->assertElementColour($modal, $companion_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_default);
                    $modal
                        ->click($init_switch_selector)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS)
                        ->assertSeeIn($init_switch_selector, $this->_label_switch_enabled);
                    $this->assertElementColour($modal, $init_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_active);
                    $modal->assertSeeIn($companion_switch_selector, $this->_label_switch_disabled);
                    $this->assertElementColour($modal, $companion_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_default);
                    $modal
                        ->click($companion_switch_selector)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS)
                        ->assertSeeIn($companion_switch_selector, $this->_label_switch_enabled);
                    $this->assertElementColour($modal, $companion_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_active);
                    $modal
                        ->assertSeeIn($init_switch_selector, $this->_label_switch_disabled);
                    $this->assertElementColour($modal, $init_switch_selector.' '.$this->_class_switch_core, $this->_color_filter_switch_default);
                });
        });
    }

    public function providerRangeValueConvertsIntoDecimalOfTwoPlaces(){
        return [
            'Min Range'=>['#filter-min-value'], // test 22/25
            'Max Range'=>['#filter-max-value'], // test 23/25
        ];
    }

    /**
     * @dataProvider providerRangeValueConvertsIntoDecimalOfTwoPlaces
     * @param $field_selector
     *
     * @throws Throwable
     *
     * @group filter-modal-1
     * test (see provider)/25
     */
    public function testRangeValueConvertsIntoDecimalOfTwoPlaces($field_selector){
        $this->browse(function(Browser $browser) use ($field_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($field_selector){
                    $modal
                        ->type($field_selector, "rh48r7th72.9ewd3dadh1")
                        ->click($this->_selector_modal_filter_field_end_date)
                        ->assertInputValue($field_selector, "48772.93");
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 24/25
     */
    public function testClickingOnTagButtons(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function (Browser $modal){
                    $tags = $this->getApiTags();
                    foreach($tags as $tag){
                        $selector_tag_checkbox = $this->_selector_modal_filter_field_tags.' '.$this->_partial_selector_filter_tag.$tag['id'];
                        $selector_tag_label = $selector_tag_checkbox.'+label';
                        $modal->assertNotChecked($selector_tag_checkbox);
                        $this->assertElementColour($modal, $selector_tag_label, $this->_color_filter_btn_tag_default);
                        $modal
                            ->click($selector_tag_label)
                            ->assertChecked($selector_tag_checkbox);
                        $this->assertElementColour($modal, $selector_tag_label, $this->_color_filter_btn_tag_active);
                    }
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-1
     * test 25/25
     */
    public function testResetFields(){
        $this->browse(function(Browser $browser){
            $tags = $this->getApiTags();

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($tags){
                    $time_from_browser = $modal->getBrowserLocaleDate();
                    $start_date = $modal->processLocaleDateForTyping($time_from_browser);

                    $account_types = $this->getApiAccountTypes();
                    $account_type = $this->faker->randomElement($account_types);

                    $tags_to_select = $this->faker->randomElements($tags, $this->faker->numberBetween(1, count($tags)));

                    $companion_switch_set_1 = [$this->_selector_modal_filter_field_switch_expense, $this->_selector_modal_filter_field_switch_income];
                    $companion_switch_set_2 = [$this->_selector_modal_filter_field_switch_has_attachment, $this->_selector_modal_filter_field_switch_no_attachment];

                    $modal
                        ->type($this->_selector_modal_filter_field_start_date, $start_date)
                        ->type($this->_selector_modal_filter_field_end_date, $start_date)
                        ->click($this->getSwitchAccountAndAccountTypeId())
                        ->select($this->_selector_modal_filter_field_account_and_account_type, $account_type['id']);

                    foreach($tags_to_select as $tag_to_select){
                        $modal->click($this->_selector_modal_filter_field_tags.' '.$this->_partial_selector_filter_tag.$tag_to_select['id'].'+label');
                    }

                    $modal
                        ->click($this->faker->randomElement($companion_switch_set_1))
                        ->click($this->faker->randomElement($companion_switch_set_2))
                        ->click($this->_selector_modal_filter_field_switch_transfer)
                        ->click($this->_selector_modal_filter_field_switch_unconfirmed)
                        ->type($this->_selector_modal_filter_field_max_value, "65.43")
                        ->type($this->_selector_modal_filter_field_min_value, "9.87");
                })

                // click reset button
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal
                        ->assertVisible($this->_selector_modal_filter_btn_reset)
                        ->click($this->_selector_modal_filter_btn_reset)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS);
                })

                // confirm all fields have been reset
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($tags){
                    $modal
                        ->assertInputValue($this->_selector_modal_filter_field_start_date, '')
                        ->assertInputValue($this->_selector_modal_filter_field_end_date, '')
                        ->assertSeeIn($this->getSwitchAccountAndAccountTypeId(), "Account")
                        ->assertSelected($this->_selector_modal_filter_field_account_and_account_type, '');

                    foreach($tags as $tag){
                        $modal->assertNotChecked($this->_selector_modal_filter_field_tags.' '.$this->_partial_selector_filter_tag.$tag['id']);
                    }

                    $modal
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_income, $this->_label_switch_disabled)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_expense, $this->_label_switch_disabled)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_has_attachment, $this->_label_switch_disabled)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_no_attachment, $this->_label_switch_disabled)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_transfer, $this->_label_switch_disabled)
                        ->assertSeeIn($this->_selector_modal_filter_field_switch_unconfirmed, $this->_label_switch_disabled)
                        ->assertInputValue($this->_selector_modal_filter_field_min_value, "")
                        ->assertInputValue($this->_selector_modal_filter_field_max_value, "");

                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_min_value_icon, 'class'));
                    $this->assertContains($this->_class_icon_dollar, $modal->attribute($this->_selector_modal_filter_field_max_value_icon, 'class'));
                });
        });
    }

    public function providerClickFilterButtonToFilterResults(){
        return [
            "Start Date"=>[$this->_selector_modal_filter_field_start_date],                         // test 1/25
            "End Date"=>[$this->_selector_modal_filter_field_end_date],                             // test 2/25
            "Account&Account-type"=>[$this->_selector_modal_filter_field_account_and_account_type], // test 3/25
            "Tags"=>[$this->_partial_selector_filter_tag],                                          // test 4/25
            "Income"=>[$this->_selector_modal_filter_field_switch_income],                          // test 5/25
            "Expense"=>[$this->_selector_modal_filter_field_switch_expense],                        // test 6/25
            "Has Attachments"=>[$this->_selector_modal_filter_field_switch_has_attachment],         // test 7/25
            "No Attachments"=>[$this->_selector_modal_filter_field_switch_no_attachment],           // test 8/25
            "Transfer"=>[$this->_selector_modal_filter_field_switch_transfer],                      // test 9/25
            "Unconfirmed"=>[$this->_selector_modal_filter_field_switch_unconfirmed],                // test 10/25
            "Min Range"=>[$this->_selector_modal_filter_field_min_value],                           // test 11/25
            "Max Range"=>[$this->_selector_modal_filter_field_max_value],                           // test 12/25
        ];
    }

    /**
     * @dataProvider providerClickFilterButtonToFilterResults
     * @param $filter_param
     *
     * @throws Throwable
     *
     * @group filter-modal-2
     * test (see provider)/25
     */
    public function testClickFilterButtonToFilterResults($filter_param){
        $this->browse(function(Browser $browser) use ($filter_param){
            $filter_value = '';
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_param, &$filter_value){
                    switch($filter_param){
                        case $this->_selector_modal_filter_field_start_date:
                        case $this->_selector_modal_filter_field_end_date:
                            $filter_value = ['actual'=>$this->faker->date("Y-m-d")];
                            $browser_date = $modal->getDateFromLocale($modal->getBrowserLocale(), $filter_value['actual']);
                            $filter_value['typed'] = $modal->processLocaleDateForTyping($browser_date);
                            $modal->type($filter_param, $filter_value['typed']);
                            break;

                        case $this->_selector_modal_filter_field_account_and_account_type:
                            $is_account = $this->faker->boolean;
                            if($is_account){
                                // account
                                $filter_values = $this->getApiAccounts();
                            } else {
                                // account-type
                                $modal->click($this->getSwitchAccountAndAccountTypeId());
                                $filter_values = $this->getApiAccountTypes();
                            }
                            $filter_value = collect($filter_values)->where('disabled', false)->random(1)->first();
                            $modal->select($filter_param, $filter_value['id']);

                            if($is_account){
                                $account = Account::find_account_with_types($filter_value['id']);
                                $filter_value = $account->account_types->pluck('name')->toArray();
                            } else {
                                $filter_value = $filter_value['name'];
                            }
                            break;

                        case $this->_partial_selector_filter_tag:
                            $tags = $this->getApiTags();
                            $filter_value = collect($tags)->random(3)->toArray();
                            foreach($filter_value as $tag){
                                $modal->click($filter_param.$tag['id'].'+label');
                            }
                            break;

                        case $this->_selector_modal_filter_field_switch_income:
                        case $this->_selector_modal_filter_field_switch_expense:
                        case $this->_selector_modal_filter_field_switch_has_attachment:
                        case $this->_selector_modal_filter_field_switch_no_attachment:
                        case $this->_selector_modal_filter_field_switch_transfer:
                        case $this->_selector_modal_filter_field_switch_unconfirmed:
                            $modal->click($filter_param);
                            break;

                        case $this->_selector_modal_filter_field_min_value:
                        case $this->_selector_modal_filter_field_max_value:
                            $filter_value = $this->faker->randomFloat(2, 0, 100);
                            // need to use type() here otherwise vuejs won't pick up the update
                            $modal->type($filter_param, $filter_value);
                            break;

                        default:
                            throw new \InvalidArgumentException("Unknown filter parameter provided:".$filter_param);
                    }
                })

                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal->click($this->_selector_modal_filter_btn_filter);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_filter)

                // confirm only rows matching the filter parameters are shown
                ->with($this->_selector_table.' '.$this->_selector_table_body, function(Browser $table) use ($filter_param, $filter_value){
                    $table_rows = $table->elements('tr');
                    foreach($table_rows as $table_row){
                        switch($filter_param){
                            case $this->_selector_modal_filter_field_start_date:
                                // only rows with dates >= $start_date
                                $row_entry_date = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_date))->getText();
                                $this->assertGreaterThanOrEqual(
                                    strtotime($filter_value['actual']),
                                    strtotime($row_entry_date),
                                    'Row date "'.$row_entry_date.'" less than filter start date "'.$filter_value['actual'].'" typed as "'.$filter_value['typed'].'"'
                                );
                                break;

                            case $this->_selector_modal_filter_field_end_date:
                                // only rows with dates <= $end_date
                                $row_entry_date = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_date))->getText();
                                $this->assertLessThanOrEqual(
                                    strtotime($filter_value['actual']),
                                    strtotime($row_entry_date),
                                    'Row date "'.$row_entry_date.'" greater than filter start date "'.$filter_value['actual'].'" typed as "'.$filter_value['typed'].'"'
                                );
                                break;

                            case $this->_selector_modal_filter_field_account_and_account_type:
                                // only rows with account-type(s)
                                $row_entry_account_type = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))->getText();
                                if(is_array($filter_value)){
                                    $this->assertContains($row_entry_account_type, $filter_value);
                                } else {
                                    $this->assertEquals($row_entry_account_type, $filter_value);
                                }
                                break;

                            case $this->_partial_selector_filter_tag:
                                // only rows with .has-tags class
                                $this->assertContains('has-tags', $table_row->getAttribute('class'));
                                // each row will contain the selected tag text
                                $row_entry_tags = explode("\n", $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_tags))->getText());
                                $filter_value_names = collect($filter_value)->pluck('name')->toArray();
                                $this->assertNotEmpty(
                                    array_intersect($row_entry_tags, $filter_value_names),
                                    sprintf(
                                        "Could not find any of these tags [%s] in the list of filtered tags [%s]",
                                        implode(',', $row_entry_tags),
                                        implode(', ', $filter_value_names)
                                    )
                                );
                                break;

                            case $this->_selector_modal_filter_field_switch_income:
                                // only rows with .is-income class
                                $this->assertContains('is-income', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_expense:
                                // only rows with .is-expense class
                                $this->assertContains('is-expense', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_has_attachment:
                                // only rows with .has-attachments class
                                $this->assertContains('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_no_attachment:
                                // rows DO NOT CONTAIN .has-attachments class
                                $this->assertNotContains('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_transfer:
                                // only rows with .is-transfer class
                                $this->assertContains('is-transfer', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_unconfirmed:
                                // rows DO NOT CONTAIN .is-confirmed class
                                $table_row_class = $table_row->getAttribute('class');
                                $this->assertNotContains('is-confirmed', $table_row_class);
                                $this->assertContains('has-background-warning', $table_row_class);
                                break;
                            case $this->_selector_modal_filter_field_min_value:
                                // only rows with value >= min_value
                                $row_entry_value = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_value))->getText();
                                $this->assertGreaterThanOrEqual($filter_value, $row_entry_value);
                                break;

                            case $this->_selector_modal_filter_field_max_value:
                                // only rows with value <= max_value
                                $row_entry_value = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_value))->getText();
                                $this->assertLessThanOrEqual($filter_value, $row_entry_value);
                                break;

                            default:
                                throw new \InvalidArgumentException("Unknown filter parameter provided:".$filter_param);
                        }
                    }
            });
        });
    }


    /**
     * @throws Throwable
     *
     * @group filter-modal-2
     * test 13/25
     */
    public function testClickFilterButtonToUpdateInstitutionsPanelActive(){
        $this->browse(function(Browser $browser){
            $filter_value = [];
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use (&$filter_value){
                    $accounts = $this->getApiAccounts();
                    $filter_value = collect($accounts)->where('disabled', 0)->random(1)->first();
                    $modal->select($this->_selector_modal_filter_field_account_and_account_type, $filter_value['id']);
                })

                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function($modal){
                    $modal->click($this->_selector_modal_filter_btn_filter);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_filter)

                ->with($this->_selector_panel_institutions, function(Browser $panel) use ($filter_value){
                    // TODO: confirm "Overview (filtered)" is visible
                    // "overview" is NOT active
                    $overview_classes = $panel->attribute($this->_selector_panel_institutions_overview, 'class');
                    $this->assertNotContains($this->_class_is_active, $overview_classes);

                    $panel
                        ->click("#institution-".$filter_value['institution_id'].' a')
                        ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS);

                    $account_classes = $panel->attribute('#account-'.$filter_value['id'].' '.$this->_selector_panel_institutions_accounts_account_name, 'class');
                    $this->assertContains($this->_class_is_active, $account_classes);

                    $selector = '#account-'.$filter_value['id'].' '.$this->_selector_panel_institutions_accounts_account_name.' span:first-child';
                    $account_name = $panel->text($selector);
                    $this->assertEquals($filter_value['name'], $account_name, "Could not find value at selector:".$panel->resolver->format($selector));
                });
        });
    }

    /**
     * @return string
     */
    private function getSwitchAccountAndAccountTypeId(){
        return sprintf($this->_selector_pattern_modal_filter_field_switch_account_and_account_type, 'filter-modal');
    }

}
