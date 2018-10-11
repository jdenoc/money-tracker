<?php

namespace Tests\Browser;

use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class EntryModalNewEntryTest extends DuskTestCase {

    private $_selector_modal = "@entry-modal";

    private $_selector_modal_head = "#entry-modal .modal-card-head";
    private $_selector_modal_head_confirmed = "#entry-confirm";
    private $_selector_modal_head_confirmed_label = "#entry-confirm + label";
    private $_selector_modal_head_close_btn = "button.delete";

    private $_selector_modal_body = "#entry-modal .modal-card-body";
    private $_selector_modal_body_date = "input#entry-date";
    private $_selector_modal_body_value = "input#entry-value";
    private $_selector_modal_body_account_type = "select#entry-account-type";
    private $_selector_modal_body_account_type_is_loading = ".select.is-loading select#entry-account-type";
    private $_selector_modal_body_memo = "textarea#entry-memo";
    private $_selector_modal_body_expense = "#entry-expense";
    private $_selector_modal_body_tags_container_is_loading = ".field:nth-child(6) .control.is-loading";
    private $_selector_modal_body_tags = ".tags-input input";
    private $_selector_modal_body_tag_autocomplete_options = ".typeahead span";
    private $_selector_modal_body_file_upload = "#entry-modal-file-upload";

    private $_selector_dropzone_upload_thumbnail = "#entry-modal-file-upload .dz-complete:last-child";
    private $_selector_dropzone_hidden_file_input = "#dz-hidden-file-input";
    private $_selector_dropzone_progress = ".dz-progress";
    private $_selector_dropzone_error_mark = ".dz-error-mark";
    private $_selector_dropzone_error_message = ".dz-error-message";
    private $_selector_dropzone_remove_btn = ".dz-remove";

    private $_selector_modal_foot = "#entry-modal .modal-card-foot";
    private $_selector_modal_foot_delete_btn = "button#entry-delete-btn";
    private $_selector_modal_foot_lock_btn = "button#entry-lock-btn";
    private $_selector_modal_foot_cancel_btn = "button#entry-cancel-btn";
    private $_selector_modal_foot_save_btn = "button#entry-save-btn";

    private $_label_account_type_meta_account_name = "Account Name:";
    private $_label_account_type_meta_last_digits = "Last 4 Digits:";
    private $_label_btn_confirmed = "Confirmed";
    private $_label_switch_expense = "Expense";
    private $_label_switch_income = "Income";
    private $_label_btn_dropzone_remove_file = "REMOVE FILE";

    public function testEntryModalIsNotVisibleByDefault(){
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new HomePage())
                ->assertMissing($this->_selector_modal);
        });
    }

    public function testEntryModalIsVisibleWhenNavbarElementIsClicked(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->assertVisible($this->_selector_modal);
        });
    }

    public function testModalHeaderHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head
                        ->assertSee("Entry: new")
                        ->assertNotChecked($this->_selector_modal_head_confirmed)
                        ->assertSee($this->_label_btn_confirmed)
                        ->assertVisible($this->_selector_modal_head_close_btn);

                    $entry_confirm_class = $entry_modal_head->attribute($this->_selector_modal_head_confirmed_label, 'class');
                    $this->assertContains('has-text-grey-light', $entry_confirm_class);
                });
        });
    }

    public function testCloseEntryModalWithXInModalHead(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head->click($this->_selector_modal_head_close_btn);
                })
                ->assertMissing($this->_selector_modal);
        });
    }

    public function testConfirmedButtonActivatesWhenClicked(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head
                        ->assertSee($this->_label_btn_confirmed)
                        ->click($this->_selector_modal_head_confirmed_label)
                        ->assertChecked($this->_selector_modal_head_confirmed);

                    $classes = $entry_modal_head->attribute($this->_selector_modal_head_confirmed_label, "class");
                    $this->assertContains("has-text-white", $classes);
                    $this->assertNotContains("has-text-grey-light", $classes);
                });
        });
    }

    public function testModalBodyHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->assertSee('Date:')
                        ->assertVisible($this->_selector_modal_body_date)

                        ->assertSee('Value:')
                        ->assertVisible($this->_selector_modal_body_value)

                        ->assertSee('Account Type:')
                        ->assertVisible($this->_selector_modal_body_account_type)
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits)

                        ->assertSee('Memo:')
                        ->assertVisible($this->_selector_modal_body_memo)

                        ->assertVisible($this->_selector_modal_body_expense)
                        ->assertSee($this->_label_switch_expense)
                        ->assertDontSee($this->_label_switch_income)

                        ->assertSee('Tags:')
                        ->assertVisible($this->_selector_modal_body_tags)  // auto-complete tags-input field

                        ->assertVisible($this->_selector_modal_body_file_upload) // drag-n-drop file upload field
                        ->with($this->_selector_modal_body_file_upload, function($file_upload){
                            $file_upload->assertSee("Drag & Drop");
                        });

                    $this->assertEquals(
                        'date',
                        $entry_modal_body->attribute($this->_selector_modal_body_date, 'type'),
                        $this->_selector_modal_body_date.' is not type="date"'
                    );
                    $entry_date = $entry_modal_body->value($this->_selector_modal_body_date);
                    $this->assertNotEmpty($entry_date, $this->_selector_modal_body_date." is empty");
                    $this->assertEquals(date("Y-m-d"), $entry_date, $this->_selector_modal_body_date." value is not correct");

                    $this->assertEquals(
                        'text',
                        $entry_modal_body->attribute($this->_selector_modal_body_value, 'type'),
                        $this->_selector_modal_body_value.' is not type="text"'
                    );
                    $this->assertEmpty($entry_modal_body->value($this->_selector_modal_body_value), $this->_selector_modal_body_value." is not empty");

                    $this->assertEmpty($entry_modal_body->value($this->_selector_modal_body_account_type), $this->_selector_modal_body_account_type." is not empty");

                    $this->assertEmpty($entry_modal_body->value($this->_selector_modal_body_memo), $this->_selector_modal_body_memo." is not empty");
                });
        });
    }

    public function testModalFooterHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_foot, function($entry_modal_foot){
                    $entry_modal_foot
                        ->assertMissing($this->_selector_modal_foot_delete_btn)   // delete button
                        ->assertMissing($this->_selector_modal_foot_lock_btn)     // lock/unlock button
                        ->assertVisible($this->_selector_modal_foot_cancel_btn)   // cancel button
                        ->assertSee("Cancel")
                        ->assertVisible($this->_selector_modal_foot_save_btn)     // save button
                        ->assertSee("Save changes");

                    $this->assertEquals(
                        'true',
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, 'disabled'),
                        "Save button is NOT disabled by default"
                    );
                    $this->assertContains(
                        'is-success',
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, 'class'),
                        "Save button should have 'is-success' class"
                    );
                });
        });
    }

    public function testCloseEntryModalWithCancelButton(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_foot, function($entry_modal_foot){
                    $entry_modal_foot->click($this->_selector_modal_foot_cancel_btn);
                })
                ->assertMissing($this->_selector_modal);
        });
    }

    public function testEntryValueConvertsIntoDecimalOfTwoPlaces(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->type($this->_selector_modal_body_value, "F15sae.92fwfw")
                        ->click($this->_selector_modal_body_date);

                    $this->assertEquals(
                        "15.92",
                        $entry_modal_body->value($this->_selector_modal_body_value),
                        $this->_selector_modal_body_value." value not correct"
                    );
                });
        });
    }

    public function testSelectingAccountTypeDisplaysAccountTypeMetaData(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];
        $this->assertNotEmpty($account_type);

        $this->browse(function(Browser $browser) use ($account_type){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_body_account_type_is_loading)
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                        // TODO: currency icon in input#entry-value is "$"
                })
                ->waitUntilMissing($this->_selector_modal_body_account_type_is_loading, HomePage::WAIT_SECONDS)
                ->with($this->_selector_modal_body, function($entry_modal_body) use ($account_type){
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_body_account_type)
                        ->select($this->_selector_modal_body_account_type, $account_type['id'])
                        ->assertNotSelected($this->_selector_modal_body_account_type, "")
                        ->assertSelected($this->_selector_modal_body_account_type, $account_type['id'])
                        ->assertSee($account_type['name'])
                        ->assertSee($this->_label_account_type_meta_account_name)
                        ->assertSee($this->_label_account_type_meta_last_digits)
                        // TODO: currency icon in input#entry-value is updated
                        ->select($this->_selector_modal_body_account_type, "")
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                        // TODO: currency icon in input#entry-value is "$"
                });

        });
    }

    public function testClickingExpenseIncomeSwitch(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->assertSee($this->_label_switch_expense)
                        ->click($this->_selector_modal_body_expense)
                        ->assertSee($this->_label_switch_income)
                        ->assertDontSee($this->_label_switch_expense);
                });
        });
    }

    public function testFillFieldsToEnabledSaveButton(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $not_disabled_save_btn_message = "Save button is NOT disabled by default after %s filled in.";
            $disabled_save_btn_message = "Save button IS disabled by default after %s filled in.";
            $is_disabled = "true";
            $attribute_disabled = "disabled";

            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    // The date field should already be filled in. No need to fill it in again.
                    $entry_date = $entry_modal_body->value($this->_selector_modal_body_date);
                    $this->assertNotEmpty($entry_date, $this->_selector_modal_body_date." is empty");
                    $this->assertEquals(date("Y-m-d"), $entry_date, $this->_selector_modal_body_date." value is not correct");
                })
                ->with($this->_selector_modal_foot, function($entry_modal_foot) use ($not_disabled_save_btn_message, $is_disabled, $attribute_disabled){
                    $this->assertEquals(
                        $is_disabled,
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, $attribute_disabled),
                        sprintf($not_disabled_save_btn_message, $this->_selector_modal_body_date)
                    );
                })

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body->type($this->_selector_modal_body_value, "9.99");
                })
                ->with($this->_selector_modal_foot, function($entry_modal_foot) use ($not_disabled_save_btn_message, $is_disabled, $attribute_disabled){
                    $this->assertEquals(
                        $is_disabled,
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, $attribute_disabled),
                        sprintf($not_disabled_save_btn_message, $this->_selector_modal_body_value)
                    );
                })

                ->waitUntilMissing($this->_selector_modal_body_account_type_is_loading, HomePage::WAIT_SECONDS)
                ->with($this->_selector_modal_body, function($entry_modal_body) use ($account_type){
                    $entry_modal_body
                        ->select($this->_selector_modal_body_account_type, $account_type['id'])
                        ->assertSee($this->_label_account_type_meta_account_name)
                        ->assertSee($this->_label_account_type_meta_last_digits);
                })
                ->with($this->_selector_modal_foot, function($entry_modal_foot) use ($not_disabled_save_btn_message, $is_disabled, $attribute_disabled){
                    $this->assertEquals(
                        $is_disabled,
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, $attribute_disabled),
                        sprintf($not_disabled_save_btn_message, $this->_selector_modal_body_account_type)
                    );
                })

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->type($this->_selector_modal_body_memo, "Test entry")
                        ->click($this->_selector_modal_body_date);
                })
                ->with($this->_selector_modal_foot, function($entry_modal_foot) use ($disabled_save_btn_message, $is_disabled, $attribute_disabled){
                    $this->assertNotEquals(
                        $is_disabled,
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, $attribute_disabled),
                        sprintf($disabled_save_btn_message, $this->_selector_modal_body_memo)
                    );
                })

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    // laravel dusk has an issue typing into input[type="date"] fields
                    // work-around for this is to use individual key-strokes
                    $backspace_count = strlen($entry_modal_body->value($this->_selector_modal_body_date));
                    for($i=0; $i<$backspace_count; $i++){
                        $entry_modal_body->keys($this->_selector_modal_body_date, "{backspace}");
                    }
                })
                ->with($this->_selector_modal_foot, function($entry_modal_foot) use ($not_disabled_save_btn_message, $is_disabled, $attribute_disabled){
                    $this->assertEquals(
                        $is_disabled,
                        $entry_modal_foot->attribute($this->_selector_modal_foot_save_btn, $attribute_disabled),
                        sprintf($not_disabled_save_btn_message, $this->_selector_modal_body_date)
                    );
                });
        });
    }

    public function testUploadAttachmentToNewEntry(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $upload_file_path = storage_path(parent::TEST_STORAGE_FILE_PATH);

                    $this->assertFileExists($upload_file_path);
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_body_file_upload)
                        ->attach($this->_selector_dropzone_hidden_file_input, $upload_file_path)
                        ->waitFor($this->_selector_dropzone_upload_thumbnail)
                        ->with($this->_selector_dropzone_upload_thumbnail, function($upload_thumbnail) use ($upload_file_path){
                            $upload_thumbnail
                                ->waitUntilMissing($this->_selector_dropzone_progress)
                                ->assertMissing($this->_selector_dropzone_error_mark)
                                ->mouseover("") // hover over current element
                                ->assertSee(basename($upload_file_path))
                                ->assertMissing($this->_selector_dropzone_error_message)
                                ->assertVisible($this->_selector_dropzone_remove_btn)
                                ->assertSee($this->_label_btn_dropzone_remove_file)
                                ->click($this->_selector_dropzone_remove_btn);
                        })
                        ->assertMissing($this->_selector_dropzone_upload_thumbnail);
                });
        });
    }

    public function testTagsInputAutoComplete(){
        // select tag at random and input the first character into the tags-input field
        $tags = $this->getApiTags();
        $tag = $tags[array_rand($tags, 1)]['name'];

        $this->browse(function(Browser $browser) use ($tag){
            $browser
                ->visit(new HomePage())
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function ($entry_modal_body) use ($tag){
                    $first_char = substr($tag, 0, 1);
                    $entry_modal_body
                        ->waitUntilMissing($this->_selector_modal_body_tags_container_is_loading, HomePage::WAIT_SECONDS)
                        ->keys($this->_selector_modal_body_tags, $first_char)
                        ->waitFor($this->_selector_modal_body_tag_autocomplete_options)
                        ->assertSee($tag);
                });
        });
    }

    public function testSaveEntry(){
        $this->markTestIncomplete("TODO: build");
    }

}