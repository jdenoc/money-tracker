<?php

namespace App\Traits\Tests\Dusk;

trait EntryModalSelectors {

    // selectors
    private $_selector_modal_entry = "#entry-modal";
    private $_selector_modal_entry_confirmed = "#entry-confirm";
    private $_selector_modal_entry_btn_transfer = "#entry-transfer-btn";
    private $_selector_modal_entry_field_entry_id = "#entry-id";
    private static string $SELECTOR_MODAL_ENTRY_FIELD_DATE = "input#entry-date";
    private static string $SELECTOR_MODAL_ENTRY_FIELD_VALUE = "input#entry-value";
    private static string $SELECTOR_MODAL_ENTRY_FIELD_ACCOUNT_TYPE = "select#entry-account-type";
    private $_selector_modal_entry_meta = "#entry-account-type-meta";
    private static string $SELECTOR_MODAL_ENTRY_FIELD_MEMO = "textarea#entry-memo";
    private static string $SELECTOR_MODAL_ENTRY_FIELD_EXPENSE = "#entry-expense";
    private $_selector_modal_entry_tags = "#entry-tags";
    private static string $SELECTOR_MODAL_ENTRY_TAGS_LOCKED = "#entry-tags-locked";
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
    private string $_label_btn_confirmed = "Confirmed";
    private string $_label_expense_switch_expense = "Expense";
    private string $_label_expense_switch_income = "Income";

}
