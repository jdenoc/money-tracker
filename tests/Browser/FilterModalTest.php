<?php

namespace Tests\Browser;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\AccountType;
use App\Traits\Tests\Dusk\BrowserDateUtil as DuskTraitBrowserDateUtil;
use App\Traits\Tests\Dusk\FilterModal as DuskTraitFilterModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\WithTailwindColors;
use Facebook\WebDriver\WebDriverBy;
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
    use DuskTraitBrowserDateUtil;
    use DuskTraitFilterModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitTagsInput;
    use HomePageSelectors;
    use WithTailwindColors;

    // variables
    private $_default_currency_character;

    public function __construct($name = null) {
        parent::__construct($name);
        $this->initFilterModalTogglingSelectorLabelId();

        $default_currency = CurrencyHelper::getCurrencyDefaults();
        $this->_default_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter($default_currency->html);
    }

    public function setUp(): void {
        parent::setUp();
        $this->initFilterModalColors();
        $this->initTagsInputColors();
    }

    /**
     * @group filter-modal-1
     * test 1/20
     */
    public function testModalHeaderHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_head, function(Browser $modal) {
                    $modal
                        ->assertSee("Filter Entries")
                        ->assertVisible($this->_selector_modal_btn_close);
                });
        });
    }

    /**
     * @group filter-modal-1
     * test 2/20
     */
    public function testModalBodyHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use ($accounts) {
                    $modal
                        // start date - input
                        ->assertSee("Start Date:")
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE)
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE, "");
                    $this->assertEquals(
                        'date',
                        $modal->attribute(self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE, 'type'),
                        self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE.' is not type="date"'
                    );

                    $modal
                        // end date - input
                        ->assertSee("End Date:")
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE)
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE, "");
                    $this->assertEquals(
                        'date',
                        $modal->attribute(self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE, 'type'),
                        self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE.' is not type="date"'
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
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // expense - switch
                    $modal->assertSee("Expense:");
                    $this->assertToggleButtonState(
                        $modal,
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // has attachment - switch
                    $modal->assertSee("Has Attachment(s):");
                    $this->assertToggleButtonState(
                        $modal,
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // no attachment - switch
                    $modal->assertSee("No Attachment(s):");
                    $this->assertToggleButtonState(
                        $modal,
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // is transfer - switch
                    $modal->assertSee("Transfer:");
                    $this->assertToggleButtonState(
                        $modal,
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // unconfirmed - switch
                    $modal->assertSee("Not Confirmed:");
                    $this->assertToggleButtonState(
                        $modal,
                        self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED,
                        $this->_label_switch_disabled,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    // min range - input
                    $modal
                        ->assertSee("Min Range:")
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE)
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, "");
                    $this->assertEquals(
                        'text',
                        $modal->attribute(self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, 'type'),
                        self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE.' is not type="text"'
                    );

                    // max range - input
                    $modal
                        ->assertSee("Max Range:")
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE)
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, "");
                    $this->assertEquals(
                        'text',
                        $modal->attribute(self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, 'type'),
                        self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE.' is not type="text"'
                    );
                });
        });
    }

    /**
     * @group filter-modal-1
     * test 3/20
     */
    public function testModalFooterHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_BTN_CANCEL)
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_BTN_RESET)
                        ->assertSee($this->_label_btn_reset)
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_BTN_FILTER)
                        ->assertSee($this->_label_btn_filter);
                });
        });
    }

    /**
     * @group filter-modal-1
     * test 4/20
     */
    public function testModalHasTheCorrectNumberOfInputs() {
        $filter_modal_field_selectors = $this->filterModalInputs();

        $this->browse(function(Browser $browser) use ($filter_modal_field_selectors) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_modal_field_selectors) {
                    $filter_modal_elements = $modal->elements('.filter-modal-element');
                    $this->assertCount(count($filter_modal_field_selectors), $filter_modal_elements);
                });
        });
    }

    /**
     * @group filter-modal-1
     * test 5/20
     */
    public function testCloseTransferModalWithXButtonInHeader() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_head, function(Browser $modal) {
                    $modal->click($this->_selector_modal_btn_close);
                })
                ->assertMissing(self::$SELECTOR_MODAL_FILTER);
        });
    }

    /**
     * @group filter-modal-1
     * test 6/20
     */
    public function testCloseTransferModalWithCancelButtonInFooter() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal->click(self::$SELECTOR_MODAL_FILTER_BTN_CANCEL);
                })
                ->assertMissing(self::$SELECTOR_MODAL_FILTER);
        });
    }

    /**
     * @group filter-modal-1
     * test 7/20
     */
    public function testCloseFilterModalWithHotkey() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->assertVisible(self::$SELECTOR_MODAL_FILTER)
                ->keys('', "{escape}")
                ->assertMissing(self::$SELECTOR_MODAL_FILTER);
        });
    }

    /**
     * @group filter-modal-1
     * test 8/20
     */
    public function testOpenFilterModalWithHotkey() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing(self::$SELECTOR_MODAL_FILTER)
                ->keys('', "{control}", "k")
                ->assertVisible(self::$SELECTOR_MODAL_FILTER);
        });
    }

    public static function providerFlipAccountAndAccountTypeSwitch(): array {
        return [
            // [$has_disabled_account, $has_disabled_account_type]
            'where account is NOT disabled & account-type is disabled'=>[false, true],   // test 9/20
            'where account is NOT disabled & account-type is NOT disabled'=>[false, false],  // test 10/20
            'where account is disabled & account-type is NOT disabled'=>[true, false],   // test 11/20
            'where account is disabled & account-type is disabled'=>[true, true],    // test 12/20
        ];
    }

    /**
     * @dataProvider providerFlipAccountAndAccountTypeSwitch
     *
     * @group filter-modal-1
     * test (see provider)/20
     */
    public function testFlipAccountAndAccountTypeSwitch(bool $has_disabled_account, bool $has_disabled_account_type) {
        DB::table('accounts')->truncate();
        DB::table('account_types')->truncate();

        $institutions = $this->getApiInstitutions();
        $institution_id = collect($institutions)->pluck('id')->random(1)->first();

        Account::factory()->count(3)->create(['institution_id' => $institution_id]);
        if ($has_disabled_account) {
            Account::factory()->count(1)->disabled()->create(['institution_id' => $institution_id]);
        }
        $accounts = $this->getApiAccounts();

        AccountType::factory()->count(3)->create(['account_id' => collect($accounts)->pluck('id')->random(1)->first()]);
        if ($has_disabled_account_type) {
            AccountType::factory()->count(1)->disabled()->create(['account_id' => collect($accounts)->pluck('id')->random(1)->first()]);
        }
        $account_types = $this->getApiAccountTypes();

        $this->browse(function(Browser $browser) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use ($has_disabled_account, $has_disabled_account_type, $accounts, $account_types) {
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($modal, $accounts);

                    if ($has_disabled_account) {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsVisible($modal);
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                        $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $accounts);
                        // click again to reset state for account-types
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                    } else {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible($modal);
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, $this->_default_currency_character);

                    // select an account and confirm the name in the select changes
                    $account_to_select = collect($accounts)->where('active', true)->random();
                    $this->selectAccountOrAccountTypeValue($modal, $account_to_select['id']);
                    $modal->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $account_to_select['name']);

                    // test select account changes currency displayed in "Min Range" & "Max Range" fields
                    $field_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter(CurrencyHelper::getCurrencyHtmlFromCode($account_to_select['currency']));
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, $field_currency_character);
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, $field_currency_character);

                    $this->toggleAccountOrAccountTypeSwitch($modal);
                    $this->assertToggleButtonState(
                        $modal,
                        $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_id_label),
                        self::$LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNTTYPE,
                        self::$COLOR_FILTER_SWITCH_DEFAULT
                    );

                    $modal
                        ->assertSelectHasOption(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                        ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                        ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT);
                    $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $account_types);

                    if ($has_disabled_account_type) {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsVisible($modal);
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($modal);
                        $this->assertSelectOptionValuesOfAccountOrAccountType($modal, $account_types);
                    } else {
                        $this->assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible($modal);
                    }

                    // test currency displayed in "Min Range" & "Max Range" fields is $
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, $this->_default_currency_character);

                    // select an account and confirm the name in the select changes
                    $account_type_to_select = fake()->randomElement($account_types);
                    $this->selectAccountOrAccountTypeValue($modal, $account_type_to_select['id']);
                    $modal->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $account_type_to_select['name']);

                    // test select account-type changes currency displayed in "Min Range" & "Max Range" fields
                    $account_from_account_type = collect($accounts)->where('id', '=', $account_type_to_select['account_id'])->first();
                    $field_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter(CurrencyHelper::getCurrencyHtmlFromCode($account_from_account_type['currency']));
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, $field_currency_character);
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, $field_currency_character);
                });
        });
    }

    private function assertValueFieldCurrency(Browser $modal, string $selector, string $currency_symbol): void {
        $value_currency = $modal->text($selector." + span.currency-symbol");
        $this->assertStringContainsString($currency_symbol, $value_currency);
    }

    public static function providerFlipSwitch(): array {
        return [
            "flip income" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME],                 // test 13/20
            "flip expense" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE],               // test 14/20
            "flip has-attachment" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT], // test 15/20
            "flip no-attachment" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT],   // test 16/20
            "flip transfer" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER],             // test 17/20
            "flip not confirmed" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED],     // test 18/20
        ];
    }

    /**
     * @dataProvider providerFlipSwitch
     *
     * @group filter-modal-1
     * test (see provider)/20
     */
    public function testFlipSwitch(string $switch_selector) {
        $this->browse(function(Browser $browser) use ($switch_selector) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER, function(Browser $modal) use ($switch_selector) {
                    $this->assertToggleButtonState($modal, $switch_selector, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_INACTIVE);
                    $this->toggleToggleButton($modal, $switch_selector);
                    $this->assertToggleButtonState($modal, $switch_selector, $this->_label_switch_enabled, self::$COLOR_FILTER_SWITCH_ACTIVE);
                });
        });
    }

    public static function providerRangeValueConvertsIntoDecimalOfTwoPlaces(): array {
        return [
            'Min Range' => [self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE], // test 19/20
            'Max Range' => [self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE], // test 20/20
        ];
    }

    /**
     * @dataProvider providerRangeValueConvertsIntoDecimalOfTwoPlaces
     *
     * @group filter-modal-1
     * test (see provider)/20
     */
    public function testRangeValueConvertsIntoDecimalOfTwoPlaces($field_selector) {
        $this->browse(function(Browser $browser) use ($field_selector) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use ($field_selector) {
                    $modal
                        ->type($field_selector, "rh48r7th72.9ewd3dadh1")
                        ->click(sprintf('label[for="%s"]', ltrim($field_selector, '#')))
                        ->assertInputValue($field_selector, "48772.93");
                });
        });
    }

    public static function providerFlippingCompanionSwitches(): array {
        return [
            "flip income with expense" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE],                              // test 1/20
            "flip expense with income" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME],                              // test 2/20
            "flip has-attachment with no-attachment" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT],  // test 3/20
            "flip no-attachment with has-attachment" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT],  // test 4/20
        ];
    }

    /**
     * @dataProvider providerFlippingCompanionSwitches
     *
     * @group filter-modal-2
     * test (see provider)/20
     */
    public function testFlippingCompanionSwitches(string $init_switch_selector, string $companion_switch_selector) {
        $this->browse(function(Browser $browser) use ($init_switch_selector, $companion_switch_selector) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within(self::$SELECTOR_MODAL_FILTER, function(Browser $modal) use ($init_switch_selector, $companion_switch_selector) {
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_INACTIVE);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_INACTIVE);

                    $this->toggleToggleButton($modal, $init_switch_selector);
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_enabled, self::$COLOR_FILTER_SWITCH_ACTIVE);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_INACTIVE);

                    $this->toggleToggleButton($modal, $companion_switch_selector);
                    $this->assertToggleButtonState($modal, $companion_switch_selector, $this->_label_switch_enabled, self::$COLOR_FILTER_SWITCH_ACTIVE);
                    $this->assertToggleButtonState($modal, $init_switch_selector, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_INACTIVE);
                });
        });
    }

    /**
     * @group filter-modal-2
     * test 5/20
     */
    public function testResetFields() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) {
                    $time_from_browser = $this->getBrowserLocaleDate($modal);
                    $start_date = $this->processLocaleDateForTyping($time_from_browser);

                    $account_types = $this->getApiAccountTypes();
                    $account_type = fake()->randomElement($account_types);

                    $tags = $this->getApiTags();
                    $tags_to_select = fake()->unique->randomElements($tags, fake()->numberBetween(1, count($tags)));

                    $companion_switch_set_1 = [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME];
                    $companion_switch_set_2 = [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT];

                    $modal
                        ->type(self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE, $start_date)
                        ->type(self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE, $start_date);
                    $this->toggleAccountOrAccountTypeSwitch($modal);
                    $this->selectAccountOrAccountTypeValue($modal, $account_type['id']);

                    foreach ($tags_to_select as $tag_to_select) {
                        $this->fillTagsInputUsingAutocomplete($modal, $tag_to_select['name']);
                    }

                    $this->toggleToggleButton($modal, fake()->randomElement($companion_switch_set_1));
                    $this->toggleToggleButton($modal, fake()->randomElement($companion_switch_set_2));
                    $modal
                        ->click(self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER)
                        ->click(self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED)
                        ->type(self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, "65.43")
                        ->type(self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, "9.87");
                })

                // click reset button
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal
                        ->assertVisible(self::$SELECTOR_MODAL_FILTER_BTN_RESET)
                        ->click(self::$SELECTOR_MODAL_FILTER_BTN_RESET)
                        ->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS);
                })

                // confirm all fields have been reset
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) {
                    $modal
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE, '')
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE, '')
                        ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, '')
                        ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT);

                    $this->assertDefaultStateOfTagsInput($modal);

                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $this->assertToggleButtonState($modal, self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED, $this->_label_switch_disabled, self::$COLOR_FILTER_SWITCH_DEFAULT);
                    $modal
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, "")
                        ->assertInputValue(self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, "");

                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE, $this->_default_currency_character);
                    $this->assertValueFieldCurrency($modal, self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE, $this->_default_currency_character);
                });
        });
    }

    /**
     * @group filter-modal-2
     * test 6/20
     */
    public function testClickFilterButtonToUpdateInstitutionsPanelActive() {
        $this->browse(function(Browser $browser) {
            $filter_value = [];
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use (&$filter_value) {
                    $accounts = $this->getApiAccounts();
                    $filter_value = collect($accounts)->where('active', true)->random();
                    $this->selectAccountOrAccountTypeValue($modal, $filter_value['id']);
                })

                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_foot, function($modal) {
                    $modal->click(self::$SELECTOR_MODAL_FILTER_BTN_FILTER);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing(self::$SELECTOR_MODAL_FILTER)

                ->within($this->_selector_panel_institutions, function(Browser $panel) use ($filter_value) {
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

    public static function providerClickFilterButtonToFilterResults(): array {
        return self::filterModalInputs();  // test (7 - 18)/20
    }

    /**
     * @dataProvider providerClickFilterButtonToFilterResults
     * @param $filter_param
     *
     * @throws Throwable
     *
     * @group filter-modal-2
     * test (see provider)/20
     */
    public function testClickFilterButtonToFilterResults($filter_param) {
        $this->browse(function(Browser $browser) use ($filter_param) {
            $filter_value = null;
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                // modify all (or at least most) fields
                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_body, function(Browser $modal) use ($filter_param, &$filter_value) {
                    $filter_value = $this->filterModalInputInteraction($modal, $filter_param);
                })

                ->within(self::$SELECTOR_MODAL_FILTER.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal->click(self::$SELECTOR_MODAL_FILTER_BTN_FILTER);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing(self::$SELECTOR_MODAL_FILTER)

                // confirm only rows matching the filter parameters are shown
                ->within($this->_selector_table.' '.$this->_selector_table_body, function(Browser $table) use ($filter_param, $filter_value) {
                    $table_rows = $table->elements('tr');
                    foreach ($table_rows as $table_row) {
                        switch($filter_param) {
                            case self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE:
                                // only rows with dates >= $start_date
                                $row_entry_date = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_date))->getText();
                                $this->assertGreaterThanOrEqual(
                                    strtotime($filter_value['actual']),
                                    strtotime($row_entry_date),
                                    'Row date "'.$row_entry_date.'" less than filter start date "'.$filter_value['actual'].'" typed as "'.$filter_value['typed'].'"'
                                );
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE:
                                // only rows with dates <= $end_date
                                $row_entry_date = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_date))->getText();
                                $this->assertLessThanOrEqual(
                                    strtotime($filter_value['actual']),
                                    strtotime($row_entry_date),
                                    'Row date "'.$row_entry_date.'" greater than filter start date "'.$filter_value['actual'].'" typed as "'.$filter_value['typed'].'"'
                                );
                                break;
                            case self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT:
                                if (is_array($filter_value)) {    // account
                                    $account_type_names = AccountType::withTrashed()->whereIn('id', $filter_value)->pluck('name')->all();
                                } else {    // account-type
                                    $account_type_names = AccountType::withTrashed()->where('id', $filter_value)->pluck('name')->all();
                                }

                                // rows only display account-type(s)
                                $row_entry_account_type = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))->getText();
                                $this->assertContains($row_entry_account_type, $account_type_names);
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_TAGS:
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
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME:
                                // only rows with .is-income class
                                $this->assertStringContainsString('is-income', $table_row->getAttribute('class'));
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE:
                                // only rows with .is-expense class
                                $this->assertStringContainsString('is-expense', $table_row->getAttribute('class'));
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT:
                                // only rows with .has-attachments class
                                $this->assertStringContainsString('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT:
                                // rows DO NOT CONTAIN .has-attachments class
                                $this->assertStringNotContainsString('has-attachments', $table_row->getAttribute('class'));
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER:
                                // only rows with .is-transfer class
                                $this->assertStringContainsString('is-transfer', $table_row->getAttribute('class'));
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED:
                                // rows DO NOT CONTAIN .is-confirmed class
                                $table_row_class = $table_row->getAttribute('class');
                                $this->assertStringNotContainsString('is-confirmed', $table_row_class);
                                $this->assertStringContainsString('unconfirmed', $table_row_class);
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE:
                                // only rows with value >= min_value
                                $row_entry_value = $table_row->findElement(WebDriverBy::cssSelector($this->_selector_table_row_value))->getText();
                                $this->assertGreaterThanOrEqual($filter_value, $row_entry_value);
                                break;
                            case self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE:
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

}
