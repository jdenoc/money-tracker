<?php

namespace Tests\Browser;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Institution;
use Facebook\WebDriver\Exception\TimeOutException;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;

class SettingsAccountsTest extends SettingsBaseTest {

    private static string $SELECTOR_SETTINGS_NAV_ACCOUNTS = 'li.settings-nav-option:nth-child(2)';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS = 'section#settings-accounts';

    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_NAME = "label[for='settings-account-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME = "input#settings-account-name:nth-child(2)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_INSTITUTION = "label[for='settings-account-institution']:nth-child(3)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION = "div:nth-child(4) select#settings-account-institution";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION = "div:nth-child(4) span.loading";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CURRENCY = 'div:nth-child(5)';
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY = "div:nth-child(6) label.settings-account-currency input[name='settings-account-currency']";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_TOTAL = "label[for='settings-account-total']:nth-child(7)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL = 'div:nth-child(8) input#settings-account-total';
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_CURRENCY_TOTAL = 'div:nth-child(8) input#settings-account-total+span';
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_ACTIVE = "label[for='settings-account-disabled']:nth-child(9)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE = "div:nth-child(10) #settings-account-disabled";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CREATED = "div:nth-child(11)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_CREATED = "div:nth-child(12)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_MODIFIED = "div:nth-child(13)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_MODIFIED = "div:nth-child(14)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_DISABLED = "div:nth-child(15)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_DISABLED = "div:nth-child(16)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_CLEAR = "button:nth-child(17)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE = "button:nth-child(18)";

    private static string $SELECTOR_SETTINGS_LOADING_ACCOUNTS = "#loading-settings-accounts";
    private static string $TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT = 'input#settings-account-currency-%s';
    private static string $TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_ACCOUNT_ID = '#settings-account-%d';

    private static string $LABEL_SETTINGS_ACCOUNT = "Accounts";
    private static string $LABEL_SETTINGS_ACCOUNT_NOTIFICATION_ACCOUNT_NEW = 'New account created';
    private static string $LABEL_SETTINGS_ACCOUNT_NOTIFICATION_ACCOUNT_UPDATE = 'Account updated';

    private Currency $default_currency;
    private string $color_currency_active;
    private string $color_currency_inactive;

    public function setUp():void {
        parent::setUp();
        $this->default_currency = CurrencyHelper::getCurrencyDefaults();
        $this->color_currency_active = $this->tailwindColors->blue(600);
        $this->color_currency_inactive = $this->tailwindColors->white();
    }

    public function testNavigateToAccountSettingsAndAssertForm(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $section
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_HEADER, self::$LABEL_SETTINGS_ACCOUNT)

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_NAME, self::$LABEL_INPUT_NAME)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME)
                        ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, '')

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_INSTITUTION, 'Institution:')
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION)
                        ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, "")

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CURRENCY, 'Currency:');
                    $currencies = CurrencyHelper::fetchCurrencies();
                    foreach($currencies as $currency){
                        $radio_selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT, $currency->label);
                        $section
                            ->assertInputValue($radio_selector, $currency->code);
                        if($currency->code === $this->default_currency->code){
                            $section
                                ->assertRadioSelected($radio_selector, $currency->code)
                                ->assertSeeIn($radio_selector.'+span', $currency->code);
                            $this->assertElementBackgroundColor($section, $radio_selector, $this->color_currency_active);
                        } else {
                            $section
                                ->assertRadioNotSelected($radio_selector, $currency->code)
                                ->assertSeeIn($radio_selector.'+span', $currency->code);
                            $this->assertElementBackgroundColor($section, $radio_selector, $this->color_currency_inactive);
                        }
                    }

                    $section
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_TOTAL, 'Total:')
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL)
                        ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, '')
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($this->default_currency->html))

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_ACTIVE, self::$LABEL_INPUT_ACTIVE_STATE)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
                    $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);

                    $section
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_DISABLED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_DISABLED)

                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_CLEAR)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_CLEAR, self::$LABEL_BUTTON_CLEAR)

                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE.' svg')
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, self::$LABEL_BUTTON_SAVE);
                    $this->assertElementBackgroundColor($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, $this->color_button_save);
                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEquals('true', $save_button_state);
                });
            });
        });
    }

    public function testAccountsListedUnderFormAreVisible(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNTS, self::$WAIT_SECONDS);

                    $accounts = Account::all();
                    $this->assertCount($accounts->count(), $section->elements('hr~ul li'));
                    foreach ($accounts as $account){
                        $selector_account_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_ACCOUNT_ID, $account->id);
                        $section
                            ->assertVisible($selector_account_id)
                            ->assertSeeIn($selector_account_id, $account->name);
                        $class_is_disabled = 'is-disabled';
                        $class_is_active = 'is-active';
                        $account_node_classes = $section->attribute($selector_account_id, 'class');
                        if($account->disabled){
                            $this->assertStringContainsString($class_is_disabled, $account_node_classes);
                            $this->assertStringNotContainsString($class_is_active, $account_node_classes);
                        } else {
                            $this->assertStringContainsString($class_is_active, $account_node_classes);
                            $this->assertStringNotContainsString($class_is_disabled, $account_node_classes);
                        }
                    }
                });
            });
        });
    }

    public function testFormFieldInteractionAndClearButtonFunctionality(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $section
                        ->type(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, $this->faker->word())
                        ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, "");

                    $institution = Institution::get()->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, $institution->id)
                        ->assertNotSelected(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, "");

                    $currencies = CurrencyHelper::fetchCurrencies();
                    $currency = $currencies->where('code', '!=', $this->default_currency->code)->random();
                    $selector_currency = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT, $currency->label);
                    $section
                        ->click($selector_currency.'+span')
                        ->assertRadioSelected($selector_currency, $currency->code);
                    $this->assertElementBackgroundColor($section, $selector_currency, $this->color_currency_active);
                    $selector_default_currency = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT, $this->default_currency->label);
                    $section->assertRadioNotSelected($selector_default_currency, $this->default_currency->code);
                    $this->assertElementBackgroundColor($section, $selector_default_currency, $this->color_currency_inactive);

                    $section
                        ->type(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, $this->faker->randomFloat(2))
                        ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, '')
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($currency->html));

                    $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
                    $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);

                    $save_button_disabled_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_disabled_state);    // if not disabled, the attribute isn't even available

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);
                });
            });
        });
    }

    public function testClickExistingAccountDisplaysDataInFormAndClearingFormThenReclickSameAccount(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNTS, self::$WAIT_SECONDS);

                    $account = Account::get()->random();
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_ACCOUNT_ID.' span', $account->id);
                    $section->click($selector);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account);
                    $this->clickClearButton($section);

                    $this->assertFormDefaults($section);
                    $section
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_DISABLED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_DISABLED);

                    $section->click($selector);
                    $this->assertFormWithExistingData($section, $account);
                });
            });
        });
    }

    public function testSaveNewAccount(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $accounts = Account::all();
                    do{
                        $account_name = $this->faker->word();
                    } while($accounts->contains('name', $account_name));

                    $section->type(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, $account_name);

                    $institution = Institution::get()->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, $institution->id);

                    $currency = CurrencyHelper::fetchCurrencies()->random();
                    $section->click(sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT.'+span', $currency->label));

                    $section->type(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, $this->faker->randomFloat(2));

                    $is_account_active = $this->faker->boolean();
                    if(!$is_account_active){
                        $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
                    }

                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);
                    $section->click(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_ACCOUNT_NOTIFICATION_ACCOUNT_NEW);
                        $this->dismissNotification($body);
                    });

                    $this->assertFormDefaults($section);

                    $new_account = Account::all()->diff($accounts)->first();
                    $this->assertEquals(!$is_account_active, $new_account->disabled);
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_ACCOUNT_ID.'%s', $new_account->id, ($is_account_active?'.is-active':'.is-disabled'));
                    $section
                        ->assertVisible($selector)
                        ->click($selector.' span');
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $new_account);
                });
            });
        });
    }

    public function testSaveExistingAccount(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS, function(Browser $section){
                    $this->assertFormDefaults($section);
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNTS, self::$WAIT_SECONDS);

                    $account = Account::all()->random();
                    $selector_account_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_ACCOUNT_ID, $account->id);
                    $section
                        ->assertVisible($selector_account_id)
                        ->click($selector_account_id.' span');

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account);

                    $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);

                    $section->click(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_ACCOUNT_NOTIFICATION_ACCOUNT_UPDATE);
                        $this->dismissNotification($body);
                    });
                    $this->assertFormDefaults($section);

                    $account = Account::find($account->id); // get updated account data
                    $account_class_state = $account->disabled ? '.is-disabled' : '.is-active';
                    $section->assertVisible($selector_account_id.$account_class_state);
                    $section->click($selector_account_id.' span');
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account);
                });
            });
        });
    }

    private function navigateToAccountsSettingsOnSettingsPage(Browser $browser){
         $this->navigateToSettingsSectionOnSettingsPage($browser, self::$SELECTOR_SETTINGS_NAV_ACCOUNTS, self::$LABEL_SETTINGS_ACCOUNT);
    }

    private function assertAccountsSettingsDisplayed(Browser $settings_display){
         $this->assertSettingsSectionDisplayed($settings_display, self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS);
    }

    /**
     * @param Browser $browser
     * @throws TimeOutException
     *
     * Form defaults:
     *   Name: (empty)
     *   Institution: (Empty)
     *   Currency: "USD"
     *   Total: ""
     *   Active State: "Active"
     *   Save button [disabled]
     */
    private function assertFormDefaults(Browser $browser){
        $browser
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, '')
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, '')
            ->assertRadioSelected(sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT, $this->default_currency->label), $this->default_currency->code)
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, '')
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($this->default_currency->html));
        $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);
    }

    private function assertFormWithExistingData(Browser $browser, Account $account){
        $currency = CurrencyHelper::fetchCurrencies()->where('code', $account->currency)->first();
        $browser
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_NAME, $account->name)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_SELECT_INSTITUTION, $account->institution_id)
            ->assertRadioSelected(sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_FORM_RADIO_CURRENCY_INPUT, $currency->label), $currency->code)
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_INPUT_TOTAL, $account->total)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($currency->html));
        if($account->disabled){
            $this->assertActiveStateToggleInactive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_FORM_TOGGLE_ACTIVE);
        }

        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_CREATED, $this->convertDateToECMA262Format($account->create_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_MODIFIED, $this->convertDateToECMA262Format($account->modified_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_LABEL_DISABLED);
        if(is_null($account->disabled_stamp)){
            $browser->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_DISABLED);
        } else {
            $browser->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_DISABLED, $this->convertDateToECMA262Format($account->disabled_stamp));
        }

        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);    // no changes; so button remains disabled
    }

    private function clickClearButton(Browser $browser){
        $browser->click(self::$SELECTOR_SETTINGS_ACCOUNT_FORM_BUTTON_CLEAR);
    }

}
