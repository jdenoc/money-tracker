<?php

namespace Tests\Traits;

trait HomePageSelectors {

    // ###*** SELECTORS ***###
    // generic - modal
    private $_selector_modal_head = ".modal-card-head";
    private $_selector_modal_title = ".modal-card-title";
    private $_selector_modal_body = ".modal-card-body";
    private $_selector_modal_foot = ".modal-card-foot";
    private $_selector_modal_btn_close = "button.delete";
    private $_selector_modal_tag_autocomplete_options = ".typeahead span";

    // institutions panel
    private $_selector_panel_institutions = "#institutions-panel-column";
    private $_selector_panel_institutions_heading = ".panel-heading";
    private $_selector_panel_institutions_overview = "#overview";
    private $_selector_panel_institutions_institution = ".institution-panel-institution";
    private $_selector_panel_institutions_institution_open_close = ".institution-panel-institution-name span.panel-icon i";
    private $_selector_panel_institutions_institution_name = ".institution-panel-institution-name span.name-label";
    private $_selector_panel_institutions_accounts = ".institution-panel-institution-accounts";
    private $_selector_panel_institutions_accounts_account = ".institutions-panel-account";
    private $_selector_panel_institutions_accounts_account_name = ".institutions-panel-account-name";
    private $_selector_panel_institutions_accounts_account_total = ".institutions-panel-account-name .account-currency span";

    // entry-modal
    private $_selector_modal_entry = "@entry-modal";  // see Browser\Pages\HomePage.php
    private $_selector_modal_entry_btn_confirmed = "#entry-confirm";
    private $_selector_modal_entry_btn_confirmed_label = "#entry-confirm + label";
    private $_selector_modal_entry_btn_transfer = "#entry-transfer-btn";
    private $_selector_modal_entry_field_entry_id = "#entry-id";
    private $_selector_modal_entry_field_date="input#entry-date";
    private $_selector_modal_entry_field_value = "input#entry-value";
    private $_selector_modal_entry_field_account_type = "select#entry-account-type";
    private $_selector_modal_entry_field_account_type_is_loading = ".select.is-loading select#entry-account-type";
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
    private $_selector_modal_entry_btn_lock_icon = "#entry-lock-btn i";
    private $_selector_modal_entry_btn_cancel = "button#entry-cancel-btn";
    private $_selector_modal_entry_btn_save = "button#entry-save-btn";

    // transfer-modal
    private $_selector_modal_transfer = "@transfer-modal";  // see Browser\Pages\HomePage.php
    private $_selector_modal_transfer_field_date = "input#transfer-date";
    private $_selector_modal_transfer_field_value = "input#transfer-value";
    private $_selector_modal_transfer_field_from = "select#from-account-type";
    private $_selector_modal_transfer_field_from_is_loading = ".select.is-loading select#from-account-type";
    private $_selector_modal_transfer_meta_from = "#transfer-from-account-type-meta";
    private $_selector_modal_transfer_meta_account_name_from = "#from-account-type-meta-account-name";
    private $_selector_modal_transfer_meta_last_digits_from = "#from-account-type-meta-last-digits";
    private $_selector_modal_transfer_field_to = "select#to-account-type";
    private $_selector_modal_transfer_field_to_is_loading = ".select.is-loading select#to-account-type";
    private $_selector_modal_transfer_meta_to = "#transfer-to-account-type-meta";
    private $_selector_modal_transfer_meta_account_name_to = "#to-account-type-meta-account-name";
    private $_selector_modal_transfer_meta_last_digits_to = "#to-account-type-meta-last-digits";
    private $_selector_modal_transfer_field_memo = "#transfer-memo";
    private $_selector_modal_transfer_field_upload = "#transfer-modal-file-upload";
    private $_selector_modal_transfer_dropzone_hidden_file_input = "#transfer-modal-hidden-file-input";
    private $_selector_modal_transfer_dropzone_upload_thumbnail = "#transfer-modal-file-upload .dz-complete:last-child";
    private $_selector_modal_transfer_btn_cancel = "#transfer-cancel-btn";
    private $_selector_modal_transfer_btn_save = "#transfer-save-btn";

    // entries-table
    private $_selector_table = "#entry-table";
    private $_selector_table_head = 'thead';
    private $_selector_table_body = 'tbody';
    private $_selector_table_unconfirmed_expense = "tr.has-background-warning.is-expense";
    private $_selector_table_unconfirmed_income = 'tr.has-background-warning.is-income';
    private $_selector_table_confirmed_expense = 'tr.is-confirmed.is-expense';
    private $_selector_table_confirmed_income = 'tr.has-background-success.is-confirmed.is-income';
    private $_selector_table_row_date = 'td.row-entry-date';
    private $_selector_table_row_memo = 'td.row-entry-memo';
    private $_selector_table_row_value = 'td.row-entry-value';
    private $_selector_table_row_account_type = 'td.row-entry-account-type';
    private $_selector_table_row_transfer_checkbox = "td.row-entry-transfer-checkbox";
    private $_selector_table_row_attachment_checkbox = "td.row-entry-attachment-checkbox";
    private $_selector_table_row_tags = "td.row-entry-tags";
    private $_selector_table_is_checked_checkbox = ".fas.fa-check-square";
    private $_selector_table_unchecked_checkbox = "far fa-square";
    private $_selector_pagination_btn_next = "button#paginate-btn-next";
    private $_selector_pagination_btn_prev = "button#paginate-btn-prev";
    private static $PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW = '#entry-%s';

    // ###*** LABELS ***###
    private $_label_entry_new = "Entry: new";
    private $_label_entry_not_new = "Entry: ";
    private $_label_btn_confirmed = "Confirmed";
    private $_label_account_type_meta_account_name = "Account Name:";
    private $_label_account_type_meta_last_digits = "Last 4 Digits:";
    private $_label_expense_switch_expense = "Expense";
    private $_label_expense_switch_income = "Income";
    private $_label_checkbox_show_disabled = "Show Disabled";
    private $_label_switch_enabled = "Enabled";
    private $_label_switch_disabled = "Disabled";
    private $_label_btn_cancel = "Cancel";
    private $_label_btn_delete = "Delete";
    private $_label_btn_save = "Save changes";
    private $_label_btn_reset = "Reset";
    private $_label_btn_filter = "Filter";
    private $_label_notification_transfer_saved = "Transfer entry created";
    private $_label_notification_new_entry_created = "New entry created";

    // ###*** classes ***###
    private $_class_is_income = "is-income";
    private $_class_is_expense = "is-expense";
    private $_class_is_active = "is-active";
    private $_class_switch_core = ".v-switch-core";
    private $_class_icon_euro = "fa-euro-sign";
    private $_class_icon_dollar = "fa-dollar-sign";
    private $_class_icon_pound = "fa-pound-sign";

    // ###*** COLOURS ***###
    private $_color_expense_switch_expense = "";
    private $_color_expense_switch_income = "";

}