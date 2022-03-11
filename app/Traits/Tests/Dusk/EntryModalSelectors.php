<?php

namespace App\Traits\Tests\Dusk;

trait EntryModalSelectors {

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

}
