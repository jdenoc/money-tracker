<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\Dusk\AccountOrAccountTypeSelector as DuskTraitAccountOrAccountTypeSelector;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\WaitTimes;
use Exception;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait AccountOrAccountTypeTogglingSelector {
    use DuskTraitAccountOrAccountTypeSelector;
    use DuskTraitToggleButton;
    use WaitTimes;

    // selectors
    private static string $SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT = ".select-account-or-account-types-id";

    // labels
    private static string $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNT = "Account";
    private static string $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNTTYPE = "Account Type";
    private static string $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_DEFAULT = "Account";
    private static string $LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT = "[ ALL ]";

    // variables
    private string $_account_or_account_type_toggling_selector_id_label;
    private bool $_disable_checkbox_checked = false;

    private function assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet(): void {
        if (!$this->_account_or_account_type_toggling_selector_id_label) {
            throw new Exception("variable \$_account_or_account_type_toggling_selector_id_label has not been set");
        }
    }

    protected function getAccountOrAccountTypeTogglingSelectorComponentId(string $id_label): string {
        $selector_pattern_component_account_or_account_type_toggling_selector = "#account-or-account-type-toggling-selector-for-%s";
        return sprintf($selector_pattern_component_account_or_account_type_toggling_selector, $id_label);
    }

    protected function getSwitchAccountAndAccountTypeId(string $id_label): string {
        $selector_pattern_field_toggle_switch_account_and_account_type = "#toggle-account-and-account-types-for-%s";
        return sprintf($selector_pattern_field_toggle_switch_account_and_account_type, $id_label);
    }

    protected function getCheckboxShowDisabledAccountOrAccountType(string $id_label): string {
        $selector_pattern_field_checkbox_show_disabled = "#show-disabled-accounts-or-account-types-%s-checkbox";
        return sprintf($selector_pattern_field_checkbox_show_disabled, $id_label);
    }

    protected function getLabelSelectorLabelShowDisabledAccountsOrAccountTypes(): string {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $selector_pattern_field_checkbox_show_disabled = $this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_id_label);
        return 'label[for="'.ltrim($selector_pattern_field_checkbox_show_disabled, "#").'"].show-disabled-accounts-or-account-types';
    }

    public function assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent(Browser $browser, array $accounts): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser
            // component
            ->assertVisible($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label))
            ->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) use ($accounts) {
                $color_switch_default = $this->tailwindColors->gray(400);

                // account/account-type - switch
                $this->assertToggleButtonState(
                    $component,
                    $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_id_label),
                    self::$LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_DEFAULT,
                    $color_switch_default
                );

                // account/account-type - select
                $component
                    ->assertVisible(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT)
                    ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT);

                $this->waitUntilSelectLoadingIsMissing($component, self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT);
                $component
                    ->assertSelectHasOption(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSelectHasOptions(
                        self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT,
                        collect($accounts)->where('active', true)->pluck('id')->toArray()
                    );

                // disable checkbox
                $component->assertMissing($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_id_label));
                if (collect($accounts)->where('active', false)->count() > 0) {
                    $component->assertVisible($this->getLabelSelectorLabelShowDisabledAccountsOrAccountTypes());
                } else {
                    $component->assertMissing($this->getLabelSelectorLabelShowDisabledAccountsOrAccountTypes());
                }
            });
    }

    public function assertShowDisabledAccountOrAccountTypeCheckboxIsVisible(Browser $browser): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) {
            $component->assertVisible($this->getLabelSelectorLabelShowDisabledAccountsOrAccountTypes());
        });
    }

    public function assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible(Browser $browser): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) {
            $component->assertMissing($this->getLabelSelectorLabelShowDisabledAccountsOrAccountTypes());
        });
    }

    public function toggleAccountOrAccountTypeSwitch(Browser $browser): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) {
            // account/account-type - switch
            $this->toggleToggleButton($component, $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_id_label));
        });
    }

    public function toggleShowDisabledAccountOrAccountTypeCheckbox(Browser $browser): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) {
            // make sure accounts have finished loading
            $this->waitUntilSelectLoadingIsMissing($component, self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT);
            // show disabled checkbox
            $component->click($this->getLabelSelectorLabelShowDisabledAccountsOrAccountTypes());
            $this->_disable_checkbox_checked = !$this->_disable_checkbox_checked;
        });
    }

    public function selectAccountOrAccountTypeValue(Browser $browser, ?int $selector_value): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $selector_value = $selector_value ?? '';
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) use ($selector_value) {
            // make sure accounts have finished loading
            $this->waitUntilSelectLoadingIsMissing($component, self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT);
            // choose the account/account-type value
            $component->select(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $selector_value);

            if (!$this->_disable_checkbox_checked) {
                $this->assertSelectOptionsClassOfAccountOrAccountTypeAreNotDisabled($component);
            }
        });
    }

    public function assertSelectOptionValuesOfAccountOrAccountType(Browser $browser, array $accounts_or_account_types): void {
        $this->assertAccountOrAccountTypeTogglingSelectorIdLabelBeenSet();
        $browser->within($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_id_label), function(Browser $component) use ($accounts_or_account_types) {
            if (!$this->_disable_checkbox_checked) {
                $this->assertSelectOptionsClassOfAccountOrAccountTypeAreNotDisabled($component);
                $option_values = collect($accounts_or_account_types)->where('active', true)->pluck('id')->toArray();
            } else {
                $option_values = collect($accounts_or_account_types)->pluck('id')->toArray();
            }

            $component->assertSelectHasOptions(
                self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT,
                $option_values
            );
        });
    }

    private function assertSelectOptionsClassOfAccountOrAccountTypeAreNotDisabled(Browser $component): void {
        $options = $component->elements(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT.' option');
        foreach ($options as $option) {
            $option_class = $option->getAttribute('class');
            if (is_string($option_class)) {
                Assert::assertStringNotContainsString('disabled-option', $option_class);
            } else {
                Assert::assertNull($option_class);
            }
        }
    }

}
