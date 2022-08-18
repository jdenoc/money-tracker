<?php

namespace Tests\Browser;

use App\Models\Account;
use App\Models\AccountType;
use Facebook\WebDriver\Exception\TimeoutException;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;

class SettingsAccountTypesTest extends SettingsBaseTest {

    private static string $SELECTOR_SETTINGS_NAV_ACCOUNT_TYPES = 'li.settings-nav-option:nth-child(3)';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES = 'section#settings-account-types';

    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_NAME = "label[for='settings-account-type-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME = "input#settings-account-type-name:nth-child(2)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_TYPE = "label[for='settings-account-type-type']:nth-child(3)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE = "div:nth-child(4) span.loading";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE = "div:nth-child(4) select#settings-account-type-type";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_LAST_DIGITS = "label[for='settings-account-type-last-digits']:nth-child(5)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS = "input#settings-account-type-last-digits:nth-child(6)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_ACCOUNT = "label[for='settings-account-type-account']:nth-child(7)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT = "div:nth-child(8) span.loading";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT = "div:nth-child(8) select#settings-account-type-account";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_ACTIVE = "label[for='settings-account-type-disabled']:nth-child(9)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE = "div:nth-child(10) #settings-account-type-disabled";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_CREATED = "div:nth-child(11)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_CREATED = "div:nth-child(12)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_MODIFIED = "div:nth-child(13)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_MODIFIED = "div:nth-child(14)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_DISABLED = "div:nth-child(15)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_DISABLED = "div:nth-child(16)";
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_CLEAR = 'button:nth-child(17)';
    private static string $SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE = 'button:nth-child(18)';

    private static string $TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_TYPE_ACCOUNT_TYPE_ID = '#settings-account-type-%d';

    private static string $SELECTOR_SETTINGS_LOADING_ACCOUNT_TYPES =  '#loading-settings-account-types';

    private static string $LABEL_SETTINGS_ACCOUNT_TYPE = 'Account Types';
    private static string $LABEL_SETTINGS_FORM_TYPE = 'Type:';
    private static string $LABEL_SETTINGS_FORM_LAST_DIGITS = 'Last Digits:';
    private static string $LABEL_SETTINGS_FORM_ACCOUNT = 'Account:';
    private static string $LABEL_SETTINGS_ACCOUNT_TYPE_NOTIFICATION_NEW = 'New Account-type created';
    private static string $LABEL_SETTINGS_ACCOUNT_TYPE_NOTIFICATION_UPDATE = 'Account-type updated';

    public function testNavigateToAccountTypeSettingsAndAssertForm(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $section
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_HEADER, self::$LABEL_SETTINGS_ACCOUNT_TYPE)

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_NAME, self::$LABEL_INPUT_NAME)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME)
                        ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, '')

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_TYPE, self::$LABEL_SETTINGS_FORM_TYPE)
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE)
                        ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, "")

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_LAST_DIGITS, self::$LABEL_SETTINGS_FORM_LAST_DIGITS)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS)
                        ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, "")

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_ACCOUNT, self::$LABEL_SETTINGS_FORM_ACCOUNT)
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT)
                        ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, "")

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_ACTIVE, self::$LABEL_INPUT_ACTIVE_STATE)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
                    $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);

                    $section
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_DISABLED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_DISABLED)

                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_CLEAR)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_CLEAR, self::$LABEL_BUTTON_CLEAR)

                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE)
                        ->assertVisible(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE.' svg')
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, self::$LABEL_BUTTON_SAVE);
                    $this->assertElementBackgroundColor($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, $this->color_button_save);
                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEquals('true', $save_button_state);
                });
            });
        });
    }

    public function testAccountTypesListedUnderFormAreVisible(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNT_TYPES, self::$WAIT_SECONDS);

                    $account_types = AccountType::all();
                    $this->assertCount($account_types->count(), $section->elements('hr~ul li'));
                    foreach ($account_types as $account_type){
                        $selector_account_type_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_TYPE_ACCOUNT_TYPE_ID, $account_type->id);
                        $section
                            ->assertVisible($selector_account_type_id)
                            ->assertSeeIn($selector_account_type_id, $account_type->name);
                        $class_is_disabled = 'is-disabled';
                        $class_is_active = 'is-active';
                        $account_type_node_classes = $section->attribute($selector_account_type_id, 'class');
                        if($account_type->disabled){
                            $this->assertStringContainsString($class_is_disabled, $account_type_node_classes);
                            $this->assertStringNotContainsString($class_is_active, $account_type_node_classes);
                        } else {
                            $this->assertStringContainsString($class_is_active, $account_type_node_classes);
                            $this->assertStringNotContainsString($class_is_disabled, $account_type_node_classes);
                        }
                    }
                });
            });
        });
    }

    public function testFormFieldInteractionAndClearButtonFunctionality(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $section
                        ->type(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, $this->faker->word())
                        ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, '');

                    $type = collect(AccountType::getEnumValues())->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, $type)
                        ->assertNotSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, "")

                        ->type(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, $this->faker->numerify("####"))
                        ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, "");

                    $account = Account::get()->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, $account->id)
                        ->assertNotSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, "");

                    $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
                    $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);

                    $save_button_disabled_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_disabled_state);    // if not disabled, the attribute isn't even available

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);
                });
            });
        });
    }

    public function testClickExistingAccountTypeDisplaysDataInFormAndClearingFormThenReclickSameAccountType(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNT_TYPES, self::$WAIT_SECONDS);

                    $account_type = AccountType::get()->random();
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_TYPE_ACCOUNT_TYPE_ID.' span', $account_type->id);
                    $section->click($selector);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account_type);
                    $this->clickClearButton($section);

                    $this->assertFormDefaults($section);
                    $section
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_CREATED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_MODIFIED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_DISABLED)
                        ->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_DISABLED);

                    $section->click($selector);
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $account_type);
                });
            });
        });
    }

    public function testSaveNewAccountType(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $account_types = AccountType::all();
                    do{
                        $account_type_name = $this->faker->word();
                    } while($account_types->contains('name', $account_type_name));
                    $section->type(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, $account_type_name);

                    $type = collect(AccountType::getEnumValues())->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, $type);

                    $section->type(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, $this->faker->numerify("####"));

                    $account = Account::get()->random();
                    $section
                        ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
                        ->select(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, $account->id);

                    $is_account_type_active = $this->faker->boolean();
                    if(!$is_account_type_active){
                        $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
                    }

                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);
                    $section->click(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_ACCOUNT_TYPE_NOTIFICATION_NEW);
                        $this->dismissNotification($body);
                    });

                    $this->assertFormDefaults($section);

                    $new_account_type = AccountType::all()->diff($account_types)->first();
                    $this->assertEquals(!$is_account_type_active, $new_account_type->disabled);
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_TYPE_ACCOUNT_TYPE_ID.'%s', $new_account_type->id, ($is_account_type_active?'.is-active':'.is-disabled'));
                    $section
                        ->assertVisible($selector)
                        ->click($selector.' span');

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $new_account_type);
                });
            });
        });
    }

    public function testSaveExistingAccountType(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToAccountTypesSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertAccountTypesSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES, function(Browser $section){
                    $this->assertFormDefaults($section);
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_ACCOUNT_TYPES, self::$WAIT_SECONDS);

                    $account_type = AccountType::get()->random();
                    $selector_account_type_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_ACCOUNT_TYPE_ACCOUNT_TYPE_ID, $account_type->id);
                    $section
                        ->assertVisible($selector_account_type_id)
                        ->click($selector_account_type_id.' span');

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account_type);

                    $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);

                    $section->click(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_ACCOUNT_TYPE_NOTIFICATION_UPDATE);
                        $this->dismissNotification($body);
                    });
                    $this->assertFormDefaults($section);

                    $account_type = AccountType::find($account_type->id); // get updated account data
                    $account_class_state = $account_type->disabled ? '.is-disabled' : '.is-active';
                    $section->assertVisible($selector_account_type_id.$account_class_state);
                    $section->click($selector_account_type_id.' span');

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $account_type);
                });
            });
        });
    }

    private function navigateToAccountTypesSettingsOnSettingsPage(Browser $browser){
        $this->navigateToSettingsSectionOnSettingsPage($browser, self::$SELECTOR_SETTINGS_NAV_ACCOUNT_TYPES, "Account-types");
    }

    private function assertAccountTypesSettingsDisplayed(Browser $settings_display){
        $this->assertSettingsSectionDisplayed($settings_display, self::$SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES);
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
            ->scrollToElement(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, '')
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, "")
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, "")
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, "");
        $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);
    }

    private function assertFormWithExistingData(Browser $browser, AccountType $accountType){
        $browser
            ->scrollToElement(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_NAME, $accountType->name)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_TYPE, $accountType->type)
            ->assertInputValue(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_INPUT_LAST_DIGITS, $accountType->last_digits)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_SELECT_ACCOUNT, $accountType->account_id);
        if($accountType->disabled){
            $this->assertActiveStateToggleInactive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_TOGGLE_ACTIVE);
        }

        $browser
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_CREATED, self::$LABEL_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_CREATED, $this->convertDateToECMA262Format($accountType->create_stamp))
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_MODIFIED, self::$LABEL_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_MODIFIED, $this->convertDateToECMA262Format($accountType->modified_stamp))
            ->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_LABEL_DISABLED, self::$LABEL_LABEL_DISABLED);
        if(is_null($accountType->disabled_stamp)){
            $browser->assertMissing(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_DISABLED);
        } else {
            $browser->assertSeeIn(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_DISABLED, $this->convertDateToECMA262Format($accountType->disabled_stamp));
        }

        $this->assertElementBackgroundColor($browser, self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, $this->color_button_save);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);    // no changes; so button remains disabled
    }

    private function clickClearButton(Browser $browser){
        $browser
            ->scrollToElement(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_CLEAR)
            ->click(self::$SELECTOR_SETTINGS_ACCOUNT_TYPE_FORM_BUTTON_CLEAR);
    }
}
