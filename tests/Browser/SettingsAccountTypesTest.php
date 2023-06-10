<?php

namespace Tests\Browser;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\BaseModel;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;

/**
 * @group settings
 * @group settings-account-types
 */
class SettingsAccountTypesTest extends SettingsBase {
    use DuskTraitToggleButton;

    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = 'li.settings-nav-option:nth-child(3)';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = 'Account-types';

    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = 'section#settings-account-types';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = "Account Types";

    private static string $SELECTOR_SETTINGS_FORM_LABEL_NAME = "label[for='settings-account-type-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_FORM_INPUT_NAME = "input#settings-account-type-name:nth-child(2)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_TYPE = "label[for='settings-account-type-type']:nth-child(3)";
    private static string $SELECTOR_SETTINGS_FORM_LOADING_TYPE = "div:nth-child(4) span.loading";
    private static string $SELECTOR_SETTINGS_FORM_SELECT_TYPE = "div:nth-child(4) select#settings-account-type-type";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_LAST_DIGITS = "label[for='settings-account-type-last-digits']:nth-child(5)";
    private static string $SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS = "input#settings-account-type-last-digits:nth-child(6)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_ACCOUNT = "label[for='settings-account-type-account']:nth-child(7)";
    private static string $SELECTOR_SETTINGS_FORM_LOADING_ACCOUNT = "div:nth-child(8) span.loading";
    private static string $SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT = "div:nth-child(8) select#settings-account-type-account";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_ACTIVE = "label[for='settings-account-type-disabled']:nth-child(9)";
    protected static string $SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE = "div:nth-child(10) #settings-account-type-disabled";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_CREATED = "div:nth-child(11)";
    private static string $SELECTOR_SETTINGS_FORM_CREATED = "div:nth-child(12)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_MODIFIED = "div:nth-child(13)";
    private static string $SELECTOR_SETTINGS_FORM_MODIFIED = "div:nth-child(14)";
    private static string $SELECTOR_SETTINGS_FORM_LABEL_DISABLED = "div:nth-child(15)";
    private static string $SELECTOR_SETTINGS_FORM_DISABLED = "div:nth-child(16)";
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = 'button:nth-child(17)';
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = 'button:nth-child(18)';

    protected static string $SELECTOR_SETTINGS_LOADING_OBJECTS =  '#loading-settings-account-types';
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = '#settings-account-type-%d';

    private static string $LABEL_SETTINGS_FORM_TYPE = 'Type:';
    private static string $LABEL_SETTINGS_FORM_LAST_DIGITS = 'Last Digits:';
    private static string $LABEL_SETTINGS_FORM_ACCOUNT = 'Account:';
    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = 'New Account-type created';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = 'Account-type updated';

    public function providerDisablingOrRestoringAccount(): array {
        return [];
    }

    public function providerSaveExistingSettingNode(): array {
        return [
            'name'=>[self::$SELECTOR_SETTINGS_FORM_INPUT_NAME],                 // test 7/20
            'type'=>[self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE],                // test 8/20
            'last_digits'=>[self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS],   // test 9/20
            'account'=>[self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT],          // test 10/20
            'active'=>[self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE],            // test 11/20
        ];
    }

    /**
     * Form defaults:
     *   Name: ""
     *   Type: (Empty)
     *   Last Digits: ""
     *   Account: (Empty)
     *   Active State: "Active"
     *   Save button [disabled]
     */
    protected function assertFormDefaults(Browser $section) {
        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '')
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE, "")
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS, "")
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT, "");
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

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_TYPE, self::$LABEL_SETTINGS_FORM_TYPE)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE, "")

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_LAST_DIGITS, self::$LABEL_SETTINGS_FORM_LAST_DIGITS)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS, "")

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_ACCOUNT, self::$LABEL_SETTINGS_FORM_ACCOUNT)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT, "")

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

    protected function assertFormWithExistingData(Browser $section, BaseModel $node) {
        $this->assertObjectIsOfType($node, AccountType::class);

        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, $node->name)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_TYPE, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE, $node->type)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS, $node->last_digits)
            ->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS)
            ->assertSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT, $node->account_id);
        if ($node->disabled) {
            $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        }

        $section
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED, self::$LABEL_SETTINGS_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CREATED, $this->convertDateToECMA262Format($node->create_stamp))
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED, self::$LABEL_SETTINGS_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_MODIFIED, $this->convertDateToECMA262Format($node->modified_stamp))
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED, self::$LABEL_SETTINGS_LABEL_DISABLED);
        if (is_null($node->disabled_stamp)) {
            $section->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED);
        } else {
            $section->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_DISABLED, $this->convertDateToECMA262Format($node->disabled_stamp));
        }

        $this->assertSaveButtonDisabled($section);
    }

    protected function assertNodesVisible(Browser $section) {
        $account_types = AccountType::all();
        $this->assertCount($account_types->count(), $section->elements('hr~ul li'));
        foreach ($account_types as $account_type) {
            $selector_account_type_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID, $account_type->id);
            $section
                ->assertVisible($selector_account_type_id)
                ->assertSeeIn($selector_account_type_id, $account_type->name);
            $class_is_disabled = 'is-disabled';
            $class_is_active = 'is-active';
            $account_type_node_classes = $section->attribute($selector_account_type_id, 'class');
            if ($account_type->disabled) {
                $this->assertStringContainsString($class_is_disabled, $account_type_node_classes);
                $this->assertStringNotContainsString($class_is_active, $account_type_node_classes);
            } else {
                $this->assertStringContainsString($class_is_active, $account_type_node_classes);
                $this->assertStringNotContainsString($class_is_disabled, $account_type_node_classes);
            }
        }
    }

    protected function fillForm(Browser $section) {
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_NAME);
        $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');

        $section->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_TYPE, self::$WAIT_SECONDS);
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE);
        $section->assertNotSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE, "");

        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS);
        $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS, "");

        $section->waitUntilMissing(self::$SELECTOR_SETTINGS_FORM_LOADING_ACCOUNT, self::$WAIT_SECONDS);
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT);
        $section->assertNotSelected(self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT, "");

        $is_account_type_active = fake()->boolean();
        if (!$is_account_type_active) {
            $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
            $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        }
    }

    protected function generateObject(bool $isInitObjectActive): BaseModel {
        return AccountType::factory()->create();
    }

    protected function getObject(int $id=null): BaseModel {
        return AccountType::get()->random();
    }

    protected function getAllObjects(): Collection {
        return AccountType::all();
    }

    protected function interactWithObjectListItem(Browser $section, BaseModel $node, bool $is_fresh_load=true) {
        $this->assertObjectIsOfType($node, AccountType::class);

        $class_state = $node->disabled ? '.is-disabled' : '.is-active';
        $selector_account_type_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID.$class_state, $node->id);
        $section
            ->assertVisible($selector_account_type_id)
            ->click($selector_account_type_id.' span');

        $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
            $this->waitForLoadingToStop($body);
        });
        $section->pause($this->toggleButtonTransitionTimeInMilliseconds());
    }

    protected function interactWithFormElement(Browser $section, string $selector, BaseModel $node=null) {
        if (is_null($node)) {
            $node = new AccountType();
        }
        $this->assertObjectIsOfType($node, AccountType::class);

        switch($selector) {
            case self::$SELECTOR_SETTINGS_FORM_INPUT_NAME:
                $account_types = $this->getAllObjects();
                do {
                    $name = fake()->word();
                } while ($node->name == $name || $account_types->contains('name', $name));
                $section
                    ->clear($selector)
                    ->type($selector, fake()->word());
                break;
            case self::$SELECTOR_SETTINGS_FORM_SELECT_TYPE:
                do {
                    $type = collect(AccountType::getEnumValues())->random();
                } while ($node->type == $type);
                $section->select($selector, $type);
                break;
            case self::$SELECTOR_SETTINGS_FORM_INPUT_LAST_DIGITS:
                do {
                    $last_digits = fake()->numerify("####");
                } while ($node->last_digits == $last_digits);
                $section
                    ->clear($selector)
                    ->type($selector, $last_digits);
                break;
            case self::$SELECTOR_SETTINGS_FORM_SELECT_ACCOUNT:
                do {
                    $account = Account::get()->random();
                } while ($node->account_id == $account->id);
                $section->select($selector, $account->id);
                break;
            case self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE:
                $this->toggleToggleButton($section, $selector);
                break;
            default:
                throw new \UnexpectedValueException(sprintf("Unexpected form element [%s] provided", $selector));
        }
    }

}
