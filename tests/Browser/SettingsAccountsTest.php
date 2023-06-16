<?php

namespace Tests\Browser;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Institution;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;

/**
 * @group settings
 * @group settings-accounts
 */
class SettingsAccountsTest extends SettingsBase {
    use DuskTraitToggleButton;

    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = 'li.settings-nav-option:nth-child(2)';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = 'Accounts';

    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = 'section#settings-accounts';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = "Accounts";

    private static string $SELECTOR_SETTINGS_FORM_LABEL_NAME = "label[for='settings-account-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_FORM_INPUT_NAME = "input#settings-account-name:nth-child(2)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_INSTITUTION = "label[for='settings-account-institution']:nth-child(3)";
    private static string $SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION = "div:nth-child(4) select#settings-account-institution";
    private static string $SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION = "div:nth-child(4) span.loading";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_CURRENCY = 'div:nth-child(5)';
    private static string $SELECTOR_SETTINGS_FORM_RADIO_CURRENCY = "div:nth-child(6) label.settings-account-currency input[name='settings-account-currency']";
    private static string $TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT = 'input#settings-account-currency-%s';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_TOTAL = "label[for='settings-account-total']:nth-child(7)";
    private static string $SELECTOR_SETTINGS_FORM_INPUT_TOTAL = 'div:nth-child(8) input#settings-account-total';
    private static string $SELECTOR_SETTINGS_FORM_CURRENCY_TOTAL = 'div:nth-child(8) input#settings-account-total+span';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_ACTIVE = "label[for='settings-account-active']:nth-child(9)";
    protected static string $SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE = "div:nth-child(10) #settings-account-active";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_CREATED = "div:nth-child(11)";
    private static string $SELECTOR_SETTINGS_FORM_CREATED = "div:nth-child(12)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_MODIFIED = "div:nth-child(13)";
    private static string $SELECTOR_SETTINGS_FORM_MODIFIED = "div:nth-child(14)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_DISABLED = "div:nth-child(15)";
    private static string $SELECTOR_SETTINGS_FORM_DISABLED = "div:nth-child(16)";
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = "button:nth-child(17)";
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = "button:nth-child(18)";

    protected static string $SELECTOR_SETTINGS_LOADING_OBJECTS = "#loading-settings-accounts";
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = '#settings-account-%d';

    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = 'New account created';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = 'Account updated';
    protected static string $LABEL_SETTINGS_NOTIFICATION_RESTORE = 'Account has been enabled';
    protected static string $LABEL_SETTINGS_NOTIFICATION_DELETE = 'Account has been disabled';

    private Currency $default_currency;
    private string $color_currency_active;
    private string $color_currency_inactive;

    public function setUp(): void {
        parent::setUp();
        $this->default_currency = CurrencyHelper::getCurrencyDefaults();
    }

    protected function initSettingsColors() {
        parent::initSettingsColors();
        $this->color_currency_active = $this->tailwindColors->blue(600);
        $this->color_currency_inactive = $this->tailwindColors->white();
    }

    public function providerDisablingOrRestoringAccount(): array {
        return [
            'disabling account'=>['isInitAccountActive'=>true],     // test 7/20
            'restoring account'=>['isInitAccountActive'=>false],    // test 8/20
        ];
    }

    public function providerSaveExistingSettingNode(): array {
        return [
            'name'=>[self::$SELECTOR_SETTINGS_FORM_INPUT_NAME],                         // test 9/20
            'institution'=>[self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION],          // test 10/20
            'currency'=>[self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT],  // test 11/20
            'total'=>[self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL],                       // test 12/20
        ];
    }

    /**
     * Form defaults:
     *   Name: ""
     *   Institution: (Empty)
     *   Currency: "USD"
     *   Total: ""
     *   Active State: "Active"
     *   Save button [disabled]
     */
    protected function assertFormDefaults(Browser $section) {
        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '')
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION, '')
            ->assertRadioSelected(sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT, $this->default_currency->label), $this->default_currency->code)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL, '')
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($this->default_currency->html));
        $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);

        $section
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED);
        $this->assertSaveButtonDisabled($section);
    }

    protected function assertFormDefaultsFull(Browser $section) {
        $section
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_NAME, self::$LABEL_SETTINGS_FORM_INPUT_NAME)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '')

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_INSTITUTION, 'Institution:')
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION, "")

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_CURRENCY, 'Currency:');
        $currencies = CurrencyHelper::fetchCurrencies();
        foreach ($currencies as $currency) {
            $radio_selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT, $currency->label);
            $section
                ->assertInputValue($radio_selector, $currency->code);
            if ($currency->code === $this->default_currency->code) {
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
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_TOTAL, 'Total:')
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL, '')
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($this->default_currency->html))

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_ACTIVE, self::$LABEL_SETTINGS_FORM_LABEL_ACTIVE)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);

        $section
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED)

            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR, self::$LABEL_SETTINGS_FORM_BUTTON_CLEAR);

        $this->assertSaveButtonDefault($section);
    }

    protected function assertFormWithExistingData(Browser $section, BaseModel $object) {
        $this->assertObjectIsOfType($object, Account::class);
        $currency = CurrencyHelper::fetchCurrencies()->where('code', $object->currency)->first();
        $section
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, $object->name)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION, $object->institution_id)
            ->assertRadioSelected(sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT, $currency->label), $currency->code)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL, $object->total)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($currency->html));

        if ($object->disabled) {
            $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        }

        $section
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CREATED, $this->convertDateToECMA262Format($object->create_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_MODIFIED, $this->convertDateToECMA262Format($object->modified_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED);

        if (is_null($object->disabled_stamp)) {
            $section->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED);
        } else {
            $section->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_DISABLED, $this->convertDateToECMA262Format($object->disabled_stamp));
        }

        $this->assertSaveButtonDisabled($section);
    }

    protected function assertNodesVisible(Browser $section) {
        $accounts = Account::withTrashed()->get();
        $this->assertCount($accounts->count(), $section->elements('hr~ul li'));
        foreach ($accounts as $account) {
            $selector_account_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID, $account->id);
            $section
                ->assertVisible($selector_account_id)
                ->assertSeeIn($selector_account_id, $account->name);
            $class_is_disabled = 'is-disabled';
            $class_is_active = 'is-active';
            $account_node_classes = $section->attribute($selector_account_id, 'class');
            if ($account->active) {
                $this->assertStringContainsString($class_is_active, $account_node_classes);
                $this->assertStringNotContainsString($class_is_disabled, $account_node_classes);
            } else {
                $this->assertStringContainsString($class_is_disabled, $account_node_classes);
                $this->assertStringNotContainsString($class_is_active, $account_node_classes);
            }
        }
    }

    protected function fillForm(Browser $section) {
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_NAME);
        $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, "");

        $section->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS);
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION);
        $section->assertNotSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION, "");

        $currency = CurrencyHelper::fetchCurrencies()->random();
        $selector_currency = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT, $currency->label);
        $section
            ->click($selector_currency.'+span')
            ->assertRadioSelected($selector_currency, $currency->code);
        $this->assertElementBackgroundColor($section, $selector_currency, $this->color_currency_active);
        if ($currency->code !== $this->default_currency->code) {
            $selector_default_currency = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT, $this->default_currency->label);
            $section->assertRadioNotSelected($selector_default_currency, $this->default_currency->code);
            $this->assertElementBackgroundColor($section, $selector_default_currency, $this->color_currency_inactive);
        }

        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL);
        $section
            ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL, '')
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CURRENCY_TOTAL, CurrencyHelper::convertCurrencyHtmlToCharacter($currency->html));

        // don't interact with the "active" toggle button
        // doing so would clear the form
    }

    protected function generateObject(bool $isInitObjectActive): BaseModel {
        return Account::factory()->create();
    }

    protected function getObject(int $id=null): BaseModel {
        if (!is_null($id)) {
            return Account::find($id);
        } else {
            return Account::get()->random();
        }
    }

    protected function getAllObjects(): Collection {
        return Account::withTrashed()->get();
    }

    protected function interactWithObjectListItem(Browser $section, BaseModel $node, bool $is_fresh_load=true) {
        $this->assertObjectIsOfType($node, Account::class);
        $class_state = $node->disabled ? '.is-disabled' : '.is-active';
        $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID.$class_state.' span', $node->id);
        $section
            ->assertVisible($selector)
            ->click($selector);

        if ($is_fresh_load) {
            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                $this->waitForLoadingToStop($body);
            });
        }
        $section->pause($this->toggleButtonTransitionTimeInMilliseconds());
    }

    protected function interactWithFormElement(Browser $section, string $selector, BaseModel $node=null) {
        if (is_null($node)) {
            $node = new Account();
        }
        $this->assertObjectIsOfType($node, Account::class);

        switch($selector) {
            case self::$SELECTOR_SETTINGS_FORM_INPUT_NAME:
                $accounts = $this->getAllObjects();
                do {
                    $name = fake()->word();
                } while ($node->name == $name || $accounts->contains('name', $name));
                $section
                    ->clear($selector)
                    ->type($selector, $name);
                break;
            case self::$SELECTOR_SETTINGS_FORM_SELECT_INSTITUTION:
                do {
                    $institution = Institution::get()->random();
                } while ($node->institution_id == $institution->id);
                $section
                    ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_INSTITUTION, self::$WAIT_SECONDS)
                    ->select($selector, $institution->id);
                break;
            case self::$TEMPLATE_SELECTOR_SETTINGS_FORM_RADIO_CURRENCY_INPUT:
                do {
                    $currency = CurrencyHelper::fetchCurrencies()->random();
                } while ($node->currency == $currency->code);
                $selector_currency = sprintf($selector, $currency->label);
                $section
                    ->click($selector_currency.'+span')
                    ->assertRadioSelected($selector_currency, $currency->code);
                break;
            case self::$SELECTOR_SETTINGS_FORM_INPUT_TOTAL:
                do {
                    $total = fake()->randomFloat(2);
                } while ($node->total == $total);
                $section
                    ->clear($selector)
                    ->type($selector, $total);
                break;
            case self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE:
                $this->toggleToggleButton($section, $selector);
                break;
            default:
                throw new \UnexpectedValueException(sprintf("Unexpected form element [%s] provided", $selector));
        }
    }

}
