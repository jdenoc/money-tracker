<?php

namespace App\Traits\Tests\Dusk;

use App\Models\Account;
use App\Traits\Tests\WithTailwindColors;
use InvalidArgumentException;
use Laravel\Dusk\Browser;

trait FilterModal {
    use AccountOrAccountTypeTogglingSelector;
    use BrowserDateUtil;
    use TagsInput;
    use WithTailwindColors;

    // selectors
    private string $_selector_modal_filter = "@filter-modal";  // see Browser\Pages\HomePage.php
    private string $_selector_modal_filter_field_start_date = "#filter-start-date";
    private string $_selector_modal_filter_field_end_date = "#filter-end-date";
    private string $_selector_modal_filter_field_tags= "#filter-tags";
    private string $_selector_modal_filter_field_switch_income = "#filter-is-income";
    private string $_selector_modal_filter_field_switch_expense = "#filter-is-expense";
    private string $_selector_modal_filter_field_switch_has_attachment = "#filter-has-attachment";
    private string $_selector_modal_filter_field_switch_no_attachment = "#filter-no-attachment";
    private string $_selector_modal_filter_field_switch_transfer = "#filter-is-transfer";
    private string $_selector_modal_filter_field_switch_unconfirmed = "#filter-unconfirmed";
    private string $_selector_modal_filter_field_min_value = "#filter-min-value";
    private string $_selector_modal_filter_field_max_value = "#filter-max-value";
    private string $_selector_modal_filter_field_min_value_icon = "#filter-min-value+span i";
    private string $_selector_modal_filter_field_max_value_icon = "#filter-max-value+span i";
    private string $_selector_modal_filter_btn_cancel = "#filter-cancel-btn";
    private string $_selector_modal_filter_btn_reset = "#filter-reset-btn";
    private string $_selector_modal_filter_btn_filter = "#filter-btn";

    // colors
    private string $_color_filter_btn_export = '';
    private string $_color_filter_switch_default = "";
    private string $_color_filter_switch_active = "";
    private string $_color_filter_switch_inactive = "";

    protected function initFilterModalColors(): void {
        $this->_color_filter_btn_export = $this->tailwindColors->blue(600);
        $this->_color_filter_switch_active = $this->tailwindColors->blue(600);
        $this->_color_filter_switch_inactive = $this->tailwindColors->gray(400);
        $this->_color_filter_switch_default = $this->_color_filter_switch_inactive;
    }

    protected function initFilterModalTogglingSelectorLabelId(): void {
        $this->_account_or_account_type_toggling_selector_id_label = 'filter-modal';
    }

    protected function filterModalInputs(): array {
        return [
            "Start Date"=>[$this->_selector_modal_filter_field_start_date],                         // test 1/25
            "End Date"=>[$this->_selector_modal_filter_field_end_date],                             // test 2/25
            "Account & AccountType"=>[self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT],       // test 3/25
            "Tags"=>[$this->_selector_modal_filter_field_tags],                                     // test 4/25
            "Income"=>[$this->_selector_modal_filter_field_switch_income],                          // test 5/25
            "Expense"=>[$this->_selector_modal_filter_field_switch_expense],                        // test 6/25
            "Has Attachments"=>[$this->_selector_modal_filter_field_switch_has_attachment],         // test 7/25
            "No Attachments"=>[$this->_selector_modal_filter_field_switch_no_attachment],           // test 8/25
            "Transfer"=>[$this->_selector_modal_filter_field_switch_transfer],                      // test 9/25
            "Unconfirmed"=>[$this->_selector_modal_filter_field_switch_unconfirmed],                // test 10/25
            "Min Range"=>[$this->_selector_modal_filter_field_min_value],                           // test 11/25
            "Max Range"=>[$this->_selector_modal_filter_field_max_value],                           // test 12/25
        ];
    }

    protected function filterModalInputInteraction(Browser $modal, $filter_input_selector) {
        $filter_value = null;
        switch ($filter_input_selector) {
            case $this->_selector_modal_filter_field_start_date:
            case $this->_selector_modal_filter_field_end_date:
                $filter_value = ['actual'=>fake()->dateTimeBetween('-15 months', '-1 month')->format("Y-m-d")];
                $browser_date = $this->getDateFromLocale($this->getBrowserLocale($modal), $filter_value['actual']);
                $filter_value['typed'] = $this->processLocaleDateForTyping($browser_date);
                $modal->type($filter_input_selector, $filter_value['typed']);
                break;
            case self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT:
                $is_account = fake()->boolean();
                if ($is_account) {
                    // account
                    $filter_values = $this->getApiAccounts();
                } else {
                    // account-type
                    $this->toggleAccountOrAccountTypeSwitch($modal);
                    $filter_values = $this->getApiAccountTypes();
                }
                $filter_value = collect($filter_values)->where('disabled', false)->random();
                $this->selectAccountOrAccountTypeValue($modal, $filter_value['id']);

                if ($is_account) {
                    $account = Account::with('account_types')->find($filter_value['id']);
                    $filter_value = $account->account_types->pluck('name')->toArray();
                } else {
                    $filter_value = $filter_value['name'];
                }
                break;
            case $this->_selector_modal_filter_field_tags:
                $tags = $this->getApiTags();
                $filter_value = collect($tags)->random(3)->toArray();
                foreach ($filter_value as $tag) {
                    $this->fillTagsInputUsingAutocomplete($modal, $tag['name']);
                    $this->assertTagInInput($modal, $tag['name']);
                }
                break;
            case $this->_selector_modal_filter_field_switch_income:
            case $this->_selector_modal_filter_field_switch_expense:
            case $this->_selector_modal_filter_field_switch_has_attachment:
            case $this->_selector_modal_filter_field_switch_no_attachment:
            case $this->_selector_modal_filter_field_switch_transfer:
            case $this->_selector_modal_filter_field_switch_unconfirmed:
                $this->toggleToggleButton($modal, $filter_input_selector);
                break;
            case $this->_selector_modal_filter_field_min_value:
            case $this->_selector_modal_filter_field_max_value:
                $filter_value = fake()->randomFloat(2, 0, 100);
                // need to use type() here otherwise vuejs won't pick up the update
                $modal->type($filter_input_selector, $filter_value);
                break;
            default:
                throw new InvalidArgumentException("Unknown filter parameter provided:".$filter_input_selector);
        }
        return $filter_value;
    }

}
