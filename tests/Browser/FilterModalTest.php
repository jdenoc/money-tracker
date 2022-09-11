<?php

namespace Tests\Browser;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\AccountType;
use App\Traits\Tests\Dusk\FilterModal as DuskTraitFilterModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\WithTailwindColors;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
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

    use DuskTraitFilterModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitTagsInput;
    use HomePageSelectors;
    use WithFaker;
    use WithTailwindColors;

    private $_default_currency_character;

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->initFilterModalTogglingSelectorLabelId();

        $default_currency = CurrencyHelper::getCurrencyDefaults();
        $this->_default_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter($default_currency->html);
    }

    public function setUp(): void{
        parent::setUp();
        $this->initFilterModalColors();
    }

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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal){
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

        $this->browse(function(Browser $browser) use ($accounts){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($accounts){
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
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($modal, $accounts);

                    // tags - button(s)
                    $modal->assertSee("Tags:");
                    $this->assertDefaultStateOfTagsInput($modal);

                    // income - switch
                    $modal->assertSee("Income:");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_income,
                        $this->_label_switch_disabled,
                        $this->_color_filter_switch_default
                    );

                    // expense - switch
                    $modal->assertSee("Expense:");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_expense,
                        $this->_label_switch_disabled,
                        $this->_color_filter_switch_default
                    );

                    // has attachment - switch
                    $modal->assertSee("Has Attachment(s):");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_has_attachment,
                        $this->_label_switch_disabled,
                        $this->_color_filter_switch_default
                    );

                    // no attachment - switch
                    $modal->assertSee("No Attachment(s):");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_no_attachment,
                        $this->_label_switch_disabled,
                        $this->_color_filter_switch_default
                    );

                    // is transfer - switch
                    $modal->assertSee("Transfer:");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_transfer,
                        $this->_label_switch_disabled,
                        $this->_color_filter_switch_default
                    );

                    // unconfirmed - switch
                    $modal->assertSee("Not Confirmed:");
                    $this->assertToggleButtonState(
                        $modal,
                        $this->_selector_modal_filter_field_switch_unconfirmed,
                        $this->_label_switch_disabled,
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

                    // max range - input
                    $modal
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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
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
        $filter_modal_field_selectors = $this->filterModalInputs();

        $this->browse(function(Browser $browser) use ($filter_modal_field_selectors){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_modal_field_selectors){
                    $filter_modal_elements = $modal->elements('.filter-modal-element');
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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal){
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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
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
        $this->markTestIncomplete("hotkey functionality needs work");
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->keys('', "{control}", "{escape}") // ["{control}", "{escape}"] didn't work
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function providerFlipAccountAndAccountTypeSwitch():array{
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
    public function testFlipAccountAndAccountTypeSwitch(bool $has_disabled_account, bool $has_disabled_account_type){
        DB::table('accounts')->truncate();
        DB::table('account_types')->truncate();

        $institutions = $this->getApiInstitutions();
        $institution_id = collect($institutions)->pluck('id')->random(1)->first();

        Account::factory()->count(3)->create(['disabled'=>0, 'institution_id'=>$institution_id]);
        if($has_disabled_account){
            Account::factory()->count(1)->create(['disabled'=>1, 'institution_id'=>$institution_id]);
        }
        $accounts = $this->getApiAccounts();

        AccountType::factory()->count(3)->create(['disabled'=>0, 'account_id'=>collect($accounts)->pluck('id')->random(1)->first()]);
        if($has_disabled_account_type){
            AccountType::factory()->count(1)->create(['disabled'=>1, 'account_id'=>collect($accounts)->pluck('id')->random(1)->first()]);
        }
        $account_types = $this->getApiAccountTypes();

        $this->browse(function(Browser $browser) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types){
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($modal, $accounts);

                    if($has_disabled_account){
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsVisible($modal);
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                        $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $accounts);
                        // click again to reset state for account-types
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                    } else {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible($modal);
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_min_value, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_max_value, $this->_default_currency_character);

                    // select an account and confirm the name in the select changes
                    $account_to_select = collect($accounts)->where('disabled', 0)->random();
                    $this->selectAccountOrAccountTypeValue($modal, $account_to_select['id']);
                    $modal->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $account_to_select['name']);

                    // test select account changes currency displayed in "Min Range" & "Max Range" fields
                    $field_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter(CurrencyHelper::getCurrencyHtmlFromCode($account_to_select['currency']));
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_min_value, $field_currency_character);
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_max_value, $field_currency_character);

                    $this->toggleAccountOrAccountTypeSwitch($modal);
                    $this->assertToggleButtonState(
                        $modal,
                        $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_id_label),
                        self::$LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNTTYPE,
                        $this->_color_filter_switch_default
                    );

                    $modal
                        ->assertSelectHasOption(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                        ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                        ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT);
                    $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $account_types);

                    if($has_disabled_account_type){
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsVisible($modal);
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                        $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $account_types);
                    } else {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible($modal);
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_min_value, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_max_value, $this->_default_currency_character);

                    // select an account and confirm the name in the select changes
                    $account_type_to_select = $this->faker->randomElement($account_types);
                    $this->selectAccountOrAccountTypeValue($modal, $account_type_to_select['id']);
                    $modal->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $account_type_to_select['name']);

                    // test select account-type changes currency displayed in "Min Range" & "Max Range" fields
                    $account_from_account_type = collect($accounts)->where('id', '=', $account_type_to_select['account_id'])->first();
                    $field_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter(CurrencyHelper::getCurrencyHtmlFromCode($account_from_account_type['currency']));
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_min_value, $field_currency_character);
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_max_value, $field_currency_character);
                });
        });
    }

    private function assertValueFieldCurrency(Browser $modal, string $selector, string $currency_symbol){
        $value_currency = $modal->text($selector." + span.currency-symbol");
        $this->assertStringContainsString($currency_symbol, $value_currency);
    }

    public function providerFlipSwitch():array{
        return [
            "flip income"=>[$this->_selector_modal_filter_field_switch_income],                 // test 12/25
            "flip expense"=>[$this->_selector_modal_filter_field_switch_expense],               // test 13/25
            "flip has-attachment"=>[$this->_selector_modal_filter_field_switch_has_attachment], // test 14/25
            "flip no-attachment"=>[$this->_selector_modal_filter_field_switch_no_attachment],   // test 15/25
            "flip transfer"=>[$this->_selector_modal_filter_field_switch_transfer],             // test 16/25
            "flip not confirmed"=>[$this->_selector_modal_filter_field_switch_unconfirmed]      // test 17/25
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
    public function testFlipSwitch(string $switch_selector){
        $this->browse(function(Browser $browser) use ($switch_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function(Browser $modal) use ($switch_selector){
                    $this->assertToggleButtonState($modal, $switch_selector, $this->_label_switch_disabled, $this->_color_filter_switch_inactive);
                    $this->toggleToggleButton($modal, $switch_selector);
                    $this->assertToggleButtonState($modal, $switch_selector, $this->_label_switch_enabled, $this->_color_filter_switch_active);
                });
        });
    }

    public function providerFlippingCompanionSwitches():array{
        return [
            "flip income with expense"=>[$this->_selector_modal_filter_field_switch_income, $this->_selector_modal_filter_field_switch_expense],                              // test 18/25
            "flip expense with income"=>[$this->_selector_modal_filter_field_switch_expense, $this->_selector_modal_filter_field_switch_income],                              // test 19/25
            "flip has-attachment with no-attachment"=>[$this->_selector_modal_filter_field_switch_has_attachment, $this->_selector_modal_filter_field_switch_no_attachment],  // test 20/25
            "flip no-attachment with has-attachment"=>[$this->_selector_modal_filter_field_switch_no_attachment, $this->_selector_modal_filter_field_switch_has_attachment]   // test 21/25
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
    public function testFlippingCompanionSwitches(string $init_switch_selector, string $companion_switch_selector){
        $this->browse(function(Browser $browser) use ($init_switch_selector, $companion_switch_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function(Browser $modal) use ($init_switch_selector, $companion_switch_selector){
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_disabled, $this->_color_filter_switch_inactive);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_disabled, $this->_color_filter_switch_inactive);

                    $this->toggleToggleButton($modal, $init_switch_selector);
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_enabled, $this->_color_filter_switch_active);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_disabled, $this->_color_filter_switch_inactive);

                    $this->toggleToggleButton($modal, $companion_switch_selector);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_enabled, $this->_color_filter_switch_active);
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_disabled, $this->_color_filter_switch_inactive);
                });
        });
    }

    public function providerRangeValueConvertsIntoDecimalOfTwoPlaces():array{
        return [
            'Min Range'=>[$this->_selector_modal_filter_field_min_value], // test 22/25
            'Max Range'=>[$this->_selector_modal_filter_field_max_value], // test 23/25
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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($field_selector){
                    $modal
                        ->type($field_selector, "rh48r7th72.9ewd3dadh1")
                        ->click(sprintf('label[for="%s"]', ltrim($field_selector, '#')))
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
    public function testResetFields(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal){
                    $time_from_browser = $modal->getBrowserLocaleDate();
                    $start_date = $modal->processLocaleDateForTyping($time_from_browser);

                    $account_types = $this->getApiAccountTypes();
                    $account_type = $this->faker->randomElement($account_types);

                    $tags = $this->getApiTags();
                    $tags_to_select = $this->faker->randomElements($tags, $this->faker->numberBetween(1, count($tags)));

                    $companion_switch_set_1 = [$this->_selector_modal_filter_field_switch_expense, $this->_selector_modal_filter_field_switch_income];
                    $companion_switch_set_2 = [$this->_selector_modal_filter_field_switch_has_attachment, $this->_selector_modal_filter_field_switch_no_attachment];

                    $modal
                        ->type($this->_selector_modal_filter_field_start_date, $start_date)
                        ->type($this->_selector_modal_filter_field_end_date, $start_date);
                    $this->toggleAccountOrAccountTypeSwitch($modal);
                    $this->selectAccountOrAccountTypeValue($modal, $account_type['id']);

                    foreach($tags_to_select as $tag_to_select){
                        $this->fillTagsInputUsingAutocomplete($modal, $tag_to_select['name']);
                    }

                    $this->toggleToggleButton($modal, $this->faker->randomElement($companion_switch_set_1));
                    $this->toggleToggleButton($modal, $this->faker->randomElement($companion_switch_set_2));
                    $modal
                        ->click($this->_selector_modal_filter_field_switch_transfer)
                        ->click($this->_selector_modal_filter_field_switch_unconfirmed)
                        ->type($this->_selector_modal_filter_field_max_value, "65.43")
                        ->type($this->_selector_modal_filter_field_min_value, "9.87");
                })

                // click reset button
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal
                        ->assertVisible($this->_selector_modal_filter_btn_reset)
                        ->click($this->_selector_modal_filter_btn_reset)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS);
                })

                // confirm all fields have been reset
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal){
                    $modal
                        ->assertInputValue($this->_selector_modal_filter_field_start_date, '')
                        ->assertInputValue($this->_selector_modal_filter_field_end_date, '')
                        ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, '')
                        ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT);

                    $this->assertDefaultStateOfTagsInput($modal);

                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_income, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_expense, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_has_attachment, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_no_attachment, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_transfer, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $this->assertToggleButtonState($modal, $this->_selector_modal_filter_field_switch_unconfirmed, $this->_label_switch_disabled, $this->_color_filter_switch_default);
                    $modal
                        ->assertInputValue($this->_selector_modal_filter_field_min_value, "")
                        ->assertInputValue($this->_selector_modal_filter_field_max_value, "");

                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_min_value, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, $this->_selector_modal_filter_field_max_value, $this->_default_currency_character);
                });
        });
    }

    public function providerClickFilterButtonToFilterResults():array{
        return $this->filterModalInputs();  // test (?)/25
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
            $filter_value = null;
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_param, &$filter_value){
                    $filter_value = $this->filterModalInputInteraction($modal, $filter_param);
                })

                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal->click($this->_selector_modal_filter_btn_filter);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_filter)

                // confirm only rows matching the filter parameters are shown
                ->within($this->_selector_table.' '.$this->_selector_table_body, function(Browser $table) use ($filter_param, $filter_value){
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

                            case self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT:
                                // only rows with account-type(s)
                                $row_entry_account_type = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))->getText();
                                if(is_array($filter_value)){
                                    $this->assertContains($row_entry_account_type, $filter_value);
                                } else {
                                    $this->assertEquals($row_entry_account_type, $filter_value);
                                }
                                break;

                            case $this->_selector_modal_filter_field_tags:
                                // only rows with .has-tags class
                                $this->assertStringContainsString('has-tags', $table_row->getAttribute('class'));
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
                                $this->assertStringContainsString('is-income', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_expense:
                                // only rows with .is-expense class
                                $this->assertStringContainsString('is-expense', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_has_attachment:
                                // only rows with .has-attachments class
                                $this->assertStringContainsString('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_no_attachment:
                                // rows DO NOT CONTAIN .has-attachments class
                                $this->assertStringNotContainsString('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_transfer:
                                // only rows with .is-transfer class
                                $this->assertStringContainsString('is-transfer', $table_row->getAttribute('class'));
                                break;
                            case $this->_selector_modal_filter_field_switch_unconfirmed:
                                // rows DO NOT CONTAIN .is-confirmed class
                                $table_row_class = $table_row->getAttribute('class');
                                $this->assertStringNotContainsString('is-confirmed', $table_row_class);
                                $this->assertStringContainsString('unconfirmed', $table_row_class);
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
                                throw new InvalidArgumentException("Unknown filter parameter provided:".$filter_param);
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
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_body, function(Browser $modal) use (&$filter_value){
                    $accounts = $this->getApiAccounts();
                    $filter_value = collect($accounts)->where('disabled', 0)->random();
                    $this->selectAccountOrAccountTypeValue($modal, $filter_value['id']);
                })

                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function($modal){
                    $modal->click($this->_selector_modal_filter_btn_filter);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_filter)

                ->within($this->_selector_panel_institutions, function(Browser $panel) use ($filter_value){
                    // TODO: confirm "Overview (filtered)" is visible
                    // "overview" is NOT active
                    $overview_classes = $panel->attribute($this->_selector_panel_institutions_overview, 'class');
                    $this->assertStringNotContainsString($this->_class_is_active, $overview_classes);

                    $panel
                        ->click("#institution-".$filter_value['institution_id'].' div')
                        ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS);

                    $account_classes = $panel->attribute('#account-'.$filter_value['id'], 'class');
                    $this->assertStringContainsString($this->_class_is_active, $account_classes);

                    $selector = '#account-'.$filter_value['id'].' '.$this->_selector_panel_institutions_accounts_account_name;
                    $account_name = $panel->text($selector);
                    $this->assertEquals($filter_value['name'], $account_name, "Could not find value at selector:".$panel->resolver->format($selector));
                });
        });
    }

}
