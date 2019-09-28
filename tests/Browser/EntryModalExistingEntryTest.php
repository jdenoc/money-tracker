<?php

namespace Tests\Browser;

use App\Http\Controllers\Api\EntryController;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;

/**
 * Class EntryModalExistingEntryTest
 *
 * @package Tests\Browser
 *
 * @group entry-modal
 * @group modal
 * @group home
 */
class EntryModalExistingEntryTest extends DuskTestCase {

    use DatabaseMigrations;
    use HomePageSelectors;

    private $_class_lock = "fa-lock";
    private $_class_unlock = "fa-unlock-alt";
    private $_class_disabled = "disabled";
    private $_class_white_text = "has-text-white";
    private $_class_light_grey_text = "has-text-grey-light";
    private $_class_has_attachments = "has-attachments";
    private $_class_is_transfer = "is-transfer";
    private $_class_has_tags = "has-tags";
    private $_class_existing_attachment = "existing-attachment";
    private $_modal_id_prefix = "#entry-";

    public function providerUnconfirmedEntry(){
        return [
            "Expense"=>[$this->_selector_table_unconfirmed_expense, $this->_label_expense_switch_expense],
            "Income"=>[$this->_selector_table_unconfirmed_income, $this->_label_expense_switch_income],
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
                ->with($this->_selector_modal_entry, function($entry_modal) use ($data_expense_switch_label){
                    $entry_id = $entry_modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                            $entry_confirm_class = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, 'class');
                            $this->assertContains($this->_class_light_grey_text, $entry_confirm_class);
                        })

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data, $data_expense_switch_label){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo'])
                                ->assertSee($this->_label_account_type_meta_account_name)
                                ->assertSee($this->_label_account_type_meta_last_digits)
                                ->assertSee($data_expense_switch_label);
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_modal_entry_btn_delete)
                                ->assertSee($this->_label_btn_delete)
                                ->assertMissing($this->_selector_modal_entry_btn_lock)
                                ->assertVisible($this->_selector_modal_entry_btn_cancel)
                                ->assertSee($this->_label_btn_cancel)
                                ->assertVisible($this->_selector_modal_entry_btn_save)
                                ->assertSee($this->_label_btn_save);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    public function providerConfirmedEntry(){
        return [
            "Expense"=>[$this->_selector_table_confirmed_expense, $this->_label_expense_switch_expense],
            "Income"=>[$this->_selector_table_confirmed_income, $this->_label_expense_switch_income],
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
                ->with($this->_selector_modal_entry, function($entry_modal) use ($data_expense_switch_label){
                    $entry_id = $entry_modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);

                            $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                            $this->assertContains($this->_class_white_text, $classes);
                            $this->assertNotContains($this->_class_light_grey_text, $classes);
                            $this->assertEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_confirmed, "disabled"));
                        })

                        ->with($this->_selector_modal_body, function($modal_body) use ($entry_data, $data_expense_switch_label){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertSee($this->_label_account_type_meta_account_name)
                                ->assertSee($this->_label_account_type_meta_last_digits)
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo'])
                                ->assertSee($data_expense_switch_label)
                                ->assertMissing($this->_selector_modal_entry_field_upload)
                                ->assertDontSee($this->_label_file_upload);

                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, "disabled"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, "readonly"));

                            $classes = $modal_body->attribute($this->_selector_modal_entry_field_expense, "class");
                            $this->assertContains($this->_class_disabled, $classes);
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_modal_entry_btn_delete)
                                ->assertSee($this->_label_btn_delete)
                                ->assertVisible($this->_selector_modal_entry_btn_lock)
                                ->assertVisible($this->_selector_modal_entry_btn_cancel)
                                ->assertSee($this->_label_btn_cancel)
                                ->assertMissing($this->_selector_modal_entry_btn_save)
                                ->assertDontSee($this->_label_btn_save);

                            $classes = $modal_foot->attribute($this->_selector_modal_entry_btn_lock_icon, 'class');
                            $this->assertContains($this->_class_unlock, $classes);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    public function testClickingOnEntryTableEditButtonOfConfirmedEntryThenUnlock(){
        $this->browse(function(Browser $browser){
            $confirmed_entry_selector = $this->randomConfirmedEntrySelector(true);
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($confirmed_entry_selector)
                ->click($this->_selector_modal_entry_btn_lock)
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head
                        ->assertDontSee($this->_label_entry_new)
                        ->assertSee($this->_label_entry_not_new)
                        ->assertSee($this->_label_btn_confirmed);

                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    $this->assertContains($this->_class_white_text, $classes, $this->_selector_modal_entry_btn_confirmed_label." is missing class:".$this->_class_white_text);
                    $this->assertNotContains($this->_class_light_grey_text, $classes, $this->_selector_modal_entry_btn_confirmed_label." has missing class:".$this->_class_light_grey_text);

                    $this->assertNotEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_confirmed, "disabled"));
                })

                ->with($this->_selector_modal_body, function($modal_body){
                    $modal_body
                        ->assertVisible($this->_selector_modal_entry_field_upload)
                        ->assertSee($this->_label_file_upload);

                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, 'disabled'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, 'readonly'));

                    $classes = $modal_body->attribute($this->_selector_modal_entry_field_expense, "class");
                    $this->assertNotContains($this->_class_disabled, $classes);
                })

                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot
                        ->assertVisible($this->_selector_modal_entry_btn_delete)
                        ->assertSee($this->_label_btn_delete)
                        ->assertVisible($this->_selector_modal_entry_btn_lock)
                        ->assertVisible($this->_selector_modal_entry_btn_cancel)
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_entry_btn_save)
                        ->assertSee($this->_label_btn_save);

                    $classes = $modal_foot->attribute($this->_selector_modal_entry_btn_lock_icon, "class");
                    $this->assertContains($this->_class_lock, $classes);
                    $this->assertNotContains($this->_class_unlock, $classes);
                });
        });
    }

    public function testClickingOnEntryTableEditButtonOfEntryWithAttachments(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function($entry_modal){
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertGreaterThan(0, count($elements));
                });
        });
    }

    public function providerEntryWithTags(){
        return [
            "Confirmed"=>[$this->randomConfirmedEntrySelector().'.'.$this->_class_has_tags, $this->_selector_tags, $this->_selector_tags_tag],
            "Unconfirmed"=>[$this->randomUnconfirmedEntrySelector().'.'.$this->_class_has_tags, $this->_selector_modal_entry_field_tags, $this->_selector_modal_entry_field_tags_input_tag],
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
                ->with($this->_selector_modal_entry, function($entry_modal) use ($data_entry_selector, $data_tags_container_selector, $data_tag_selector){
                    $entry_modal->assertVisible($data_tags_container_selector);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::cssSelector($data_tag_selector));
                    $this->assertGreaterThan(0, count($elements), "Selector:\"".$data_entry_selector."\" opened entry-modal, but tags not present");
                });
        });
    }

    public function testOpenAttachment(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function($entry_modal){
                    $entry_modal
                        ->assertVisible($this->_selector_modal_entry_existing_attachments)
                        ->with($this->_selector_modal_entry_existing_attachments, function($existing_attachment){
                            $existing_attachment
                                ->assertVisible($this->_selector_modal_entry_existing_attachments_btn_view)
                                ->click($this->_selector_modal_entry_existing_attachments_btn_view);
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
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            // initialising this variable here, then pass it as a reference so that we can update its value.
            $attachment_count = 0;

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function($entry_modal) use (&$attachment_count){
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $attachment_count = count($attachments);

                    $entry_modal->with($this->_selector_modal_entry_existing_attachments, function($existing_attachment){
                        $attachment_name = trim($existing_attachment->text('.'.$this->_class_existing_attachment));
                        $existing_attachment
                            ->assertVisible($this->_selector_modal_entry_existing_attachments_btn_delete)
                            ->click($this->_selector_modal_entry_existing_attachments_btn_delete)
                            ->assertDialogOpened("Are you sure you want to delete attachment: ".$attachment_name)
                            ->acceptDialog();
                    });
                })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_INFO, "Attachment has been deleted")
                ->with($this->_selector_modal_entry, function($entry_modal) use (&$attachment_count){
                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertEquals($attachment_count-1, count($attachments), "Attachment was NOT removed from UI");
                });
        });
    }

    public function testUpdateExistingEntryDate(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = date("Y-m-d", strtotime("-90 days"));

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, $new_value){
                    $old_value = $modal_body->value($this->_selector_modal_entry_field_date);
                    // clear input[type="date"]
                    for($i=0; $i<strlen($old_value); $i++){
                        $modal_body->keys($this->_selector_modal_entry_field_date, "{backspace}");
                    }

                    $browser_date = $modal_body->getDateFromLocale($modal_body->getBrowserLocale(), $new_value);
                    $new_value_to_type = $modal_body->processLocaleDateForTyping($browser_date);
                    $modal_body->type($this->_selector_modal_entry_field_date, $new_value_to_type);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->scrollToElement($entry_selector)
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($modal_body) use (&$old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_date));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_date));
                });
        });
    }

    public function testUpdateExistingEntryAccountType(){
        $account_types = $this->getApiAccountTypes();
        $this->browse(function(Browser $browser) use ($account_types){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = "";

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($modal_body) use (&$old_value, &$new_value, $account_types){
                    $old_value = $modal_body->value($this->_selector_modal_entry_field_account_type);
                    do{
                        $account_type = $account_types[array_rand($account_types, 1)];
                        $new_value = $account_type['id'];
                    }while($old_value == $new_value);
                    $modal_body->select($this->_selector_modal_entry_field_account_type, $new_value);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($modal_body) use ($old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                });
        });
    }

    public function providerUpdateEntry(){
        return [
            'entry_value'=>[$this->_selector_modal_entry_field_value, 0.01],
            'memo'=>[$this->_selector_modal_entry_field_memo, "hfrsighesiugbeusigbweuisgbeisugsebuibseiugbg"],
        ];
    }

    /**
     * @dataProvider providerUpdateEntry
     * @param string $field_selector
     * @param $new_value
     *
     * @throws \Throwable
     */
    public function testUpdateExistingEntryValue($field_selector, $new_value){
        $this->browse(function(Browser $browser) use ($field_selector, $new_value){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($modal_body) use ($field_selector, &$old_value, $new_value){
                    $old_value = $modal_body->value($field_selector);
                    $modal_body->clear($field_selector);
                    $modal_body->type($field_selector, $new_value);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($modal_body) use ($field_selector, &$old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->value($field_selector));
                    $this->assertEquals($new_value, $modal_body->value($field_selector));
                });
        });
    }

    public function providerOpenExistingEntryInModalThenChangeConfirmSwitch(){
        return [
            'unconfirmed->confirmed'=>[false],
            'confirmed->unconfirmed'=>[true]
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeConfirmSwitch
     * @param bool $selector_bool
     *
     * @throws \Throwable
     */
    public function testOpenExistingEntryInModalThenChangeConfirmSwitch($selector_bool){
        $entry_selector = $this->randomEntrySelector(['confirm'=>$selector_bool]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool){
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?'is-confirmed':'has-background-warning'))
                ->with($this->_selector_modal_foot, function(Browser $modal_foot) use ($selector_bool){
                    if($selector_bool){
                        $modal_foot->click($this->_selector_modal_entry_btn_lock);
                    }
                })
                ->with($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool){
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    if($selector_bool){
                        $this->assertContains($this->_class_white_text, $classes);
                        $this->assertNotContains($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertContains($this->_class_light_grey_text, $classes);
                        $this->assertNotContains($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    }

                    $modal_head->click($this->_selector_modal_entry_btn_confirmed_label);
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");

                    if($selector_bool){
                        $this->assertContains($this->_class_light_grey_text, $classes);
                        $this->assertNotContains($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertContains($this->_class_white_text, $classes);
                        $this->assertNotContains($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    }
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?'has-background-warning':'is-confirmed'))
                ->with($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool){
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    if($selector_bool){
                        $this->assertContains($this->_class_light_grey_text, $classes);
                        $this->assertNotContains($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertContains($this->_class_white_text, $classes);
                        $this->assertNotContains($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    }
                });
        });
    }

    public function providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch(){
        return [
            'expense->income'=>[true],
            'income->expense'=>[false],
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch
     * @param bool $selector_bool
     *
     * @throws \Throwable
     */
    public function testOpenExistingEntryInModalThenChangeExpenseIncomeSwitch($selector_bool){
        $entry_selector = $this->randomEntrySelector(['expense'=>$selector_bool, 'confirm'=>false]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool){
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool ? $this->_class_is_expense:$this->_class_is_income))
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool){
                    $entry_expense_switch_text = $modal_body->text($this->_selector_modal_entry_field_expense);
                    $this->assertEquals($selector_bool ? $this->_label_expense_switch_expense:$this->_label_expense_switch_income , $entry_expense_switch_text);
                    $modal_body
                        ->click($this->_selector_modal_entry_field_expense)
                        ->pause(500); // 0.5 seconds - need to wait for the transition to complete after click;
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?$this->_class_is_income:$this->_class_is_expense))
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool){
                    $entry_expense_switch_text = $modal_body->text($this->_selector_modal_entry_field_expense);
                    $this->assertEquals(($selector_bool?$this->_label_expense_switch_income:$this->_label_expense_switch_expense), $entry_expense_switch_text);
                });
        });
    }

    public function testExistingTransferEntryHasEntryButton(){
        $this->browse(function(Browser $browser){
            do{
                $entry_selector = $this->randomEntrySelector(['is_transfer'=>true]);
                $entry_id = str_replace($this->_modal_id_prefix, "", $entry_selector);
                $entry_data = $this->getApiEntry($entry_id);
            }while($entry_data['transfer_entry_id'] === 0);
            $transfer_entry_data = $this->getApiEntry($entry_data['transfer_entry_id']);
            $this->assertEquals($entry_id, $entry_data['id']);
            $this->assertEquals($entry_data['transfer_entry_id'], $transfer_entry_data['id']);
            $this->assertEquals($transfer_entry_data['transfer_entry_id'], $entry_data['id']);
            $entry_selector .= '.'.$this->_class_is_transfer;

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id){
                    $entry_modal->with($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id){
                        $modal_entry_id = $modal_head->value($this->_selector_modal_entry_field_entry_id);
                        $this->assertNotEmpty($modal_entry_id);
                        $this->assertEquals($entry_id, $modal_entry_id);

                        $modal_head->assertVisible($this->_selector_modal_entry_btn_transfer);
                        $this->assertNotEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_transfer, "disabled"));
                        $modal_head->click($this->_selector_modal_entry_btn_transfer);
                    });
                })
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_modal_entry)
                ->with($this->_selector_modal_entry, function(Browser $entry_modal) use ($transfer_entry_data){
                    $entry_modal
                        ->with($this->_selector_modal_body, function(Browser $modal_body) use ($transfer_entry_data){
                            $expense_switch_label = $transfer_entry_data['expense'] ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;

                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $transfer_entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $transfer_entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $transfer_entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $transfer_entry_data['memo'])
                                ->assertSeeIn($this->_selector_modal_entry_field_expense, $expense_switch_label);
                        })
                        ->with($this->_selector_modal_head, function(Browser $modal_head) use ($transfer_entry_data){
                            $modal_entry_id = $modal_head->value($this->_selector_modal_entry_field_entry_id);
                            $this->assertNotEmpty($modal_entry_id);
                            $this->assertEquals($transfer_entry_data['id'], $modal_entry_id);

                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertVisible($this->_selector_modal_entry_btn_transfer)
                                ->click($this->_selector_modal_entry_btn_transfer);
                        });
                })
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_modal_entry)
                ->with($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_data){
                    $entry_modal
                        ->with($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data){
                            $expense_switch_label = $entry_data['expense'] ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;

                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo'])
                                ->assertSeeIn($this->_selector_modal_entry_field_expense, $expense_switch_label);
                        })
                        ->with($this->_selector_modal_head, function(Browser $modal_head) use ($entry_data){
                            $modal_entry_id = $modal_head->value($this->_selector_modal_entry_field_entry_id);
                            $this->assertNotEmpty($modal_entry_id);
                            $this->assertEquals($entry_data['id'], $modal_entry_id);

                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertVisible($this->_selector_modal_entry_btn_transfer);
                        });
                });
        });
    }

    public function testExistingExternalTransferEntryHasButtonButIsDisabled(){
        $this->browse(function(Browser $browser){
            do{
                $entry_selector = $this->randomEntrySelector(['is_transfer'=>true]);
                $entry_id = str_replace($this->_modal_id_prefix, "", $entry_selector);
                $entry_data = $this->getApiEntry($entry_id);
            } while($entry_data['transfer_entry_id'] !== EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID);
            $this->assertEquals($entry_id, $entry_data['id']);
            $entry_selector .= '.'.$this->_class_is_transfer;

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id){
                    $entry_modal->with($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id){
                        $modal_entry_id = $modal_head->value($this->_selector_modal_entry_field_entry_id);
                        $this->assertNotEmpty($modal_entry_id);
                        $this->assertEquals($entry_id, $modal_entry_id);

                        $modal_head->assertVisible($this->_selector_modal_entry_btn_transfer);
                        $this->assertEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_transfer, "disabled"));
                    });
                });
        });
    }

    // TODO: write test for changing tags input values

    public function testUploadAttachmentToExistingEntry(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector(['confirm'=>false]);

            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $upload_file_path = storage_path($this->getRandomTestFileStoragePath());
                    $this->assertFileExists($upload_file_path);
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_entry_field_upload)
                        ->attach($this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path)
                        ->waitFor($this->_selector_modal_entry_dropzone_upload_thumbnail, HomePage::WAIT_SECONDS)
                        ->with($this->_selector_modal_entry_dropzone_upload_thumbnail, function(Browser $upload_thumbnail) use ($upload_file_path){
                            $upload_thumbnail
                                ->waitUntilMissing($this->_selector_modal_dropzone_progress, HomePage::WAIT_SECONDS)
                                ->assertMissing($this->_selector_modal_dropzone_error_mark)
                                ->mouseover("") // hover over current element
                                ->waitUntilMissing($this->_selector_modal_dropzone_success_mark, HomePage::WAIT_SECONDS)
                                ->assertSeeIn($this->_selector_modal_dropzone_label_filename, basename($upload_file_path))
                                ->assertMissing($this->_selector_modal_dropzone_error_message)
                                ->assertVisible($this->_selector_modal_dropzone_btn_remove)
                                ->assertSeeIn($this->_selector_modal_dropzone_btn_remove, $this->_label_btn_dropzone_remove_file)
                                ->click($this->_selector_modal_dropzone_btn_remove);
                        })
                        ->assertMissing($this->_selector_modal_entry_dropzone_upload_thumbnail);
                });
        });
    }

    public function testOpenExistingEntryInModalThenCloseModalAndOpenNewEntryModal(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                // open existing entry in modal and confirm fields are filled
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function($entry_modal){
                    $entry_modal
                        ->with($this->_selector_modal_head, function($modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                        })

                        ->with($this->_selector_modal_body, function($modal_body){
                            $modal_body
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_date, "")
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_value, "")
                                ->assertNotSelected($this->_selector_modal_entry_field_account_type, "")
                                ->assertSee($this->_label_account_type_meta_account_name)
                                ->assertSee($this->_label_account_type_meta_last_digits)
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_memo, "");
                        })

                        ->with($this->_selector_modal_foot, function($modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_modal_entry_btn_delete)
                                ->assertVisible($this->_selector_modal_entry_btn_cancel)
                                // close entry-modal
                                ->click($this->_selector_modal_entry_btn_cancel);
                        });
                })
                ->waitUntilMissing($this->_selector_modal_entry, HomePage::WAIT_SECONDS)

                // open entry-modal from navbar; fields should be empty
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head
                        ->assertSee($this->_label_entry_new)
                        ->assertSee($this->_label_btn_confirmed)
                        ->assertNotChecked($this->_selector_modal_entry_btn_confirmed);

                    $entry_confirm_class = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, 'class');
                    $this->assertContains($this->_class_light_grey_text, $entry_confirm_class);
                })

                ->with($this->_selector_modal_body, function($modal_body){
                    $modal_body
                        ->assertInputValue($this->_selector_modal_entry_field_date, date("Y-m-d"))
                        ->assertInputValue($this->_selector_modal_entry_field_value, "")
                        ->assertSelected($this->_selector_modal_entry_field_account_type, "")
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits)
                        ->assertInputValue($this->_selector_modal_entry_field_memo, "");
                })

                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot
                        ->assertMissing($this->_selector_modal_entry_btn_delete)   // delete button
                        ->assertMissing($this->_selector_modal_entry_btn_lock)     // lock/unlock button
                        ->assertVisible($this->_selector_modal_entry_btn_save);    // save button
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    private function randomConfirmedEntrySelector($get_id=false){
        if($get_id){
            return $this->randomEntrySelector(['confirm'=>true]);
        } else {
            $confirmed_entry_selectors = [$this->_selector_table_confirmed_expense, $this->_selector_table_confirmed_income];
            return $confirmed_entry_selectors[array_rand($confirmed_entry_selectors, 1)];
        }
    }

    private function randomUnconfirmedEntrySelector($get_id=false){
        if($get_id){
            return $this->randomEntrySelector(['confirm'=>false]);
        } else {
            $unconfirmed_entry_selectors = [$this->_selector_table_unconfirmed_expense, $this->_selector_table_unconfirmed_income];
            return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
        }
    }

    /**
     * @param array $entry_constraints
     * @return string
     */
    private function randomEntrySelector($entry_constraints = []){
        $entries = $this->getApiEntries();
        unset($entries['count']);
        $entries_collection = collect($entries);
        if(!empty($entry_constraints)){
            foreach(array_keys($entry_constraints) as $constraint){
                $entries_collection = $entries_collection->where($constraint, $entry_constraints[$constraint]);
            }
        }
        $entry_id = $entries_collection->random(1)->pluck('id')->first();
        return $this->_modal_id_prefix.$entry_id;
    }

}