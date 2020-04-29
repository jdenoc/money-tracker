<?php

namespace App\Traits\Tests;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert;

trait AccountOrAccountTypeTogglingSelector {

    use AssertElementColor;

    private $_id_label;
    private $_disable_checkbox_checked = false;

    protected function getAccountOrAccountTypeTogglingSelectorComponentId($id_label){
        $selector_pattern_component_account_or_account_type_toggling_selector = "#account-or-account-type-toggling-selector-for-%s";
        return sprintf($selector_pattern_component_account_or_account_type_toggling_selector, $id_label);
    }

    protected function getSwitchAccountAndAccountTypeId($id_label){
        $selector_pattern_field_toggle_switch_account_and_account_type = "#toggle-account-and-account-types-for-%s";
        return sprintf($selector_pattern_field_toggle_switch_account_and_account_type, $id_label);
    }

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
                $selector_form_field_account_and_account_type_select = ".select-account-or-account-types-id";
                $label_select_option_default = "[ ALL ]";
                $class_switch_core = ".v-switch-core";
                $class_is_loading = '.is-loading';
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
                    ->assertVisible($selector_form_field_account_and_account_type_select)
                    ->assertSelected($selector_form_field_account_and_account_type_select, "")
                    ->assertSeeIn($selector_form_field_account_and_account_type_select, $label_select_option_default)
                    ->waitUntilMissing($class_is_loading.' '.$selector_form_field_account_and_account_type_select, 5)
                    ->assertSelectHasOption($selector_form_field_account_and_account_type_select, "")
                    ->assertSelectHasOptions(
                        $selector_form_field_account_and_account_type_select,
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

    public function toggleAccountOrAccountTypeSwitch(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component){
            // account/account-type - switch
            $component->click($this->getSwitchAccountAndAccountTypeId($this->_id_label));
            $this->_disable_checkbox_checked = !$this->_disable_checkbox_checked;
        });
    }

    public function toggleShowDisabledAccountOrAccountTypeCheckbox(Browser $browser){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component){
            // show disabled checkbox
            $component->click($this->getCheckboxShowDisabledAccountOrAccountType($this->_id_label));
        });
    }

    public function selectAccountOrAccountTypeValue(Browser $browser, $selector_value, $selector_option=''){
        $browser->with($this->getAccountOrAccountTypeTogglingSelectorComponentId($this->_id_label), function(Browser $component) use ($selector_value, $selector_option){
            $selector_form_field_account_and_account_type_select = ".select-account-or-account-types-id";
            $class_is_loading = '.is-loading';
            $component
                ->waitUntilMissing($class_is_loading.' '.$selector_form_field_account_and_account_type_select, 5)
                ->select($selector_form_field_account_and_account_type_select, $selector_value);

            $option_class = $component->attribute($selector_form_field_account_and_account_type_select.' option', 'class');
            if(!$this->_disable_checkbox_checked){
                Assert::assertNotContains('disabled-option', $option_class);
            }
        });
    }

}
