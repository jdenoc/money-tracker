<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait AccountOrAccountTypeTogglingSelector {

    use AssertElementColor;
    use WaitTimes;

    private static $SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT = ".select-account-or-account-types-id";
    private static $SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING = ".is-loading .select-account-or-account-types-id";

    private $_id_label;
    private $_disable_checkbox_checked = false;

    /**
     * @param string $id_label
     * @return string
     */
    protected function getAccountOrAccountTypeTogglingSelectorComponentId($id_label){
        $selector_pattern_component_account_or_account_type_toggling_selector = "#account-or-account-type-toggling-selector-for-%s";
        return sprintf($selector_pattern_component_account_or_account_type_toggling_selector, $id_label);
    }

    /**
     * @param string $id_label
     * @return string
     */
    protected function getSwitchAccountAndAccountTypeId($id_label){
        $selector_pattern_field_toggle_switch_account_and_account_type = "#toggle-account-and-account-types-for-%s";
        return sprintf($selector_pattern_field_toggle_switch_account_and_account_type, $id_label);
    }

    /**
     * @param string $id_label
     * @return string
     */
    protected function getCheckboxShowDisabledAccountOrAccountType($id_label){
        $selector_pattern_field_checkbox_show_disabled = "#show-disabled-accounts-or-account-types-%s-checkbox+label";
        return sprintf($selector_pattern_field_checkbox_show_disabled, $id_label);
    }

    /**
     * @param Browser $browser
     * @param array $accounts
     * @return void
     */
    public function assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent(Browser $browser, $accounts){
        $browser
            // component
            ->assertVisible($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label))
            ->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component) use ($accounts){
                $label_select_option_default = "[ ALL ]";
                $class_switch_core = ".v-switch-core";
                $color_switch_default = "#B5B5B5";

                // account/account-type - switch
                $component
                    ->assertVisible($this->getSwitchAccountAndAccountTypeId($this->_id_label))
                    ->assertSeeIn($this->getSwitchAccountAndAccountTypeId($this->_id_label), "Account");
                $this->assertElementColour(
                    $component,
                    $this->getSwitchAccountAndAccountTypeId($this->_id_label).' '.$class_switch_core, $color_switch_default
                );

                // account/account-type - select
                $component
                    ->assertVisible(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT)
                    ->assertSelected(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSeeIn(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $label_select_option_default)
                    ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                    ->assertSelectHasOption(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, "")
                    ->assertSelectHasOptions(
                        self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT,
                        collect($accounts)->where('disabled', false)->pluck('id')->toArray()
                    );

                // disable checkbox
                if(collect($accounts)->where('disabled', true)->count() > 0){
                    $component->assertVisible($this->getCheckboxShowDisabledAccountOrAccountType($this->_id_label));
                } else {
                    $component->assertMissing($this->getCheckboxShowDisabledAccountOrAccountType($this->_id_label));
                }
            });
    }

    /**
     * @param Browser $browser
     */
    public function toggleAccountOrAccountTypeSwitch(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component){
            // account/account-type - switch
            $component->click($this->getSwitchAccountAndAccountTypeId($this->_id_label));
        });
    }

    /**
     * @param Browser $browser
     */
    public function toggleShowDisabledAccountOrAccountTypeCheckbox(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component){
            $component
                // make sure accounts have finished loading
                ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                // show disabled checkbox
                ->click($this->getCheckboxShowDisabledAccountOrAccountType($this->_id_label));
            $this->_disable_checkbox_checked = !$this->_disable_checkbox_checked;
        });
    }

    /**
     * @param Browser $browser
     * @param string|int $selector_value
     */
    public function selectAccountOrAccountTypeValue(Browser $browser, $selector_value){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component) use ($selector_value){
            $component
                // make sure accounts have finished loading
                ->waitUntilMissing(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT_LOADING, self::$WAIT_SECONDS)
                // choose the account/account-type value
                ->select(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT, $selector_value);

            $option_class = $component->attribute(self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT.' option', 'class');
            if(!$this->_disable_checkbox_checked){
                Assert::assertNotContains('disabled-option', $option_class);
            }
        });
    }

}
