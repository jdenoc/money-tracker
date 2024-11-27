<?php

namespace App\Traits\Tests\Dusk;

use App\Models\Account;
use App\Models\AccountType;
use App\Traits\Tests\WithTailwindColors;
use InvalidArgumentException;
use Laravel\Dusk\Browser;

trait FilterModal {
    use AccountOrAccountTypeTogglingSelector;
    use BrowserDateUtil;
    use TagsInput;
    use WithTailwindColors;

    // selectors
    private static string $SELECTOR_MODAL_FILTER = "@filter-modal";  // see Browser\Pages\HomePage.php
    private static string $SELECTOR_MODAL_FILTER_FIELD_START_DATE = "#filter-start-date";
    private static string $SELECTOR_MODAL_FILTER_FIELD_END_DATE = "#filter-end-date";
    private static string $SELECTOR_MODAL_FILTER_FIELD_TAGS = "#filter-tags";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME = "#filter-is-income";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE = "#filter-is-expense";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT = "#filter-has-attachment";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT = "#filter-no-attachment";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER = "#filter-is-transfer";
    private static string $SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED = "#filter-unconfirmed";
    private static string $SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE = "#filter-min-value";
    private static string $SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE = "#filter-max-value";
    private static string $SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE_ICON = "#filter-min-value+span i";
    private static string $SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE_ICON = "#filter-max-value+span i";
    private static string $SELECTOR_MODAL_FILTER_BTN_CANCEL = "#filter-cancel-btn";
    private static string $SELECTOR_MODAL_FILTER_BTN_RESET = "#filter-reset-btn";
    private static string $SELECTOR_MODAL_FILTER_BTN_FILTER = "#filter-btn";

    // colors
    private static string $COLOR_FILTER_BTN_EXPORT = '';
    private static string $COLOR_FILTER_SWITCH_DEFAULT = "";
    private static string $COLOR_FILTER_SWITCH_ACTIVE = "";
    private static string $COLOR_FILTER_SWITCH_INACTIVE = "";

    protected function initFilterModalColors(): void {
        self::$COLOR_FILTER_BTN_EXPORT = $this->tailwindColors->blue(600);
        self::$COLOR_FILTER_SWITCH_ACTIVE = $this->tailwindColors->blue(600);
        self::$COLOR_FILTER_SWITCH_INACTIVE = $this->tailwindColors->gray(400);
        self::$COLOR_FILTER_SWITCH_DEFAULT = self::$COLOR_FILTER_SWITCH_INACTIVE;
    }

    protected function initFilterModalTogglingSelectorLabelId(): void {
        $this->_account_or_account_type_toggling_selector_id_label = 'filter-modal';
    }

    protected static function filterModalInputs(): array {
        return [
            "Start Date" => [self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE],                         // test 1/25
            "End Date" => [self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE],                             // test 2/25
            "Account & AccountType" => [self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT],       // test 3/25
            "Tags" => [self::$SELECTOR_MODAL_FILTER_FIELD_TAGS],                                     // test 4/25
            "Income" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME],                          // test 5/25
            "Expense" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE],                        // test 6/25
            "Has Attachments" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT],         // test 7/25
            "No Attachments" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT],           // test 8/25
            "Transfer" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER],                      // test 9/25
            "Unconfirmed" => [self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED],                // test 10/25
            "Min Range" => [self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE],                           // test 11/25
            "Max Range" => [self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE],                           // test 12/25
        ];
    }

    protected function filterModalInputInteraction(Browser $modal, $filter_input_selector) {
        $filter_value = null;
        switch ($filter_input_selector) {
            case self::$SELECTOR_MODAL_FILTER_FIELD_START_DATE:
            case self::$SELECTOR_MODAL_FILTER_FIELD_END_DATE:
                $filter_value = ['actual' => fake()->dateTimeBetween('-15 months', '-1 month')->format("Y-m-d")];
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
                $filter_value = collect($filter_values)->where('active', true)->random();
                $this->selectAccountOrAccountTypeValue($modal, $filter_value['id']);

                if ($is_account) {
                    // account
                    $account = Account::with(AccountType::getTableName())->find($filter_value['id']);
                    $filter_value = $account->accountTypes()->withTrashed()->pluck('id')->toArray();
                } else {
                    // account-type
                    $filter_value = $filter_value['id'];
                }
                break;
            case self::$SELECTOR_MODAL_FILTER_FIELD_TAGS:
                $tags = $this->getApiTags();
                $filter_value = collect($tags)->random(3)->toArray();
                foreach ($filter_value as $tag) {
                    $this->fillTagsInputUsingAutocomplete($modal, $tag['name']);
                    $this->assertTagInInput($modal, $tag['name']);
                }
                break;
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_INCOME:
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_EXPENSE:
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_HAS_ATTACHMENT:
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_NO_ATTACHMENT:
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_TRANSFER:
            case self::$SELECTOR_MODAL_FILTER_FIELD_SWITCH_UNCONFIRMED:
                $this->toggleToggleButton($modal, $filter_input_selector);
                break;
            case self::$SELECTOR_MODAL_FILTER_FIELD_MIN_VALUE:
            case self::$SELECTOR_MODAL_FILTER_FIELD_MAX_VALUE:
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
