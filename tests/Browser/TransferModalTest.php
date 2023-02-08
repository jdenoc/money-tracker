<?php

namespace Tests\Browser;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\AssertElementColor;
use App\Traits\Tests\Dusk\AccountOrAccountTypeSelector as DuskTraitAccountOrAccountTypeSelector;
use App\Traits\Tests\Dusk\BrowserDateUtil as DuskTraitBrowserDateUtil;
use App\Traits\Tests\Dusk\EntryModalSelectors as DuskTraitEntryModalSelectors;
use App\Traits\Tests\Dusk\FileDragNDrop as DuskTraitFileDragNDrop;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\WithTailwindColors;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * Class TransferModalTest
 *
 * @package Tests\Browser
 *
 * @group transfer-modal
 * @group modal
 * @group home
 */
class TransferModalTest extends DuskTestCase {
    use AssertElementColor;
    use DuskTraitAccountOrAccountTypeSelector;
    use DuskTraitBrowserDateUtil;
    use DuskTraitEntryModalSelectors;
    use DuskTraitFileDragNDrop;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use DuskTraitTagsInput;
    use EntryTransferKeys;
    use HomePageSelectors;
    use WithTailwindColors;
    use WithFaker;

    private static $METHOD_TO = 'to';
    private static $METHOD_FROM = 'from';
    private static $METHOD_ACCOUNT = 'account';
    private static $METHOD_ACCOUNT_TYPE = 'account-type';

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 1/20
     */
    public function testTransferModalNotVisibleByDefault() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 2/20
     */
    public function testOpenTransferModalFromNavbarElement() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser->assertVisible($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 3/20
     */
    public function testModalHeaderHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function(Browser $modal) {
                    $modal
                        ->assertSee("Transfer")
                        ->assertVisible($this->_selector_modal_btn_close);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 4/20
     */
    public function testModalBodyHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer.' '.$this->_selector_modal_body, function(Browser $modal) {
                    $modal
                        ->assertSee("Date:")
                        ->assertVisible($this->_selector_modal_transfer_field_date)
                        ->assertInputValue($this->_selector_modal_transfer_field_date, date("Y-m-d"))

                        ->assertSee("Value:")
                        ->assertVisible($this->_selector_modal_transfer_field_value)
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")

                        ->assertDontSee($this->_label_account_type_meta_last_digits)
                        ->assertDontSee($this->_label_account_type_meta_account_name)

                        ->assertSee("From:")
                        ->assertVisible($this->_selector_modal_transfer_field_from)
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->assertSelectHasOption($this->_selector_modal_transfer_field_from, "0")

                        ->assertSee("To:")
                        ->assertVisible($this->_selector_modal_transfer_field_to)
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)
                        ->assertSelectHasOption($this->_selector_modal_transfer_field_to, "0")

                        ->assertSee("Memo:")
                        ->assertVisible($this->_selector_modal_transfer_field_memo)
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")

                        ->assertSee("Tags:");
                    $this->assertDefaultStateOfTagsInput($modal);

                    $this->assertDragNDropDefaultState($modal, $this->_selector_modal_transfer_field_upload);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 5/20
     */
    public function testModalFooterHasCorrectElements() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_transfer_btn_cancel)
                        ->assertSee("Save")
                        ->assertVisible($this->_selector_modal_transfer_btn_save);
                })
                ->assertTransferModalSaveButtonIsDisabled();
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 6/20
     */
    public function testCloseTransferModalWithXButtonInHeader() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function(Browser $modal) {
                    $modal->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 7/20
     */
    public function testCloseTransferModalWithCancelButtonInFooter() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 8/20
     */
    public function testCloseTransferModalWithHotkey() {
        $this->markTestIncomplete("hotkey functionality requires further work");
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->keys('', "{control}", "{escape}") // ["{control}", "{escape}"] didn't work
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 9/20
     */
    public function testTransferValueConvertsIntoDecimalOfTwoPlaces() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal
                        ->type($this->_selector_modal_transfer_field_value, "F15sae.92fwf3w")
                        ->click($this->_selector_modal_transfer_field_date)
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "15.92");
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 10/20
     */
    public function testFillFieldsToEnabledSaveButton() {
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $this->faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    // The date field should already be filled in. No need to fill it in again.
                    $modal->assertInputValue($this->_selector_modal_transfer_field_date, date("Y-m-d"));
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal->type($this->_selector_modal_transfer_field_value, "123.45");
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_from);
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_to);
                    $modal
                        ->select($this->_selector_modal_transfer_field_to, $account_types[1]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal
                        ->type($this->_selector_modal_transfer_field_memo, "Test transfer")
                        ->click($this->_selector_modal_transfer_field_date);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // set "from" field to "[External account]"
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // set "to" field to "[External account]"
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $modal
                        ->select($this->_selector_modal_transfer_field_to, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // match the "to" field to the "from" field
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                // match the "to" field to the "from" field; they're both set to "[External account]"
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)

                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                })
                ->assertTransferModalSaveButtonIsDisabled();
        });
    }

    public function providerSelectingDisabledTransferAccountTypeMetaDataIsGrey(): array {
        // [$transfer_field, $account_type_method]
        return [
            [self::$METHOD_TO, self::$METHOD_ACCOUNT],          // test 11/20
            [self::$METHOD_TO, self::$METHOD_ACCOUNT_TYPE],     // test 12/20
            [self::$METHOD_FROM, self::$METHOD_ACCOUNT],        // test 13/20
            [self::$METHOD_FROM, self::$METHOD_ACCOUNT_TYPE],   // test 14/20
        ];
    }

    /**
     * @dataProvider providerSelectingDisabledTransferAccountTypeMetaDataIsGrey
     * @param string $transfer_field
     * @param string $account_type_method
     *
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test (see provider)/20
     */
    public function testSelectingDisabledTransferAccountTypeMetaDataIsGrey(string $transfer_field, string $account_type_method) {
        $account_types = AccountType::withTrashed()->all();
        $disabled_account_type = [];
        if ($account_type_method == self::$METHOD_ACCOUNT) {
            $disabled_account = Account::onlyTrashed()->get()->random();
            $disabled_account_type = $account_types->where('account_id', $disabled_account['id'])->random();
        } elseif ($account_type_method == self::$METHOD_ACCOUNT_TYPE) {
            $disabled_account_type = $account_types->where('active', false)->random();
        } else {
            $this->fail("Unknown account-type method provided");
        }

        $this->browse(function(Browser $browser) use ($disabled_account_type, $transfer_field) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($disabled_account_type, $transfer_field) {
                    $selector_field = '';
                    $selector_meta = '';
                    if ($transfer_field == self::$METHOD_TO) {
                        $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_to);
                        $selector_field = $this->_selector_modal_transfer_field_to;
                        $selector_meta = $this->_selector_modal_transfer_meta_to;
                    } elseif ($transfer_field == self::$METHOD_FROM) {
                        $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_from);
                        $selector_field = $this->_selector_modal_transfer_field_from;
                        $selector_meta = $this->_selector_modal_transfer_meta_from;
                    } else {
                        $this->fail("Unknown transfer field provided");
                    }

                    $modal
                        ->assertVisible($selector_field)
                        ->select($selector_field, $disabled_account_type['id'])
                        ->assertVisible($selector_meta);
                    $this->assertElementTextColor($modal, $selector_meta, $this->tailwindColors->gray(400));

                    $modal
                        ->select($selector_field, '')
                        ->assertMissing($selector_meta);
                });
        });
    }

    public function providerResetTransferModalFields(): array {
        return [
            // [$has_tags, $has_attachments]
            'standard fields'=>[false, false],                           // test 15/20
            'standard fields \w rawTagsData'=>[true, false],             // test 16/20
            'standard fields \w attachments'=>[false, true],             // test 17/20
            'standard fields \w rawTagsData & attachments'=>[true, true] // test 18/20
        ];
    }

    /**
     * @dataProvider providerResetTransferModalFields
     * @param $has_tags
     * @param $has_attachments
     *
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test (see provider)/20
     */
    public function testResetTransferModalFields($has_tags, $has_attachments) {
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $this->faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types, $has_tags, $has_attachments) {
            $upload_file_path = $has_attachments ? $this->getFullPathOfRandomAttachmentFromTestStorage() : '';
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types, $has_tags, $has_attachments, $upload_file_path) {
                    $modal
                        // make sure (almost) all the fields are empty first
                        ->assertInputValue($this->_selector_modal_transfer_field_date, date('Y-m-d'))
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")
                        ->assertInputValue(self::$SELECTOR_TAGS_INPUT_INPUT, "")
                        ->assertVisible($this->_selector_modal_transfer_field_upload)
                        ->assertMissing($this->_selector_modal_transfer_dropzone_upload_thumbnail)
                        // fill in fields
                        ->type($this->_selector_modal_transfer_field_value, "123.45")
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->select($this->_selector_modal_transfer_field_to, $account_types[1]['id'])
                        ->type($this->_selector_modal_transfer_field_memo, "Test transfer - reset");

                    if ($has_tags) {
                        // select tag at random and input the first character into the tags-input field
                        $tags = $this->getApiTags();
                        $tag = $this->faker->randomElement($tags);
                        $this->fillTagsInputUsingAutocomplete($modal, $tag['name']);
                    }

                    if ($has_attachments) {
                        $this->uploadAttachmentUsingDragNDropAndSuccess($modal, $this->_selector_modal_transfer_field_upload, $this->_selector_modal_transfer_dropzone_hidden_file_input, $upload_file_path);
                    }
                });

            if ($has_attachments) {
                $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($upload_file_path)));
                $this->dismissNotification($browser);
            }

            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer);

            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types) {
                    // make sure (almost) all the fields are empty after re-opening the transfer-modal
                    $modal
                        ->assertInputValue($this->_selector_modal_transfer_field_date, date("Y-m-d"))
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")
                        ->assertInputValue(self::$SELECTOR_TAGS_INPUT_INPUT, "")
                        ->assertVisible($this->_selector_modal_transfer_field_upload)
                        ->within($this->_selector_modal_transfer_field_upload, function(Browser $upload_field) {
                            $upload_field
                                ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL)
                                ->assertSee(self::$LABEL_FILE_DRAG_N_DROP);
                        });
                });
        });
    }

    public function providerSaveTransferEntry(): array {
        return [
            // [$is_to_account_external, $is_from_account_external, $has_tags, $has_attachments]
            'TO account is external'                                   => [true,  false, false, false],    // test 1/20
            'FROM account is external'                                 => [false, true,  false, false],    // test 2/20
            'neither account is external'                              => [false, false, false, false],    // test 3/20
            'TO account is external w\ rawTagsData'                    => [true,  false, true,  false],    // test 4/20
            'FROM account is external w\ rawTagsData'                  => [false, true,  true,  false],    // test 5/20
            'neither account is external w\ rawTagsData'               => [false, false, true,  false],    // test 6/20
            'TO account is external w\ attachments'                    => [true,  false, false, true],     // test 7/20
            'FROM account is external w\ attachments'                  => [false, true,  false, true],     // test 8/20
            'neither account is external w\ attachments'               => [false, false, false, true],     // test 9/20
            'TO account is external w\ rawTagsData & attachments'      => [true,  false, true,  true],     // test 10/20
            'FROM account is external w\ rawTagsData & attachments'    => [false, true,  true,  true],     // test 11/20
            'neither account is external w\ rawTagsData & attachments' => [false, false, true,  true],     // test 12/20
        ];
    }

    /**
     * @dataProvider providerSaveTransferEntry
     * @param bool $is_to_account_external
     * @param bool $is_from_account_external
     * @param bool $has_tags
     * @param bool $has_attachments
     *
     * @throws Throwable
     *
     * @group transfer-modal-2
     * test (see provider)/20
     */
    public function testSaveTransferEntry(bool $is_to_account_external, bool $is_from_account_external, bool $has_tags, bool $has_attachments) {
        $this->browse(function(Browser $browser) use ($is_to_account_external, $is_from_account_external, $has_tags, $has_attachments) {
            $all_account_types = $this->getApiAccountTypes();
            $account_types = $this->faker->randomElements($all_account_types, 2);
            if ($has_tags) {
                $all_tags = $this->getApiTags();
                $tag = $this->faker->randomElement($all_tags);
                $tag = $tag['name'];
            } else {
                $tag = '';
            }
            if ($has_attachments) {
                $attachment_path = $this->getFullPathOfRandomAttachmentFromTestStorage();
            } else {
                $attachment_path = '';
            }

            // get locale date string from browser
            $browser_locale_date = $this->getBrowserLocaleDate($browser);
            $browser_locale_date_for_typing = $this->processLocaleDateForTyping($browser_locale_date);

            // generate some test values
            $transfer_entry_data = [
                'date'=>$browser_locale_date_for_typing,
                'memo'=>"Test transfer - save".($has_tags ? " w/ tags" : '').($has_attachments ? " w/ attachments" : '').' - '.$this->faker->uuid(),
                'value'=>$this->faker->randomFloat(2, 0, 100),
                'from_account_type_id'=>($is_from_account_external ? self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[0]['id']),
                'to_account_type_id'=>($is_to_account_external ? self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[1]['id']),
                'tag'=>$tag,
                'attachment_path'=>$attachment_path,
            ];

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($transfer_entry_data, $has_tags, $has_attachments) {
                    // laravel dusk has an issue typing into input[type="date"] fields
                    // work-around for this is to use individual keystrokes
                    $backspace_count = strlen($modal->inputValue($this->_selector_modal_transfer_field_date));
                    for ($i=0; $i<$backspace_count; $i++) {
                        $modal->keys($this->_selector_modal_transfer_field_date, "{backspace}");
                    }

                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_from);
                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_to);

                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $transfer_entry_data['date'])
                        ->type($this->_selector_modal_transfer_field_value, $transfer_entry_data['value'])
                        ->select($this->_selector_modal_transfer_field_from, $transfer_entry_data['from_account_type_id'])
                        ->select($this->_selector_modal_transfer_field_to, $transfer_entry_data['to_account_type_id'])
                        ->type($this->_selector_modal_transfer_field_memo, $transfer_entry_data['memo']);

                    if ($has_tags) {
                        $this->fillTagsInputUsingAutocomplete($modal, $transfer_entry_data['tag']);
                    }

                    if ($has_attachments) {
                        $this->uploadAttachmentUsingDragNDropAndSuccess($modal, $this->_selector_modal_transfer_field_upload, $this->_selector_modal_transfer_dropzone_hidden_file_input, $transfer_entry_data['attachment_path']);
                    }
                });

            if ($has_attachments) {
                $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($transfer_entry_data['attachment_path'])));
                $this->dismissNotification($browser);
            }

            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal->click($this->_selector_modal_transfer_btn_save);
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, $this->_label_notification_transfer_saved);
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_modal_transfer);

            $entry_modal_date_input_value = date("Y-m-d", strtotime($browser_locale_date));
            if (!$is_from_account_external) {
                $this->assertTransferEntrySaved(
                    $browser,
                    $this->_selector_table.' '.$this->_selector_table_unconfirmed_expense.'.is-transfer',
                    $transfer_entry_data,
                    $entry_modal_date_input_value,
                    'from_account_type_id',
                    $this->_label_expense_switch_expense,
                    $has_tags,
                    $has_attachments
                );
            }
            if (!$is_to_account_external) {
                $this->assertTransferEntrySaved(
                    $browser,
                    $this->_selector_table.' '.$this->_selector_table_unconfirmed_income.'.is-transfer',
                    $transfer_entry_data,
                    $entry_modal_date_input_value,
                    'to_account_type_id',
                    $this->_label_expense_switch_income,
                    $has_tags,
                    $has_attachments
                );
            }
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-2
     * test 13/20
     */
    public function testOpeningMoreThanOneTransferEntryPairPerSession() {
        // GIVEN:
        $account_type_id1 = AccountType::where('active', false)->get()->random();
        $account_type_id2 = AccountType::where('active', false)->where('id', '!=', $account_type_id1->id)->get()->random();
        $default_entry_data = ['disabled'=>false, 'entry_date'=>date('Y-m-d'), 'expense'=>true, 'entry_value'=>$this->faker->randomFloat(2)];
        $entry_data_income = ['account_type_id'=>$account_type_id2->id, 'entry_date'=>date("Y-m-d", strtotime("-18 months")), 'expense'=>false];

        // transfer pair 1
        $e1_1 = Entry::factory()->create(array_merge(
            $default_entry_data,
            ['account_type_id'=>$account_type_id1->id, 'memo'=>$this->getName(false).'1']
        ));
        $e1_2 = Entry::factory()->create(array_merge(
            $default_entry_data,
            $entry_data_income,
            ['transfer_entry_id'=>$e1_1->id, 'memo'=>$this->getName(false).'1']
        ));
        $e1_1->transfer_entry_id = $e1_2->id;
        $e1_1->save();

        // transfer pair 2
        $e2_1 = Entry::factory()->create(array_merge(
            $default_entry_data,
            ['account_type_id'=>$account_type_id1->id, 'memo'=>$this->getName(false).'2']
        ));
        $e2_2 = Entry::factory()->create(array_merge(
            $default_entry_data,
            $entry_data_income,
            ['transfer_entry_id'=>$e2_1->id, 'memo'=>$this->getName(false).'2']
        ));
        $e2_1->transfer_entry_id = $e2_2->id;
        $e2_1->save();

        //  WHEN/THEN:
        $this->browse(function(Browser $browser) use ($e1_1, $e1_2, $e2_1, $e2_2) {
            $browser
                ->visit(new HomePage())
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $e1_1->id))
                ->within($this->_selector_modal_entry, function(Browser $modal) use ($e1_1, $e1_2) {
                    $modal_entry_id1 = $modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($modal_entry_id1);
                    $this->assertEquals($e1_1->id, $modal_entry_id1);

                    $modal->click($this->_selector_modal_entry_btn_transfer);
                    $this->waitForLoadingToStop($modal);

                    $modal_entry_id2 = $modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($modal_entry_id2);
                    $this->assertEquals($e1_2->id, $modal_entry_id2);

                    $modal->click($this->_selector_modal_entry_btn_cancel);
                })

                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $e2_1->id))
                ->within($this->_selector_modal_entry, function(Browser $modal) use ($e2_1, $e2_2) {
                    $modal_entry_id1 = $modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($modal_entry_id1);
                    $this->assertEquals($e2_1->id, $modal_entry_id1);

                    $modal->click($this->_selector_modal_entry_btn_transfer);
                    $this->waitForLoadingToStop($modal);

                    $modal_entry_id2 = $modal->value($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($modal_entry_id2);
                    $this->assertEquals($e2_2->id, $modal_entry_id2);

                    $modal->click($this->_selector_modal_entry_btn_cancel);
                });
        });
    }

    /**
     * @param Browser $browser
     * @param string $table_row_selector
     * @param array $transfer_entry_data
     * @param string $entry_modal_date_input_value
     * @param string $entry_switch_expense_label
     * @param string $account_type_key
     * @param bool $has_tags
     * @param bool $has_attachments
     */
    private function assertTransferEntrySaved(Browser $browser, string $table_row_selector, array $transfer_entry_data, string $entry_modal_date_input_value, string $account_type_key, string $entry_switch_expense_label, bool $has_tags, bool $has_attachments) {
        if ($has_tags) {
            $table_row_selector .= '.has-tags';
        }
        if ($has_attachments) {
            $table_row_selector .= '.has-attachments';
        }
        $browser
            ->within($table_row_selector, function(Browser $table_row) use ($transfer_entry_data, $entry_modal_date_input_value, $has_tags, $has_attachments) {
                $table_row
                    ->assertSeeIn($this->_selector_table_row_date, $entry_modal_date_input_value)
                    ->assertSeeIn($this->_selector_table_row_memo, $transfer_entry_data['memo'])
                    ->assertSeeIn($this->_selector_table_row_value, $transfer_entry_data['value'])
                    ->assertVisible($this->_selector_table_row_transfer_checkmark);
                if ($has_tags) {
                    $table_row->assertSeeIn($this->_selector_table_row_tags, $transfer_entry_data['tag']);
                }
                if ($has_attachments) {
                    $table_row->assertVisible($this->_selector_table_row_attachment_checkmark);
                }
            })
            ->openExistingEntryModal($table_row_selector)
            ->within($this->_selector_modal_entry, function(Browser $modal) use ($transfer_entry_data, $entry_modal_date_input_value, $entry_switch_expense_label, $account_type_key, $has_tags, $has_attachments) {
                $modal
                    ->assertInputValue($this->_selector_modal_entry_field_date, $entry_modal_date_input_value)
                    ->assertInputValue($this->_selector_modal_entry_field_value, $transfer_entry_data['value'])
                    ->assertSelected($this->_selector_modal_entry_field_account_type, $transfer_entry_data[$account_type_key])
                    ->assertSee($this->_label_account_type_meta_account_name)
                    ->assertSee($this->_label_account_type_meta_last_digits)
                    ->assertInputValue($this->_selector_modal_entry_field_memo, $transfer_entry_data['memo'])
                    ->assertSee($entry_switch_expense_label);

                if ($has_tags) {
                    $modal->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER);
                    $this->assertTagInInput($modal, $transfer_entry_data['tag']);
                }

                if ($has_attachments) {
                    $modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $modal->driver->findElements(WebDriverBy::className('existing-attachment'));
                    $this->assertGreaterThan(0, count($elements));
                }

                $modal->click($this->_selector_modal_entry_btn_cancel);
            })
            ->assertMissing($this->_selector_modal_entry);
    }

}
