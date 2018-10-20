<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class EntryModalExistingEntryTest extends DuskTestCase {

    // entry-table
    private $_selector_unconfirmed_expense = "tr.has-background-warning.is-expense";
    private $_selector_unconfirmed_income = 'tr.has-background-warning.is-income';
    private $_selector_confirmed_expense = 'tr.is-confirmed.is-expense';
    private $_selector_confirmed_income = 'tr.has-background-success.is-confirmed.is-income';

    // entry-modal
    private $_selector_entry_modal = "@entry-modal";
    private $_selector_modal_head = ".modal-card-head";
    private $_selector_modal_body = ".modal-card-body";
    private $_selector_modal_foot = ".modal-card-foot";
    private $_selector_field_entry_id = "#entry-id";
    private $_selector_btn_confirmed_label = "#entry-confirm + label";
    private $_selector_btn_confirmed = "#entry-confirm";
    private $_selector_field_date = "#entry-date";
    private $_selector_field_value = "#entry-value";
    private $_selector_field_account_type = "#entry-account-type";
    private $_selector_field_memo = "#entry-memo";
    private $_selector_field_expense = "#entry-expense";
    private $_selector_tags_input = ".tags-input";
    private $_selector_tags_input_tag = ".tags-input span.badge-pill";
    private $_selector_tags = ".tags";
    private $_selector_tags_tag = ".tags .tag";
    private $_selector_field_file_upload = "#entry-modal-file-upload";
    private $_selector_existing_attachments = "#existing-entry-attachments";
    private $_selector_existing_attachments_view_btn = "button.view-attachment";
    private $_selector_existing_attachments_delete_btn = "button.delete-attachment";
    private $_selector_btn_delete = "#entry-delete-btn";
    private $_selector_btn_lock = "#entry-lock-btn";
    private $_selector_btn_lock_icon = "#entry-lock-btn i";
    private $_selector_btn_cancel = "#entry-cancel-btn";
    private $_selector_btn_save = "#entry-save-btn";

    private $_class_lock = "fa-lock";
    private $_class_unlock = "fa-unlock-alt";
    private $_class_disabled = "disabled";
    private $_class_white_text = "has-text-white";
    private $_class_light_grey_text = "has-text-grey-light";
    private $_class_has_attachments = "has-attachments";
    private $_class_has_tags = "has-tags";
    private $_class_existing_attachment = "existing-attachment";

    private $_label_head_entry_new = "Entry: new";
    private $_label_head_entry_not_new = "Entry: ";
    private $_label_head_btn_confirmed = "Confirmed";
    private $_label_body_meta_account_name = "Account Name:";
    private $_label_body_meta_last_digits = "Last 4 Digits:";
    private $_label_body_expense = "Expense";
    private $_label_body_income = "Income";
    private $_label_body_file_upload = "Drag & Drop";
    private $_label_foot_btn_cancel = "Cancel";
    private $_label_foot_btn_delete = "Delete";
    private $_label_foot_btn_save = "Save changes";

    public function providerUnconfirmedEntry(){
        return [
            "Expense"=>[$this->_selector_unconfirmed_expense, $this->_label_body_expense],
            "Income"=>[$this->_selector_unconfirmed_income, $this->_label_body_income],
        ];
    }

    /**
     * @dataProvider providerUnconfirmedEntry
     * @param string $data_entry_selector
     * @param string $data_expense_switch_label
     *
     * @throws \Throwable
     */
    public function testClickingOnEntryTableEditButtonOfUnconfirmedEntry($data_entry_selector, $data_expense_switch_label){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_expense_switch_label){
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($data_entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use ($data_expense_switch_label){
                    $entry_id = $entry_modal->value($this->_selector_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_head_entry_new)
                                ->assertSee($this->_label_head_entry_not_new)
                                ->assertSee($this->_label_head_btn_confirmed);
                            $entry_confirm_class = $modal_head->attribute($this->_selector_btn_confirmed_label, 'class');
                            $this->assertEquals($this->_class_light_grey_text, $entry_confirm_class);
                        })

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data, $data_expense_switch_label){
                            $modal_body
                                ->assertInputValue($this->_selector_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_field_account_type, $entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_field_memo, $entry_data['memo'])
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertSee($data_expense_switch_label);
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_btn_delete)
                                ->assertSee($this->_label_foot_btn_delete)
                                ->assertMissing($this->_selector_btn_lock)
                                ->assertVisible($this->_selector_btn_cancel)
                                ->assertSee($this->_label_foot_btn_cancel)
                                ->assertVisible($this->_selector_btn_save)
                                ->assertSee($this->_label_foot_btn_save);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    public function providerConfirmedEntry(){
        return [
            "Expense"=>[$this->_selector_confirmed_expense, $this->_label_body_expense],
            "Income"=>[$this->_selector_confirmed_income, $this->_label_body_income],
        ];
    }

    /**
     * @dataProvider providerConfirmedEntry
     * @param string $data_entry_selector
     * @param string $data_expense_switch_label
     *
     * @throws \Throwable
     */
    public function testClickingOnEntryTableEditButtonOfConfirmedEntry($data_entry_selector, $data_expense_switch_label){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_expense_switch_label){
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($data_entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use ($data_expense_switch_label){
                    $entry_id = $entry_modal->value($this->_selector_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_head_entry_new)
                                ->assertSee($this->_label_head_entry_not_new)
                                ->assertSee($this->_label_head_btn_confirmed);

                            $classes = $modal_head->attribute($this->_selector_btn_confirmed_label, "class");
                            $this->assertContains($this->_class_white_text, $classes);
                            $this->assertNotContains($this->_class_light_grey_text, $classes);
                            $this->assertEquals("true", $modal_head->attribute($this->_selector_btn_confirmed, "disabled"));
                        })

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data, $data_expense_switch_label){
                            $modal_body
                                ->assertInputValue($this->_selector_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_field_account_type, $entry_data['account_type_id'])
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertInputValue($this->_selector_field_memo, $entry_data['memo'])
                                ->assertSee($data_expense_switch_label)
                                ->assertMissing($this->_selector_field_file_upload)
                                ->assertDontSee($this->_label_body_file_upload);

                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_date, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_value, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_account_type, "disabled"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_memo, "readonly"));

                            $classes = $modal_body->attribute($this->_selector_field_expense, "class");
                            $this->assertContains($this->_class_disabled, $classes);
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_btn_delete)
                                ->assertSee($this->_label_foot_btn_delete)
                                ->assertVisible($this->_selector_btn_lock)
                                ->assertVisible($this->_selector_btn_cancel)
                                ->assertSee($this->_label_foot_btn_cancel)
                                ->assertMissing($this->_selector_btn_save)
                                ->assertDontSee($this->_label_foot_btn_save);

                            $classes = $modal_foot->attribute($this->_selector_btn_lock_icon, 'class');
                            $this->assertContains($this->_class_unlock, $classes);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    public function testClickingOnEntryTableEditButtonOfConfirmedEntryThenUnlock(){
        $this->browse(function(Browser $browser){
            $confirmed_entry_selector = $this->randomConfirmedEntrySelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($confirmed_entry_selector)
                ->click($this->_selector_btn_lock)
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head
                        ->assertDontSee($this->_label_head_entry_new)
                        ->assertSee($this->_label_head_entry_not_new)
                        ->assertSee($this->_label_head_btn_confirmed);

                    $classes = $modal_head->attribute($this->_selector_btn_confirmed_label, "class");
                    $this->assertContains($this->_class_white_text, $classes, $this->_selector_btn_confirmed_label." is missing class:".$this->_class_white_text);
                    $this->assertNotContains($this->_class_light_grey_text, $classes, $this->_selector_btn_confirmed_label." has missing class:".$this->_class_light_grey_text);

                    $this->assertNotEquals("true", $modal_head->attribute($this->_selector_btn_confirmed, "disabled"));
                })

                ->with($this->_selector_modal_body, function($modal_body){
                    $modal_body
                        ->assertVisible($this->_selector_field_file_upload)
                        ->assertSee($this->_label_body_file_upload);

                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_field_date, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_field_value, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_field_account_type, 'disabled'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_field_memo, 'readonly'));

                    $classes = $modal_body->attribute($this->_selector_field_expense, "class");
                    $this->assertNotContains($this->_class_disabled, $classes);
                })

                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot
                        ->assertVisible($this->_selector_btn_delete)
                        ->assertSee($this->_label_foot_btn_delete)
                        ->assertVisible($this->_selector_btn_lock)
                        ->assertVisible($this->_selector_btn_cancel)
                        ->assertSee($this->_label_foot_btn_cancel)
                        ->assertVisible($this->_selector_btn_save)
                        ->assertSee($this->_label_foot_btn_save);

                    $classes = $modal_foot->attribute($this->_selector_btn_lock_icon, "class");
                    $this->assertContains($this->_class_lock, $classes);
                    $this->assertNotContains($this->_class_unlock, $classes);
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfEntryWithAttachments(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector().'.'.$this->_class_has_attachments;
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal){
                    $entry_modal->assertVisible($this->_selector_existing_attachments);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertGreaterThan(0, count($elements));
                });
        });
    }

    public function providerEntryWithTags(){
        return [
            "Confirmed"=>[$this->randomConfirmedEntrySelector().'.'.$this->_class_has_tags, $this->_selector_tags, $this->_selector_tags_tag],
            "Unconfirmed"=>[$this->randomUnconfirmedEntrySelector().'.'.$this->_class_has_tags, $this->_selector_tags_input, $this->_selector_tags_input_tag],
        ];
    }

    /**
     * @dataProvider providerEntryWithTags
     * @param string $data_entry_selector
     * @param string $data_tags_container_selector
     * @param string $data_tag_selector
     *
     * @throws \Throwable
     */
    public function testClickingOnEntryTableEditButtonOfEntryWithTags($data_entry_selector, $data_tags_container_selector, $data_tag_selector){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_tags_container_selector, $data_tag_selector){
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($data_entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use ($data_entry_selector, $data_tags_container_selector, $data_tag_selector){
                    $entry_modal->assertVisible($data_tags_container_selector);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::cssSelector($data_tag_selector));
                    $this->assertGreaterThan(0, count($elements), "Selector:\"".$data_entry_selector."\" opened entry-modal, but tags not present");
                });
        });
    }

    public function testOpenAttachment(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector().'.'.$this->_class_has_attachments;
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal){
                    $entry_modal
                        ->assertVisible($this->_selector_existing_attachments)
                        ->with($this->_selector_existing_attachments, function($existing_attachment){
                            $existing_attachment
                                ->assertVisible($this->_selector_existing_attachments_view_btn)
                                ->click($this->_selector_existing_attachments_view_btn);
                        });
                });

            // Get the last opened tab
            $window = collect($browser->driver->getWindowHandles())->last();
            // Switch to the tab
            $browser->driver->switchTo()->window($window);
            // Check if the path is correct
            $browser->assertPathBeginsWith('/attachment/');
        });
    }

    public function testDeleteAttachmentFromExistingEntry(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector().'.'.$this->_class_has_attachments;
            // initialising this variable here, then pass it as a reference so that we can update its value.
            $attachment_count = 0;

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use (&$attachment_count){
                    $entry_modal->assertVisible($this->_selector_existing_attachments);

                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $attachment_count = count($attachments);

                    $entry_modal->with($this->_selector_existing_attachments, function($existing_attachment){
                        $attachment_name = trim($existing_attachment->text('.'.$this->_class_existing_attachment));
                        $existing_attachment
                            ->assertVisible($this->_selector_existing_attachments_delete_btn)
                            ->click($this->_selector_existing_attachments_delete_btn)
                            ->assertDialogOpened("Are you sure you want to delete attachment: ".$attachment_name)
                            ->acceptDialog();
                    });
                })
                ->waitForLoadingToStop()
                ->with($this->_selector_entry_modal, function($entry_modal) use (&$attachment_count){
                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertEquals($attachment_count-1, count($attachments), "Attachment was NOT removed from UI");
                });
        });
    }

    public function testUpdateExistingEntry(){
        $this->markTestIncomplete("TODO: build");
    }

    public function testOpenExistingEntryInModalThenCloseModalAndOpenNewEntryModal(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                // open existing entry in modal and confirm fields are filled
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal){
                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_head_entry_new)
                                ->assertSee($this->_label_head_entry_not_new)
                                ->assertSee($this->_label_head_btn_confirmed);
                        })

                        ->with($this->_selector_modal_body, function($modal_body){
                            $modal_body
                                ->assertInputValueIsNot($this->_selector_field_date, "")
                                ->assertInputValueIsNot($this->_selector_field_value, "")
                                ->assertNotSelected($this->_selector_field_account_type, "")
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertInputValueIsNot($this->_selector_field_memo, "");
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_btn_delete)
                                ->assertVisible($this->_selector_btn_cancel)
                                // close entry-modal
                                ->click($this->_selector_btn_cancel);
                        });
                })
                ->waitUntilMissing($this->_selector_entry_modal, HomePage::WAIT_SECONDS)

                // open entry-modal from navbar; fields should be empty
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head
                        ->assertSee($this->_label_head_entry_new)
                        ->assertSee($this->_label_head_btn_confirmed)
                        ->assertNotChecked($this->_selector_btn_confirmed);

                    $entry_confirm_class = $modal_head->attribute($this->_selector_btn_confirmed_label, 'class');
                    $this->assertContains($this->_class_light_grey_text, $entry_confirm_class);
                })

                ->with($this->_selector_modal_body, function($modal_body){
                    $modal_body
                        ->assertInputValue($this->_selector_field_date, date("Y-m-d"))
                        ->assertInputValue($this->_selector_field_value, "")
                        ->assertSelected($this->_selector_field_account_type, "")
                        ->assertDontSee($this->_label_body_meta_account_name)
                        ->assertDontSee($this->_label_body_meta_last_digits)
                        ->assertInputValue($this->_selector_field_memo, "");
                })

                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot
                            ->assertMissing($this->_selector_btn_delete)   // delete button
                            ->assertMissing($this->_selector_btn_lock)     // lock/unlock button
                            ->assertVisible($this->_selector_btn_save);    // save button
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    private function randomConfirmedEntrySelector(){
        $confirmed_entry_selectors = [$this->_selector_confirmed_expense, $this->_selector_confirmed_income];
        return $confirmed_entry_selectors[array_rand($confirmed_entry_selectors, 1)];
    }

    private function randomUnconfirmedEntrySelector(){
        $unconfirmed_entry_selectors = [$this->_selector_unconfirmed_expense, $this->_selector_unconfirmed_income];
        return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
    }

    private function randomEntrySelector(){
        $entry_selectors = [$this->randomConfirmedEntrySelector(), $this->randomUnconfirmedEntrySelector()];
        return $entry_selectors[array_rand($entry_selectors, 1)];
    }

}