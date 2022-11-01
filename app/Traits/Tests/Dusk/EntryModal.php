<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use Laravel\Dusk\Browser;

trait EntryModal {

    use AccountOrAccountTypeSelector;
    use AssertElementColor;
    use BrowserDateUtil;
    use EntryModalSelectors;
    use FileDragNDrop;
    use TagsInput;
    use ToggleButton;

    // colours
    private string $_color_expense_switch_expense = "";
    private string $_color_expense_switch_income = "";

    private function initEntryModalColours(){
        $this->_color_expense_switch_expense = $this->tailwindColors->yellow(400);
        $this->_color_expense_switch_income = $this->tailwindColors->teal(500);
    }

    protected function assertConfirmedButtonActive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->green(400));
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->white());
    }

    protected function assertConfirmedButtonInactive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertNotChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->white());
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->gray(400));
    }

    protected function interactWithConfirmButton(Browser $modal){
        // need to directly interact using JavaScript rather than dusk api as the dusk api doesn't allow us to access parent nodes of a selector
        $script = <<<JS
document.querySelector('$this->_selector_modal_entry_confirmed').parentNode.click();
JS;
        $modal->script($script);
    }

    protected function assertEntryValueCurrency(Browser $modal, string $currency_character){
        // check currency icon in input#entry-value
        $entry_value_currency = $modal->text($this->_selector_modal_entry_field_value." + span.currency-symbol");
        $this->assertStringContainsString($currency_character, $entry_value_currency);
    }

    // TODO: open empty/new entry
    //     May already exist

    // TODO: open an existing entry
    //      May already exist

    // TODO: close modal - footer "Cancel"
    // TODO: close modal - header (X)

    // TODO: save modal

    // TODO: delete modal

    protected function lockEntryModal(Browser $modal){
        $modal
            ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.unlock-icon')
            ->click($this->_selector_modal_entry_btn_lock)
            ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.lock-icon');
    }

    protected function unlockEntryModal(Browser $modal){
        $modal
            ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.lock-icon')
            ->click($this->_selector_modal_entry_btn_lock)
            ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.unlock-icon');
    }

    // TODO: click transfer entry button

    protected function setEntryModalDate(Browser $modal, string $date){
        $browser_date = $this->getDateFromLocale($this->getBrowserLocale($modal), $date);
        $new_value_to_type = $this->processLocaleDateForTyping($browser_date);
        $modal->type($this->_selector_modal_entry_field_date, $new_value_to_type);
    }

    protected function assertEntryModalDate(Browser $modal, string $date){
        $modal->assertInputValue($this->_selector_modal_entry_field_date, $date);
    }

    protected function setEntryModalValue(Browser $modal, string $value){
        $modal->type($this->_selector_modal_entry_field_value, $value);
    }

    protected function assertEntryModalValue(Browser $modal, string $value){
        $modal->assertInputValue($this->_selector_modal_entry_field_value, $value);
    }

    protected function setEntryModalAccountType(Browser $modal, int $accountTypeId){
        $modal->select($this->_selector_modal_entry_field_account_type, $accountTypeId);
    }

    protected function assertEntryModalAccountType(Browser $modal, int $accountTypeId){
        $modal
            ->assertSelected($this->_selector_modal_entry_field_account_type, $accountTypeId)
            ->assertSee($this->_label_account_type_meta_account_name)
            ->assertSee($this->_label_account_type_meta_last_digits);
    }

    protected function setEntryModalMemo(Browser $modal, string $memo){
        $modal->type($this->_selector_modal_entry_field_memo, $memo);
    }

    protected function assertEntryModalMemo(Browser $modal, string $memo){
        $modal->assertInputValue($this->_selector_modal_entry_field_memo, $memo);
    }

    protected function toggleEntryModalExpense(Browser $modal){
        $this->toggleToggleButton($modal, $this->_selector_modal_entry_field_expense);
    }

    protected function assertEntryModalExpenseState(Browser $modal, bool $isExpense){
        $data_expense_switch_label = $isExpense ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;
        $expense_switch_color = $isExpense ? $this->_color_expense_switch_expense : $this->_color_expense_switch_income;
        $this->assertToggleButtonState($modal, $this->_selector_modal_entry_field_expense, $data_expense_switch_label, $expense_switch_color);
    }

    protected function assertTagInEntryModalLockedTags(Browser $modal, string $tag){
        $modal
            ->assertVisible($this->_selector_modal_entry_tags_locked.$this->_selector_tags_tag)
            ->assertSeeIn($this->_selector_modal_entry_tags_locked, $tag);
    }

    public function assertCountOfLockedTagsInEntryModal(Browser $modal, int $expectedTagCount){
        $tags = $modal->elements(self::$SELECTOR_TAGS_INPUT_CONTAINER.' '.self::$SELECTOR_TAGS_INPUT_TAG);
        $this->assertCount($expectedTagCount, $tags);
    }

}
