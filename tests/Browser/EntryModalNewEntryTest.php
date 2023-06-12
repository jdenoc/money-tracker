<?php

namespace Tests\Browser;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\AccountType;
use App\Traits\Tests\Dusk\EntryModal as DuskTraitEntryModal;
use App\Traits\Tests\Dusk\FileDragNDrop as DuskTraitFileDragNDrop;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\WaitTimes;
use App\Traits\Tests\WithTailwindColors;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;
use Throwable;

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
    use DuskTraitEntryModal;
    use DuskTraitFileDragNDrop;
    use DuskTraitLoading;
    use DuskTraitNotification;
    use DuskTraitNavbar;
    use DuskTraitTagsInput;
    use DuskTraitToggleButton;
    use HomePageSelectors;
    use WaitTimes;
    use WithTailwindColors;

    private $method_account = 'account';
    private $method_account_type = 'account-type';
    private $default_currency_character;

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $default_currency = CurrencyHelper::getCurrencyDefaults();
        $this->default_currency_character = CurrencyHelper::convertCurrencyHtmlToCharacter($default_currency->html);
    }

    public function setUp(): void {
        parent::setUp();
        $this->initEntryModalColours();
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 1/20
     */
    public function testEntryModalIsNotVisibleByDefault() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 2/20
     */
    public function testEntryModalIsVisibleWhenNavbarElementIsClicked() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->assertVisible($this->_selector_modal_entry);
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 3/20
     */
    public function testModalHeaderHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_head, function(Browser $entry_modal_head) {
                    $entry_modal_head
                        ->assertSee($this->_label_entry_new)
                        ->assertVisible($this->_selector_modal_btn_close);
                    $this->assertConfirmedButtonInactive($entry_modal_head);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 4/20
     */
    public function testCloseEntryModalWithXInModalHead() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_head, function(Browser $entry_modal_head) {
                    $entry_modal_head->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 5/20
     */
    public function testConfirmedButtonActivatesWhenClicked() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_head, function(Browser $entry_modal_head) {
                    $this->assertConfirmedButtonInactive($entry_modal_head);
                    $this->interactWithConfirmButton($entry_modal_head);
                    $this->assertConfirmedButtonActive($entry_modal_head);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 6/20
     */
    public function testModalBodyHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
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
                        ->assertInputValue($this->_selector_modal_entry_field_memo, "");

                    $this->assertToggleButtonState($entry_modal_body, $this->_selector_modal_entry_field_expense, $this->_label_expense_switch_expense, $this->_color_expense_switch_expense);

                    $entry_modal_body->assertSee('Tags:');
                    $this->assertDefaultStateOfTagsInput($entry_modal_body);

                    $this->assertDragNDropDefaultState($entry_modal_body, $this->_selector_modal_entry_field_upload);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 7/20
     */
    public function testModalFooterHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function(Browser $entry_modal_foot) {
                    $entry_modal_foot
                        ->assertMissing($this->_selector_modal_entry_btn_delete)   // delete button
                        ->assertMissing($this->_selector_modal_entry_btn_lock)     // lock/unlock button
                        ->assertVisible($this->_selector_modal_entry_btn_cancel)   // cancel button
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_entry_btn_save)     // save button
                        ->assertSee($this->_label_btn_save);

                    $this->assertElementBackgroundColor($entry_modal_foot, $this->_selector_modal_entry_btn_save, $this->tailwindColors->green(500));
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 8/20
     */
    public function testCloseEntryModalWithCancelButton() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function(Browser $entry_modal_foot) {
                    $entry_modal_foot->click($this->_selector_modal_entry_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 9/20
     */
    public function testCloseEntryModalWithHotkey() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->assertVisible($this->_selector_modal_entry)
                ->keys('', "{escape}")
                ->assertMissing($this->_selector_modal_entry);
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 10/20
     */
    public function testEntryValueConvertsIntoDecimalOfTwoPlaces() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_value, "F15sae.92fwfw")
                        ->click($this->_selector_modal_entry_field_date)    // processing doesn't occur until another element has been interacted with
                        ->assertInputValue($this->_selector_modal_entry_field_value, "15.92");
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 11/20
     */
    public function testSelectingAccountTypeDisplaysAccountTypeMetaData() {
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];
        $this->assertNotEmpty($account_type);

        $this->browse(function(Browser $browser) use ($account_type) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) use ($account_type) {
                    // currency icon in input#entry-value is "$"
                    $this->assertEntryValueCurrency($entry_modal_body, $this->default_currency_character);
                    // don't see account meta
                    $entry_modal_body
                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                    $this->waitUntilSelectLoadingIsMissing($entry_modal_body, $this->_selector_modal_entry_field_account_type);
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

    public function providerSelectingDisabledAccountTypeMetaDataIsGrey(): array {
        // [$account_type_method]
        return [
            [$this->method_account],        // test 12/20
            [$this->method_account_type]    // test 13/20
        ];
    }

    /**
     * @dataProvider providerSelectingDisabledAccountTypeMetaDataIsGrey
     * @param string $account_type_method
     *
     * @throws Throwable
     *
     * @group entry-modal-3
     * test (see provider)/20
     */
    public function testSelectingDisabledAccountTypeMetaDataIsGrey(string $account_type_method) {
        $account_types = AccountType::all();
        $disabled_account_type = [];
        if ($account_type_method == $this->method_account) {
            $disabled_account = Account::onlyTrashed()->get()->random();
            $disabled_account_type = $account_types->where('account_id', $disabled_account['id'])->random();
        } elseif ($account_type_method == $this->method_account_type) {
            $disabled_account_type = $account_types->where('disabled', true)->random();
        } else {
            $this->fail("Unknown account-type method provided");
        }

        $this->browse(function(Browser $browser) use ($disabled_account_type) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) use ($disabled_account_type) {
                    $this->waitUntilSelectLoadingIsMissing($entry_modal_body, $this->_selector_modal_entry_field_account_type);
                    $entry_modal_body
                        ->assertVisible($this->_selector_modal_entry_field_account_type)
                        ->select($this->_selector_modal_entry_field_account_type, $disabled_account_type['id'])
                        ->assertVisible($this->_selector_modal_entry_meta);

                    $this->assertElementTextColor($entry_modal_body, $this->_selector_modal_entry_meta, $this->tailwindColors->gray(400));

                    $entry_modal_body
                        ->select($this->_selector_modal_entry_field_account_type, '')
                        ->assertMissing($this->_selector_modal_entry_meta);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 14/20
     */
    public function testSelectingAccountTypeChangesCurrency() {
        // this test relies on a consistent database to test with
        // we can't use a dataProvider as the data is wiped by the time the test(s) are run
        $accounts = Account::all()->unique('currency');
        foreach ($accounts as $account) {
            // See storage/app/json/currency.json for list of supported currencies
            $currency_html = CurrencyHelper::getCurrencyHtmlFromCode($account['currency']);
            $currency_character = html_entity_decode($currency_html, ENT_HTML5);

            $account_type_id = AccountType::where('account_id', $account['id'])->pluck('id')->random();

            $this->browse(function(Browser $browser) use ($account_type_id, $currency_character) {
                $browser->visit(new HomePage());
                $this->waitForLoadingToStop($browser);
                $this->openNewEntryModal($browser);
                $browser
                    ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) use ($account_type_id, $currency_character) {
                        // currency icon in input#entry-value is "$" by default
                        $this->assertEntryValueCurrency($entry_modal_body, $this->default_currency_character);

                        $this->waitUntilSelectLoadingIsMissing($entry_modal_body, $this->_selector_modal_entry_field_account_type);
                        $entry_modal_body
                            ->assertVisible($this->_selector_modal_entry_field_account_type)
                            ->select($this->_selector_modal_entry_field_account_type, $account_type_id);

                        $this->assertEntryValueCurrency($entry_modal_body, $currency_character);

                        // revert account-type select field to default state
                        $entry_modal_body->select($this->_selector_modal_entry_field_account_type, '');
                        $this->assertEntryValueCurrency($entry_modal_body, $this->default_currency_character);
                    });
            });
        }
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 15/20
     */
    public function testClickingExpenseIncomeSwitch() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    $this->assertToggleButtonState($entry_modal_body, $this->_selector_modal_entry_field_expense, $this->_label_expense_switch_expense, $this->_color_expense_switch_expense);
                    $this->toggleToggleButton($entry_modal_body, $this->_selector_modal_entry_field_expense);
                    $this->assertToggleButtonState($entry_modal_body, $this->_selector_modal_entry_field_expense, $this->_label_expense_switch_income, $this->_color_expense_switch_income);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 16/20
     */
    public function testFillFieldsToEnabledSaveButton() {
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);

            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    // The date field should already be filled in. No need to fill it in again.
                    $entry_modal_body->assertInputValue($this->_selector_modal_entry_field_date, date("Y-m-d"));
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) {
                    $entry_modal_body->type($this->_selector_modal_entry_field_value, "9.99");
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) use ($account_type) {
                    $this->waitUntilSelectLoadingIsMissing($entry_modal_body, $this->_selector_modal_entry_field_account_type);
                    $entry_modal_body
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->assertSee($this->_label_account_type_meta_account_name)
                        ->assertSee($this->_label_account_type_meta_last_digits);
                })
                ->assertEntryModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_memo, "Test entry")
                        ->click($this->_selector_modal_entry_field_date);
                })
                ->assertEntryModalSaveButtonIsNotDisabled()

                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    // laravel dusk has an issue typing into input[type="date"] fields
                    // work-around for this is to use individual keystrokes
                    $backspace_count = strlen($entry_modal_body->inputValue($this->_selector_modal_entry_field_date));
                    for ($i=0; $i<$backspace_count; $i++) {
                        $entry_modal_body->keys($this->_selector_modal_entry_field_date, "{backspace}");
                    }
                })
                ->assertEntryModalSaveButtonIsDisabled();
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 17/20
     */
    public function testUploadAttachmentToNewEntry() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) {
                    $upload_file_path = $this->getFullPathOfRandomAttachmentFromTestStorage();
                    $this->uploadAttachmentUsingDragNDropAndSuccess($entry_modal_body, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path);
                    $this->removeUploadedAttachmentFromDragNDrop($entry_modal_body, $this->_selector_modal_entry_field_upload);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 18/20
     */
    public function testUploadAttachmentAndAttachmentIsNotPresentAfterClosingAndReopeningModal() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $modal_body) {
                    $upload_file_path = $this->getFullPathOfRandomAttachmentFromTestStorage();
                    $this->uploadAttachmentUsingDragNDropAndSuccess($modal_body, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path);
                })
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_cancel);
                })
                ->waitUntilMissing($this->_selector_modal_entry);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $modal_body) {
                    $this->assertDragNDropDefaultState($modal_body, $this->_selector_modal_entry_field_upload);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 19/20
     */
    public function testTagsInputAutoComplete() {
        // select tag at random and input the first few characters into the tags-input field
        $tags = $this->getApiTags();
        $tag = $tags[array_rand($tags, 1)]['name'];

        $this->browse(function(Browser $browser) use ($tag) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $entry_modal_body) use ($tag) {
                    $this->fillTagsInputUsingAutocomplete($entry_modal_body, $tag);
                    $this->assertTagInInput($entry_modal_body, $tag);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-3
     * test 20/20
     */
    public function testCreateConfirmedEntry() {
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type) {
            $memo_field = "Test entry - confirmed";
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_head, function(Browser $modal_head) {
                    $this->interactWithConfirmButton($modal_head);
                })
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field) {
                    $this->waitUntilSelectLoadingIsMissing($modal_body, $this->_selector_modal_entry_field_account_type);
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);
                })
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function($modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, $this->_label_notification_new_entry_created);
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_entry)
                ->with($this->_selector_table.' '.$this->_selector_table_confirmed_expense, function($table_row) use ($memo_field) {
                    $table_row->assertSee($memo_field);
                });
        });
    }

    public function providerCreateEntryWithMinimumRequirements(): array {
        return [
            'expense'=>[true],  // 1/20
            'income'=>[false]   // 2/20
        ];
    }

    /**
     * @dataProvider providerCreateEntryWithMinimumRequirements
     * @param bool $is_expense
     * @throws Throwable
     *
     * @group entry-modal-4
     * test (see provider)/20
     */
    public function testCreateEntryWithMinimumRequirements(bool $is_expense) {
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type, $is_expense) {
            $memo_field = "Test entry - save requirements - ".($is_expense ? 'expense' : 'income');
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $modal_body) use ($account_type, $memo_field, $is_expense) {
                    $this->waitUntilSelectLoadingIsMissing($modal_body, $this->_selector_modal_entry_field_account_type);
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);

                    if (!$is_expense) {
                        $this->toggleToggleButton($modal_body, $this->_selector_modal_entry_field_expense);
                    }
                })
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, $this->_label_notification_new_entry_created);
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);

            $table_row_selector = $this->_selector_table.' '.($is_expense ? $this->_selector_table_unconfirmed_expense : $this->_selector_table_unconfirmed_income);
            $browser
                ->assertMissing($this->_selector_modal_entry)
                ->within($table_row_selector, function(Browser $table_row) use ($memo_field) {
                    $table_row->assertSee($memo_field);
                });
        });
    }

    public function providerCreateGenericEntry(): array {
        return [
            // [$has_tags, $has_attachments]
            [false, false], // test 3/20
            [true, false],  // test 4/20
            [false, true],  // test 5/20
            [true, true]    // test 6/20
        ];
    }

    /**
     * @dataProvider providerCreateGenericEntry
     * @param bool $has_tags
     * @param bool $has_attachments
     *
     * @throws Throwable
     *
     * @group entry-modal-4
     * test (see provider)/20
     */
    public function testCreateGenericEntry(bool $has_tags, bool $has_attachments) {
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type, $has_tags, $has_attachments) {
            $memo_field = "Test entry - generic".($has_tags ? " w\ tags" : "").($has_attachments ? " \w attachments" : "");
            $upload_file_path = $has_attachments ? $this->getFullPathOfRandomAttachmentFromTestStorage() : '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry.' '.$this->_selector_modal_body, function(Browser $modal_body) use ($account_type, $memo_field, $has_tags, $has_attachments, $upload_file_path) {
                    $this->waitUntilSelectLoadingIsMissing($modal_body, $this->_selector_modal_entry_field_account_type);
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);

                    if ($has_tags) {
                        $tags = $this->getApiTags();
                        $tag = $tags[array_rand($tags, 1)]['name'];

                        $this->fillTagsInputUsingAutocomplete($modal_body, $tag);
                        $this->assertTagInInput($modal_body, $tag);
                    }

                    if ($has_attachments) {
                        $this->uploadAttachmentUsingDragNDropAndSuccess($modal_body, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path);
                    }
                });
            if ($has_attachments) {
                $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($upload_file_path)));
                $this->dismissNotification($browser);
            }

            $browser->within($this->_selector_modal_entry.' '.$this->_selector_modal_foot, function(Browser $modal_foot) {
                $modal_foot->click($this->_selector_modal_entry_btn_save);
            });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, $this->_label_notification_new_entry_created);
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_modal_entry)

                ->within($this->_selector_table.' .unconfirmed'.($has_attachments ? ".has-attachments" : "").($has_tags ? ".has-tags" : ""), function(Browser $table_row) use ($memo_field) {
                    $table_row->assertSee($memo_field);
                });
        });
    }

}
