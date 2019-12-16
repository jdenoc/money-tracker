<?php

namespace Tests\Browser;

use App\Account;
use App\AccountType;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;

/**
 * Class EntryModalNewEntryTest
 *
 * @package Tests\Browser
 *
 * @group entry-modal
 * @group modal
 * @group home
 */
class EntryModalNewEntryTest extends DuskTestCase {

    use HomePageSelectors;

    private $method_account = 'account';
    private $method_account_type = 'account-type';

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 1/25
     */
    public function testEntryModalIsNotVisibleByDefault(){
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 2/25
     */
    public function testEntryModalIsVisibleWhenNavbarElementIsClicked(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->assertVisible($this->_selector_modal_entry);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 3/25
     */
    public function testModalHeaderHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_entry.' '.$this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head
                        ->assertSee($this->_label_entry_new)
                        ->assertNotChecked($this->_selector_modal_entry_btn_confirmed)
                        ->assertSee($this->_label_btn_confirmed)
                        ->assertVisible($this->_selector_modal_btn_close);

                    $entry_confirm_class = $entry_modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, 'class');
                    $this->assertContains('has-text-grey-light', $entry_confirm_class);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 4/25
     */
    public function testCloseEntryModalWithXInModalHead(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 5/25
     */
    public function testConfirmedButtonActivatesWhenClicked(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($entry_modal_head){
                    $entry_modal_head
                        ->assertSee($this->_label_btn_confirmed)
                        ->click($this->_selector_modal_entry_btn_confirmed_label)
                        ->assertChecked($this->_selector_modal_entry_btn_confirmed);

                    $classes = $entry_modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    $this->assertContains("has-text-white", $classes);
                    $this->assertNotContains("has-text-grey-light", $classes);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 6/25
     */
    public function testModalBodyHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->assertSee('Date:')
                        ->assertVisible($this->_selector_modal_entry_field_date)
                        ->assertInputValue($this->_selector_modal_entry_field_date, date("Y-m-d"));
                    $this->assertEquals(
                        'date',
                        $entry_modal_body->attribute($this->_selector_modal_entry_field_date, 'type'),
                        $this->_selector_modal_entry_field_date.' is not type="date"'
                    );

                    $entry_modal_body
                        ->assertSee('Value:')
                        ->assertVisible($this->_selector_modal_entry_field_value)
                        ->assertInputValue($this->_selector_modal_entry_field_value, "");
                    $this->assertEquals(
                        'text',
                        $entry_modal_body->attribute($this->_selector_modal_entry_field_value, 'type'),
                        $this->_selector_modal_entry_field_value.' is not type="text"'
                    );

                    $entry_modal_body
                        ->assertSee('Account Type:')
                        ->assertVisible($this->_selector_modal_entry_field_account_type)
                        ->assertSelected($this->_selector_modal_entry_field_account_type, "")
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits)

                        ->assertSee('Memo:')
                        ->assertVisible($this->_selector_modal_entry_field_memo)
                        ->assertInputValue($this->_selector_modal_entry_field_memo, "")

                        ->assertVisible($this->_selector_modal_entry_field_expense)
                        ->assertSee($this->_label_expense_switch_expense)
                        ->assertElementColour($this->_selector_modal_entry_field_expense.' '.$this->_class_switch_core, $this->_color_expense_switch_expense)
                        ->assertDontSee($this->_label_expense_switch_income)

                        ->assertSee('Tags:')
                        ->assertVisible($this->_selector_modal_entry_field_tags)  // auto-complete tags-input field
                        ->assertInputValue($this->_selector_modal_entry_field_tags, "")

                        ->assertVisible($this->_selector_modal_entry_field_upload) // drag-n-drop file upload field
                        ->with($this->_selector_modal_entry_field_upload, function($file_upload){
                            $file_upload->assertSee($this->_label_file_upload);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 7/25
     */
    public function testModalFooterHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_foot, function($entry_modal_foot){
                    $entry_modal_foot
                        ->assertMissing($this->_selector_modal_entry_btn_delete)   // delete button
                        ->assertMissing($this->_selector_modal_entry_btn_lock)     // lock/unlock button
                        ->assertVisible($this->_selector_modal_entry_btn_cancel)   // cancel button
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_entry_btn_save)     // save button
                        ->assertSee($this->_label_btn_save);

                    $this->assertContains(
                        'is-success',
                        $entry_modal_foot->attribute($this->_selector_modal_entry_btn_save, 'class'),
                        "Save button should have 'is-success' class"
                    );
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 8/25
     */
    public function testCloseEntryModalWithCancelButton(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_foot, function($entry_modal_foot){
                    $entry_modal_foot->click($this->_selector_modal_entry_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 9/25
     */
    public function testCloseEntryModalWithHotkey(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->keys('', "{control}", "{escape}") // ["{control}", "{escape}"] didn't work
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 10/25
     */
    public function testEntryValueConvertsIntoDecimalOfTwoPlaces(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_value, "F15sae.92fwfw")
                        ->click($this->_selector_modal_entry_field_date)
                        ->assertInputValue($this->_selector_modal_entry_field_value, "15.92");
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 11/25
     */
    public function testSelectingAccountTypeDisplaysAccountTypeMetaData(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];
        $this->assertNotEmpty($account_type);

        $this->browse(function(Browser $browser) use ($account_type){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body){
                    // currency icon in input#entry-value is "$"
                    $entry_value_currency = $entry_modal_body->attribute($this->_selector_modal_entry_field_value." + .icon.is-left i", 'class');
                    $this->assertContains($this->_class_icon_dollar, $entry_value_currency);
                    // don't see account meta
                    $entry_modal_body
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                })
                ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                ->with($this->_selector_modal_body, function($entry_modal_body) use ($account_type){
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_entry_field_account_type)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->assertNotSelected($this->_selector_modal_entry_field_account_type, "")
                        ->assertSelected($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->assertSeeIn($this->_selector_modal_entry_field_account_type, $account_type['name'])
                        ->assertVisible($this->_selector_modal_entry_meta)
                        ->assertSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_account_name)
                        ->assertSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_last_digits)
                        ->select($this->_selector_modal_entry_field_account_type, "")
                        ->assertMissing($this->_selector_modal_entry_meta)
                        ->assertDontSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_account_name)
                        ->assertDontSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_last_digits);
                });
        });
    }

    public function providerSelectingDisabledAccountTypeMetaDataIsGrey(){
        // [$account_type_method]
        return [
            [$this->method_account],        // test 12/25
            [$this->method_account_type]    // test 13/25
        ];
    }

    /**
     * @dataProvider providerSelectingDisabledAccountTypeMetaDataIsGrey
     * @param string $account_type_method
     *
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test (see provider)/25
     */
    public function testSelectingDisabledAccountTypeMetaDataIsGrey($account_type_method){
        $account_types = AccountType::all();
        $disabled_account_type = [];
        if($account_type_method == $this->method_account){
            $disabled_account = Account::where('disabled', true)->get()->random();
            $disabled_account_type = $account_types->where('account_id', $disabled_account['id'])->random();
        } else if($account_type_method == $this->method_account_type) {
            $disabled_account_type = $account_types->where('disabled', true)->random();
        } else {
            $this->fail("Unknown account-type method provided");
        }

        $this->browse(function(Browser $browser) use ($disabled_account_type){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($disabled_account_type){
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_entry_field_account_type)
                        ->select($this->_selector_modal_entry_field_account_type, $disabled_account_type['id'])
                        ->assertVisible($this->_selector_modal_entry_meta);

                    $meta_text_color = $entry_modal_body->attribute($this->_selector_modal_entry_meta, 'class');
                    $this->assertNotContains('has-text-info', $meta_text_color);
                    $this->assertContains('has-text-grey-light', $meta_text_color);

                    $entry_modal_body
                        ->select($this->_selector_modal_entry_field_account_type, '')
                        ->assertMissing($this->_selector_modal_entry_meta);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 14/25
     */
    public function testSelectingAccountTypeChangesCurrency(){
        // this test relies on a consistent database to test with
        // we can't use a dataProvider as the data is wiped by the time the test(s) are run
        $accounts = Account::all()->unique('currency');
        foreach($accounts as $account){
            // See resources/assets/js/currency.js for list of supported currencies
            switch($account['currency']){
                case 'EUR':
                    $currency_class = $this->_class_icon_euro;
                    break;
                case 'GBP':
                    $currency_class = $this->_class_icon_pound;
                    break;
                case 'CAD':
                case 'USD':
                default:
                    $currency_class = $this->_class_icon_dollar;
                    break;
            }

            $account_type = AccountType::where('account_id', $account['id'])->get()->random();

            $this->browse(function(Browser $browser) use ($account_type, $currency_class){
                $browser
                    ->visit(new HomePage())
                    ->waitForLoadingToStop()
                    ->openNewEntryModal()
                    ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                    ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($account_type, $currency_class){
                        // currency icon in input#entry-value is "$" by default
                        $entry_value_currency = $entry_modal_body->attribute($this->_selector_modal_entry_field_value." + .icon.is-left i", 'class');
                        $this->assertContains($this->_class_icon_dollar, $entry_value_currency);

                        $entry_modal_body
                            ->assertVisible($this->_selector_modal_entry_field_account_type)
                            ->select($this->_selector_modal_entry_field_account_type, $account_type['id']);

                        $entry_value_currency = $entry_modal_body->attribute($this->_selector_modal_entry_field_value." + .icon.is-left i", 'class');
                        $this->assertContains($currency_class, $entry_value_currency);

                        // revert account-type select field to default state
                        $entry_modal_body->select($this->_selector_modal_entry_field_account_type, '');
                        $entry_value_currency = $entry_modal_body->attribute($this->_selector_modal_entry_field_value." + .icon.is-left i", 'class');
                        $this->assertContains($this->_class_icon_dollar, $entry_value_currency);
                    });
            });

        }
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 15/25
     */
    public function testClickingExpenseIncomeSwitch(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->assertSee($this->_label_expense_switch_expense)
                        ->assertElementColour($this->_selector_modal_entry_field_expense.' '.$this->_class_switch_core, $this->_color_expense_switch_expense)
                        ->click($this->_selector_modal_entry_field_expense)
                        ->pause(500) // 0.5 seconds - need to wait for the transition to complete after click
                        ->assertSee($this->_label_expense_switch_income)
                        ->assertElementColour($this->_selector_modal_entry_field_expense.' '.$this->_class_switch_core, $this->_color_expense_switch_income)
                        ->assertDontSee($this->_label_expense_switch_expense);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 16/25
     */
    public function testFillFieldsToEnabledSaveButton(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    // The date field should already be filled in. No need to fill it in again.
                    $entry_modal_body->assertInputValue($this->_selector_modal_entry_field_date, date("Y-m-d"));
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body->type($this->_selector_modal_entry_field_value, "9.99");
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                ->with($this->_selector_modal_body, function($entry_modal_body) use ($account_type){
                    $entry_modal_body
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->assertSee($this->_label_account_type_meta_account_name)
                        ->assertSee($this->_label_account_type_meta_last_digits);
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_memo, "Test entry")
                        ->click($this->_selector_modal_entry_field_date);
                })
                ->assertEntryModalSaveButtonIsNotDisabled()

                ->with($this->_selector_modal_body, function($entry_modal_body){
                    // laravel dusk has an issue typing into input[type="date"] fields
                    // work-around for this is to use individual key-strokes
                    $backspace_count = strlen($entry_modal_body->value($this->_selector_modal_entry_field_date));
                    for($i=0; $i<$backspace_count; $i++){
                        $entry_modal_body->keys($this->_selector_modal_entry_field_date, "{backspace}");
                    }
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 17/25
     */
    public function testUploadAttachmentToNewEntry(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($entry_modal_body){
                    $upload_file_path = \Storage::path($this->getRandomTestFileStoragePath());
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

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 18/25
     */
    public function testUploadAttachmentAndAttachmentIsNotPresentAfterClosingAndReopeningModal(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body){
                    $upload_file_path = \Storage::path($this->getRandomTestFileStoragePath());
                    $this->assertFileExists($upload_file_path);
                    $modal_body
                        ->assertVisible($this->_selector_modal_entry_field_upload)
                        ->attach($this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path)
                        ->waitFor($this->_selector_modal_entry_dropzone_upload_thumbnail, HomePage::WAIT_SECONDS);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_cancel);
                })
                ->waitUntilMissing($this->_selector_modal_entry)
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body){
                    $modal_body->assertMissing($this->_selector_modal_entry_dropzone_upload_thumbnail);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 19/25
     */
    public function testTagsInputAutoComplete(){
        // select tag at random and input the first character into the tags-input field
        $tags = $this->getApiTags();
        $tag = $tags[array_rand($tags, 1)]['name'];

        $this->browse(function(Browser $browser) use ($tag){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function ($entry_modal_body) use ($tag){
                    $first_char = substr($tag, 0, 1);
                    $entry_modal_body
                        ->waitUntilMissing($this->_selector_modal_entry_field_tags_container_is_loading, HomePage::WAIT_SECONDS)
                        ->keys($this->_selector_modal_entry_field_tags, $first_char)
                        ->waitFor($this->_selector_modal_tag_autocomplete_options)
                        ->assertSee($tag);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 20/25
     */
    public function testCreateEntryWithMinimumRequirementsExpense(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $memo_field = "Test entry - save requirements - expense";
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, $this->_label_notification_new_entry_created)
                ->assertMissing($this->_selector_modal_entry)
                ->with($this->_selector_table.' .has-background-warning.is-expense', function($table_row) use ($memo_field){
                    $table_row->assertSee($memo_field);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 21/25
     */
    public function testCreateEntryWithMinimumRequirementsIncome(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $memo_field = "Test entry - save requirements - income";
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field)
                        ->click($this->_selector_modal_entry_field_expense);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, $this->_label_notification_new_entry_created)
                ->assertMissing($this->_selector_modal_entry)
                ->with($this->_selector_table.' .has-background-warning.is-income', function($table_row) use ($memo_field){
                    $table_row->assertSee($memo_field);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test 22/25
     */
    public function testCreateConfirmedEntry(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $memo_field = "Test entry - confirmed";
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head->click($this->_selector_modal_entry_btn_confirmed_label);
                })
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, $this->_label_notification_new_entry_created)
                ->assertMissing($this->_selector_modal_entry)
                ->with($this->_selector_table.' .is-confirmed', function($table_row) use ($memo_field){
                    $table_row->assertSee($memo_field);
                });
        });
    }

    public function providerCreateGenericEntry(){
        return [
            // [$has_tags, $has_attachments]
            [false, false], // test 23/25
            [true, false],  // test 24/25
            [false, true],  // test 25/25
            [true, true]    // test 26/25
        ];
    }

    /**
     * @dataProvider providerCreateGenericEntry
     * @param bool $has_tags
     * @param bool $has_attachments
     *
     * @throws \Throwable
     *
     * @group entry-modal-2
     * test (see provider)/25
     */
    public function testCreateGenericEntry($has_tags, $has_attachments){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type, $has_tags, $has_attachments){
            $memo_field = "Test entry - generic".($has_tags?" w\ tags":"").($has_attachments?" \w attachments":"");
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);

                });

            if($has_tags){
                $browser->with($this->_selector_modal_body, function($modal_body){
                    $tags = $this->getApiTags();
                    $tag = $tags[array_rand($tags, 1)]['name'];
                    $first_char = substr($tag, 0, 1);
                    $second_char = substr($tag, 1, 2);

                    $modal_body->waitUntilMissing($this->_selector_modal_entry_field_tags_container_is_loading, HomePage::WAIT_SECONDS)
                        ->keys($this->_selector_modal_entry_field_tags, $first_char)
                        ->keys($this->_selector_modal_entry_field_tags, $second_char)
                        ->waitFor($this->_selector_modal_tag_autocomplete_options)
                        ->assertSee($tag)
                        ->click($this->_selector_modal_tag_autocomplete_options);
                });
            }

            if($has_attachments){
                $upload_file_path = \Storage::path($this->getRandomTestFileStoragePath());
                $this->assertFileExists($upload_file_path);
                $browser->with($this->_selector_modal_body, function($modal_body) use ($upload_file_path){
                    $modal_body
                        ->assertVisible($this->_selector_modal_entry_field_upload)
                        ->attach($this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path)
                        ->waitFor($this->_selector_modal_entry_dropzone_upload_thumbnail, HomePage::WAIT_SECONDS);
                })
                    ->assertNotification(HomePage::NOTIFICATION_INFO, sprintf($this->_label_notification_file_upload_success, basename($upload_file_path)));
            }

            $browser->with($this->_selector_modal_foot, function($modal_foot){
                $modal_foot->click($this->_selector_modal_entry_btn_save);
            })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, $this->_label_notification_new_entry_created)
                ->assertMissing($this->_selector_modal_entry)

                ->with($this->_selector_table.' .has-background-warning'.($has_attachments?".has-attachments":"").($has_tags?".has-tags":""), function($table_row) use ($memo_field){
                    $table_row->assertSee($memo_field);
                });
        });
    }

}