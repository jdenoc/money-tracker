<?php
/**
 * Created by
 * User: denis.oconnor
 * Date: 2019-01-14
 */

namespace Tests\Traits;

trait HomePageSelectors {

    // ###*** SELECTORS ***###

    // generic - modal
    private $_selector_modal_head = ".modal-card-head";
    private $_selector_modal_body = ".modal-card-body";
    private $_selector_modal_foot = ".modal-card-foot";
    private $_selector_modal_btn_close = "button.delete";
    private $_selector_modal_tag_autocomplete_options = ".typeahead span";

    // generic - modal dropzone
    private $_selector_modal_dropzone_upload_thumbnail = ".dz-complete:last-child";
    private $_selector_modal_dropzone_progress = ".dz-progress";
    private $_selector_modal_dropzone_error_mark = ".dz-error-mark";
    private $_selector_modal_dropzone_error_message = ".dz-error-message";
    private $_selector_modal_dropzone_btn_remove = ".dz-remove";

    // entry-modal
    private $_selector_modal_entry = "@entry-modal";
    private $_selector_modal_entry_btn_confirmed = "#entry-confirm";
    private $_selector_modal_entry_btn_confirmed_label = "#entry-confirm + label";
    private $_selector_modal_entry_field_entry_id = "#entry-id";
    private $_selector_modal_entry_field_date="input#entry-date";
    private $_selector_modal_entry_field_value = "input#entry-value";
    private $_selector_modal_entry_field_account_type = "select#entry-account-type";
    private $_selector_modal_entry_field_account_type_is_loading = ".select.is-loading select#entry-account-type";
    private $_selector_modal_entry_field_memo = "textarea#entry-memo";
    private $_selector_modal_entry_field_expense = "#entry-expense";
    private $_selector_modal_entry_field_tags_container_is_loading = ".field:nth-child(6) .control.is-loading";
    private $_selector_modal_entry_field_tags = ".tags-input input";
    private $_selector_modal_entry_field_tags_input_tag = ".tags-input span.badge-pill";
    private $_selector_tags = ".tags";
    private $_selector_tags_tag = ".tags .tag";
    private $_selector_modal_entry_field_upload = "#entry-modal-file-upload";
    private $_selector_modal_entry_dropzone_hidden_file_input = "#entry-modal-hidden-file-input";
    private $_selector_modal_entry_dropzone_upload_thumbnail = "#entry-modal-file-upload .dz-complete:last-child";
    private $_selector_modal_entry_existing_attachments = "#existing-entry-attachments";
    private $_selector_modal_entry_existing_attachments_btn_view = "button.view-attachment";
    private $_selector_modal_entry_existing_attachments_btn_delete = "button.delete-attachment";
    private $_selector_modal_entry_btn_delete = "button#entry-delete-btn";
    private $_selector_modal_entry_btn_lock = "button#entry-lock-btn";
    private $_selector_modal_entry_btn_lock_icon = "#entry-lock-btn i";
    private $_selector_modal_entry_btn_cancel = "button#entry-cancel-btn";
    private $_selector_modal_entry_btn_save = "button#entry-save-btn";

    // transfer-modal
    private $_selector_modal_transfer = "@transfer-modal";
    private $_selector_modal_transfer_field_date = "#transfer-date";
    private $_selector_modal_transfer_field_value = "#transfer-value";
    private $_selector_modal_transfer_field_from = "select#from-account-type";
    private $_selector_modal_transfer_field_from_is_loading = ".select.is-loading select#from-account-type";
    private $_selector_modal_transfer_meta_account_name_from = "#from-account-type-meta-account-name";
    private $_selector_modal_transfer_meta_last_digits_from = "#from-account-type-meta-last-digits";
    private $_selector_modal_transfer_field_to = "select#to-account-type";
    private $_selector_modal_transfer_field_to_is_loading = ".select.is-loading select#to-account-type";
    private $_selector_modal_transfer_meta_account_name_to = "#to-account-type-meta-account-name";
    private $_selector_modal_transfer_meta_last_digits_to = "#to-account-type-meta-last-digits";
    private $_selector_modal_transfer_field_memo = "#transfer-memo";
    private $_selector_modal_transfer_field_tags_container_is_loading = ".field:nth-child(6) .control.is-loading";
    private $_selector_modal_transfer_field_tags = ".tags-input input";
    private $_selector_modal_transfer_field_upload = "#transfer-modal-file-upload";
    private $_selector_modal_transfer_dropzone_hidden_file_input = "#transfer-modal-hidden-file-input";
    private $_selector_modal_transfer_dropzone_upload_thumbnail = "#transfer-modal-file-upload .dz-complete:last-child";
    private $_selector_modal_transfer_btn_cancel = "#transfer-cancel-btn";
    private $_selector_modal_transfer_btn_save = "#transfer-save-btn";

    // entries-table
    private $_selector_table = "#entry-table";
    private $_selector_table_unconfirmed_expense = "tr.has-background-warning.is-expense";
    private $_selector_table_unconfirmed_income = 'tr.has-background-warning.is-income';
    private $_selector_table_confirmed_expense = 'tr.is-confirmed.is-expense';
    private $_selector_table_confirmed_income = 'tr.has-background-success.is-confirmed.is-income';
    private $_selector_table_row_transfer_checkbox = "td:nth-last-child(2)";
    private $_selector_table_row_attachment_checkbox = "td:nth-last-child(3)";
    private $_selector_table_is_checked_checkbox = ".fas.fa-check-square";
    private $_selector_table_unchecked_checkbox = "far fa-square";


    // ###*** LABELS ***###
    private $_label_entry_new = "Entry: new";
    private $_label_entry_not_new = "Entry: ";
    private $_label_btn_confirmed = "Confirmed";
    private $_label_account_type_meta_account_name = "Account Name:";
    private $_label_account_type_meta_last_digits = "Last 4 Digits:";
    private $_label_expense_switch_expense = "Expense";
    private $_label_expense_switch_income = "Income";
    private $_label_file_upload = "Drag & Drop";
    private $_label_btn_dropzone_remove_file = "REMOVE FILE";
    private $_label_btn_cancel = "Cancel";
    private $_label_btn_delete = "Delete";
    private $_label_btn_save = "Save changes";
    private $_label_notification_file_upload_success = "uploaded: %s";
    private $_label_notification_transfer_saved = "Transfer entry created";
    private $_label_notification_new_entry_created = "New entry created";

}