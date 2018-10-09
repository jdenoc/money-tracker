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
    private $_selector_entry_modal = "#entry-modal";
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
    private $_label_body_meta_account_name = "Account Type:";
    private $_label_body_meta_last_digits = "Last 4 Digits:";
    private $_label_body_expense = "Expense";
    private $_label_body_income = "Income";
    private $_label_body_file_upload = "Drag & Drop";
    private $_label_foot_btn_cancel = "Cancel";
    private $_label_foot_btn_delete = "Delete";
    private $_label_foot_btn_save = "Save changes";

    public function testClickingOnEntryTableEditButtonOfUnconfirmedExpense(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage())
                ->openExistingEntryModal($this->_selector_unconfirmed_expense)
                ->with($this->_selector_entry_modal, function($entry_modal){
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
                            $this->assertContains($this->_class_light_grey_text, $entry_confirm_class);
                        })

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data){
                            $modal_body
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertSee($this->_label_body_expense);

                            $this->assertEquals($entry_data['entry_date'], $modal_body->value($this->_selector_field_date));
                            $this->assertEquals($entry_data['entry_value'], $modal_body->value($this->_selector_field_value));
                            $this->assertEquals($entry_data['account_type_id'], $modal_body->value($this->_selector_field_account_type));
                            $this->assertEquals($entry_data['memo'], $modal_body->value($this->_selector_field_memo));
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

                            $this->assertNotEquals("true", $modal_foot->attribute($this->_selector_btn_save, "disabled"));
                        });
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfUnconfirmedIncome(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage())
                ->openExistingEntryModal($this->_selector_unconfirmed_income)
                ->with($this->_selector_entry_modal, function($entry_modal){
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

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data){
                            $modal_body
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertSee($this->_label_body_income);

                            $this->assertEquals($entry_data['entry_date'], $modal_body->value($this->_selector_field_date));
                            $this->assertEquals($entry_data['entry_value'], $modal_body->value($this->_selector_field_value));
                            $this->assertEquals($entry_data['account_type_id'], $modal_body->value($this->_selector_field_account_type));
                            $this->assertEquals($entry_data['memo'], $modal_body->value($this->_selector_field_memo));
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

                            $this->assertNotEquals("true", $modal_foot->attribute($this->_selector_btn_save, "disabled"));
                        });
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfConfirmedIncome(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage())
                ->openExistingEntryModal($this->_selector_confirmed_income)
                ->with($this->_selector_entry_modal, function($entry_modal){
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

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data){
                            $modal_body
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertSee($this->_label_body_income)
                                ->assertMissing($this->_selector_field_file_upload)
                                ->assertDontSee($this->_label_body_file_upload);

                            $this->assertEquals($entry_data['entry_date'], $modal_body->value($this->_selector_field_date));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_date, "readonly"));
                            $this->assertEquals($entry_data['entry_value'], $modal_body->value($this->_selector_field_value));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_value, "readonly"));
                            $this->assertEquals($entry_data['account_type_id'], $modal_body->value($this->_selector_field_account_type));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_account_type, "disabled"));
                            $this->assertEquals($entry_data['memo'], $modal_body->value($this->_selector_field_memo));
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
                            $this->assertNotEquals("true", $modal_foot->attribute($this->_selector_btn_save, "disabled"));
                        });
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfConfirmedExpense(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage())
                ->openExistingEntryModal($this->_selector_confirmed_expense)
                ->with($this->_selector_entry_modal, function($entry_modal){
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

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data){
                            $modal_body
                                ->assertSee($this->_label_body_meta_account_name)
                                ->assertSee($this->_label_body_meta_last_digits)
                                ->assertSee($this->_label_body_expense)
                                ->assertMissing($this->_selector_field_file_upload)
                                ->assertDontSee($this->_label_body_file_upload);

                            $this->assertEquals($entry_data['entry_date'], $modal_body->value($this->_selector_field_date));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_date, "readonly"));
                            $this->assertEquals($entry_data['entry_value'], $modal_body->value($this->_selector_field_value));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_value, "readonly"));
                            $this->assertEquals($entry_data['account_type_id'], $modal_body->value($this->_selector_field_account_type));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_field_account_type, "disabled"));
                            $this->assertEquals($entry_data['memo'], $modal_body->value($this->_selector_field_memo));
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
                            $this->assertNotEquals("true", $modal_foot->attribute($this->_selector_btn_save, "disabled"));
                        });
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfConfirmedEntryThenUnlock(){
        $this->browse(function(Browser $browser){
            $confirmed_entry_selector = $this->randomConfirmedEntrySelector();
            $browser->visit(new HomePage())
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
            $entry_selectors = [$this->randomConfirmedEntrySelector(), $this->randomUnconfirmedEntrySelector()];
            $entry_selector = $entry_selectors[array_rand($entry_selectors, 1)];
            $entry_selector .= '.'.$this->_class_has_attachments;
            $browser->visit(new HomePage())
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal){
                    $entry_modal->assertVisible($this->_selector_existing_attachments);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertGreaterThan(0, count($elements));
                });
        });
    }

    public function testOpensWhenClickingOnEntryTableEditButtonOfConfirmedEntryWithTags(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomConfirmedEntrySelector();
            $entry_selector .= '.'.$this->_class_has_tags;
            $browser->visit(new HomePage())
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use ($entry_selector){
                    $entry_modal->assertVisible($this->_selector_tags);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::cssSelector($this->_selector_tags_tag));
                    $this->assertGreaterThan(0, count($elements), "Selector:\"".$entry_selector."\" opened entry-modal, but tags not present");
                });
        });
    }

    public function testOpensWhenClickingOnEntryTableEditButtonOfUnconfirmedEntryWithTags(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomUnconfirmedEntrySelector();
            $entry_selector .= '.'.$this->_class_has_tags;
            $browser->visit(new HomePage())
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_entry_modal, function($entry_modal) use ($entry_selector){
                    $entry_modal->assertVisible($this->_selector_tags_input);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::cssSelector($this->_selector_tags_input_tag));
                    $this->assertGreaterThan(0, count($elements), "Selector:\"".$entry_selector."\" opened entry-modal, but tags not present");
                });
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

}