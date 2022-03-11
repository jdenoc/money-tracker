<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use Laravel\Dusk\Browser;

trait EntryModal {

    use AccountOrAccountTypeSelector;
    use AssertElementColor;
    use FileDragNDrop;
    use TagsInput;
    use TailwindColors;
    use ToggleButton;

    // selectors
    private $_selector_modal_entry = "#entry-modal";
    private $_selector_modal_entry_confirmed = "#entry-confirm";
    private $_selector_modal_entry_btn_transfer = "#entry-transfer-btn";
    private $_selector_modal_entry_field_entry_id = "#entry-id";
    private $_selector_modal_entry_field_date="input#entry-date";
    private $_selector_modal_entry_field_value = "input#entry-value";
    private $_selector_modal_entry_field_account_type = "select#entry-account-type";
    private $_selector_modal_entry_meta = "#entry-account-type-meta";
    private $_selector_modal_entry_field_memo = "textarea#entry-memo";
    private $_selector_modal_entry_field_expense = "#entry-expense";
    private $_selector_tags = ".tags";
    private $_selector_tags_tag = ".tags .tag";
    private $_selector_modal_entry_field_upload = "#entry-modal-file-upload";
    private $_selector_modal_entry_dropzone_hidden_file_input = "#entry-modal-hidden-file-input";
    private $_selector_modal_entry_existing_attachments = "#existing-entry-attachments";
    private $_selector_modal_entry_existing_attachments_first_attachment = ".existing-attachment:first-child";
    private $_selector_modal_entry_existing_attachments_attachment_name = " .attachment-name";
    private $_selector_modal_entry_existing_attachments_attachment_btn_view = "button.view-attachment";
    private $_selector_modal_entry_existing_attachments_attachment_btn_delete = "button.delete-attachment";
    private $_selector_modal_entry_btn_delete = "button#entry-delete-btn";
    private $_selector_modal_entry_btn_lock = "button#entry-lock-btn";
    private $_selector_modal_entry_btn_cancel = "button#entry-cancel-btn";
    private $_selector_modal_entry_btn_save = "button#entry-save-btn";

    // labels
    private $_label_btn_confirmed = "Confirmed";
    private $_label_expense_switch_expense = "Expense";
    private $_label_expense_switch_income = "Income";

    // colours
    private $_color_expense_switch_expense = "";
    private $_color_expense_switch_income = "";

    private function initEntryModalColours(){
        $this->_color_expense_switch_expense = self::amber(300);
        $this->_color_expense_switch_income = self::teal(500);
    }

    protected function assertConfirmedButtonActive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, self::green(400));
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, self::white());
    }

    protected function assertConfirmedButtonInactive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertNotChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, self::white());
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, self::gray(400));
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

    // TODO: lock modal

    // TODO: unlock modal

    // TODO: fill in fields

    // TODO: click transfer entry button

}
