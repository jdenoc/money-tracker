<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait AccountOrAccountTypeTogglingSelector {

    use DuskTraitToggleButton;
    use WaitTimes;

    private static $SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT = ".select-account-or-account-types-id";
    private static $SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING = ".is-loading .select-account-or-account-types-id";

    private static $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNT = "Account";
    private static $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_ACCOUNTTYPE = "Account Type";
    private static $LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_DEFAULT = "Account";
    private static $LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT = "[ ALL ]";

    private $_account_or_account_type_toggling_selector_label_id;
    private $_disable_checkbox_checked = false;

    /**
     * @param string $id_label
     * @return string
     */
    protected function getAccountOrAccountTypeTogglingSelectorComponentId(string $id_label):string{
        $selector_pattern_component_account_or_account_type_toggling_selector = "#account-or-account-type-toggling-selector-for-%s";
        return sprintf($selector_pattern_component_account_or_account_type_toggling_selector, $id_label);
    }

    /**
     * @param string $id_label
     * @return string
     */
    protected function getSwitchAccountAndAccountTypeId(string $id_label):string{
        $selector_pattern_field_toggle_switch_account_and_account_type = "#toggle-account-and-account-types-for-%s";
        return sprintf($selector_pattern_field_toggle_switch_account_and_account_type, $id_label);
    }

    /**
     * @param string $id_label
     * @return string
     */
    protected function getCheckboxShowDisabledAccountOrAccountType(string $id_label):string{
        $selector_pattern_field_checkbox_show_disabled = "#show-disabled-accounts-or-account-types-%s-checkbox+label";
        return sprintf($selector_pattern_field_checkbox_show_disabled, $id_label);
    }

    /**
     * @param Browser $browser
     * @param array $accounts
     * @return void
     */
    public function assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent(Browser $browser, array $accounts){
        $browser
            // component
            ->assertVisible($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id))
            ->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component) use ($accounts){
                $color_switch_default = $this->bulmaColors->getColor('COLOR_GREY_LIGHT');

                // account/account-type - switch
                $this->assertToggleButtonState(
                    $component,
                    $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_label_id),
                    self::$LABEL_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_TOGGLE_DEFAULT,
                    $color_switch_default
                );

                // account/account-type - select
                $component
                    ->assertVisible(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT)
                    ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, self::$LABEL_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_OPTION_DEFAULT)
                    ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                    ->assertSelectHasOption(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSelectHasOptions(
                        self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT,
                        collect($accounts)->where('disabled', false)->pluck('id')->toArray()
                    );

                // disable checkbox
                if(collect($accounts)->where('disabled', true)->count() > 0){
                    $component->assertVisible($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_label_id));
                } else {
                    $component->assertMissing($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_label_id));
                }
            });
    }

    public function assertShowDisabledAccountOrAccountTypeCheckboxIsVisible(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component){
            $component->assertVisible($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_label_id));
        });
    }

    public function assertShowDisabledAccountOrAccountTypeCheckboxIsNotVisible(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component){
            $component->assertMissing($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_label_id));
        });
    }

    /**
     * @param Browser $browser
     */
    public function toggleAccountOrAccountTypeSwitch(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component){
            // account/account-type - switch
            $this->toggleToggleButton($component, $this->getSwitchAccountAndAccountTypeId($this->_account_or_account_type_toggling_selector_label_id));
        });
    }

    /**
     * @param Browser $browser
     */
    public function toggleShowDisabledAccountOrAccountTypeCheckbox(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component){
            $component
                // make sure accounts have finished loading
                ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                // show disabled checkbox
                ->click($this->getCheckboxShowDisabledAccountOrAccountType($this->_account_or_account_type_toggling_selector_label_id));
            $this->_disable_checkbox_checked = !$this->_disable_checkbox_checked;
        });
    }

    /**
     * @param Browser $browser
     * @param string|int $selector_value
     */
    public function selectAccountOrAccountTypeValue(Browser $browser, $selector_value){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component) use ($selector_value){
            $component
                // make sure accounts have finished loading
                ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                // choose the account/account-type value
                ->select(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $selector_value);

            if(!$this->_disable_checkbox_checked){
                $option_class = $component->attribute(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT.' option', 'class');
                Assert::assertStringNotContainsString('disabled-option', $option_class);
            }
        });
    }

    /**
     * @param Browser $browser
     * @param array $accounts_or_account_types
     */
    public function assertSelectOptionValuesOfAccountOrAccountType(Browser $browser, array $accounts_or_account_types){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_account_or_account_type_toggling_selector_label_id), function(Browser $component) use ($accounts_or_account_types){
            $option_class = $component->attribute(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT.' option', 'class');
            if(!$this->_disable_checkbox_checked){
                Assert::assertStringNotContainsString('disabled-option', $option_class);
                $option_values = collect($accounts_or_account_types)->where('disabled', false)->pluck('id')->toArray();
            } else {
                $option_values = collect($accounts_or_account_types)->pluck('id')->toArray();
            }

            $component->assertSelectHasOptions(
                self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT,
                $option_values
            );
        });
    }

}
