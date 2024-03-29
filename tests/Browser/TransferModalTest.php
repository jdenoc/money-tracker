<?php

namespace Tests\Browser;

use App\Account;
use App\AccountType;
use App\Entry;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\Dusk\FileDragNDrop as DuskTraitFileDragNDrop;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
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

    use HomePageSelectors;
    use DuskTraitFileDragNDrop;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use DuskTraitTagsInput;
    use EntryTransferKeys;

    use WithFaker;

    private $method_to = 'to';
    private $method_from = 'from';
    private $method_account = 'account';
    private $method_account_type = 'account-type';

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 1/25
     */
    public function testTransferModalNotVisibleByDefault(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 2/25
     */
    public function testOpenTransferModalFromNavbarElement(){
        $this->browse(function(Browser $browser){
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
     * test 3/25
     */
    public function testModalHeaderHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function($modal){
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
     * test 4/25
     */
    public function testModalBodyHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_body, function(Browser $modal){
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

                    $modal
                        ->assertVisible($this->_selector_modal_transfer_field_upload) // drag-n-drop file upload field
                        ->with($this->_selector_modal_transfer_field_upload, function($file_upload){
                            $file_upload->assertSee(self::$LABEL_FILE_DRAG_N_DROP);
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 5/25
     */
    public function testModalFooterHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function($modal){
                    $modal
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_transfer_btn_cancel)
                        ->assertSee("Save")
                        ->assertVisible($this->_selector_modal_transfer_btn_save);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 6/25
     */
    public function testCloseTransferModalWithXButtonInHeader(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function($modal){
                    $modal->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 7/25
     */
    public function testCloseTransferModalWithCancelButtonInFooter(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function($modal){
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    /**
     * @throws Throwable
     *
     * @group transfer-modal-1
     * test 8/25
     */
    public function testCloseTransferModalWithHotkey(){
        $this->browse(function(Browser $browser){
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
     * test 9/25
     */
    public function testTransferValueConvertsIntoDecimalOfTwoPlaces(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer, function($modal){
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
     * test 10/25
     */
    public function testFillFieldsToEnabledSaveButton(){
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $this->faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer, function($modal){
                    // The date field should already be filled in. No need to fill it in again.
                    $modal->assertInputValue($this->_selector_modal_transfer_field_date, date("Y-m-d"));
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function($modal){
                    $modal->type($this->_selector_modal_transfer_field_value, "123.45");
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->waitUntilMissing($this->_selector_modal_transfer_field_from_is_loading, self::$WAIT_SECONDS)
                ->waitUntilMissing($this->_selector_modal_transfer_field_to_is_loading, self::$WAIT_SECONDS)

                ->with($this->_selector_modal_transfer, function(Browser $modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function(Browser $modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_to, $account_types[1]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function($modal){
                    $modal
                        ->type($this->_selector_modal_transfer_field_memo, "Test transfer")
                        ->click($this->_selector_modal_transfer_field_date);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // set "from" field to "[External account]"
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
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
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
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
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
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
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
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

    public function providerSelectingDisabledTransferAccountTypeMetaDataIsGrey(): array{
        // [$transfer_field, $account_type_method]
        return [
            [$this->method_to, $this->method_account],          // test 11/25
            [$this->method_to, $this->method_account_type],     // test 12/25
            [$this->method_from, $this->method_account],        // test 13/25
            [$this->method_from, $this->method_account_type],   // test 14/25
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
     * test (see provider)/25
     */
    public function testSelectingDisabledTransferAccountTypeMetaDataIsGrey(string $transfer_field, string $account_type_method){
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

        $this->browse(function(Browser $browser) use ($disabled_account_type, $transfer_field){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, self::$WAIT_SECONDS)
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($disabled_account_type, $transfer_field){
                    $selector_field = '';
                    $selector_meta = '';
                    if($transfer_field == $this->method_to){
                        $selector_field = $this->_selector_modal_transfer_field_to;
                        $selector_meta = $this->_selector_modal_transfer_meta_to;
                    } else if($transfer_field == $this->method_from){
                        $selector_field = $this->_selector_modal_transfer_field_from;
                        $selector_meta = $this->_selector_modal_transfer_meta_from;
                    } else {
                        $this->fail("Unknown transfer field provided");
                    }

                    $entry_modal_body
                        ->assertVisible($selector_field)
                        ->select($selector_field, $disabled_account_type['id'])
                        ->assertVisible($selector_meta);

                    $meta_text_color = $entry_modal_body->attribute($selector_meta, 'class');
                    $this->assertStringNotContainsString('has-text-info', $meta_text_color);
                    $this->assertStringContainsString('has-text-grey-light', $meta_text_color);

                    $entry_modal_body
                        ->select($selector_field, '')
                        ->assertMissing($selector_meta);
                });
        });
    }

    public function providerResetTransferModalFields():array{
        return [
            // [$has_tags, $has_attachments]
            'standard fields'=>[false, false],                           // test 15/25
            'standard fields \w rawTagsData'=>[true, false],             // test 16/25
            'standard fields \w attachments'=>[false, true],             // test 17/25
            'standard fields \w rawTagsData & attachments'=>[true, true] // test 18/25
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
     * test (see provider)/25
     */
    public function testResetTransferModalFields($has_tags, $has_attachments){
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $this->faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types, $has_tags, $has_attachments){
            $upload_file_path = $has_attachments ? Storage::path($this->getRandomTestFileStoragePath()) : '';
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->with($this->_selector_modal_transfer, function(Browser $modal) use ($account_types, $has_tags, $has_attachments, $upload_file_path){
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

                    if($has_tags){
                        // select tag at random and input the first character into the tags-input field
                        $tags = $this->getApiTags();
                        $tag = $this->faker->randomElement($tags);
                        $this->fillTagsInputUsingAutocomplete($modal, $tag['name']);
                    }

                    if($has_attachments){
                        $this->uploadAttachmentUsingDragNDropAndSuccess($modal, $this->_selector_modal_transfer_field_upload, $this->_selector_modal_transfer_dropzone_hidden_file_input, $upload_file_path);
                    }
                });

            if($has_attachments){
                $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($upload_file_path)));
                $this->dismissNotification($browser);
            }

            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal){
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer);

            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($account_types){
                    // make sure (almost) all the fields are empty after re-opening the transfer-modal
                    $modal
                        ->assertInputValue($this->_selector_modal_transfer_field_date, date("Y-m-d"))
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")
                        ->assertInputValue(self::$SELECTOR_TAGS_INPUT_INPUT, "")
                        ->assertVisible($this->_selector_modal_transfer_field_upload)
                        ->within($this->_selector_modal_transfer_field_upload, function(Browser $upload_field){
                            $upload_field
                                ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL)
                                ->assertSee(self::$LABEL_FILE_DRAG_N_DROP);
                        });
                });
        });
    }

    public function providerSaveTransferEntry(): array{
        return [
            // [$is_to_account_external, $is_from_account_external, $has_tags, $has_attachments]
            'TO account is external'                                   => [true,  false, false, false],    // test 1/25
            'FROM account is external'                                 => [false, true,  false, false],    // test 2/25
            'neither account is external'                              => [false, false, false, false],    // test 3/25
            'TO account is external w\ rawTagsData'                    => [true,  false, true,  false],    // test 4/25
            'FROM account is external w\ rawTagsData'                  => [false, true,  true,  false],    // test 5/25
            'neither account is external w\ rawTagsData'               => [false, false, true,  false],    // test 6/25
            'TO account is external w\ attachments'                    => [true,  false, false, true],     // test 7/25
            'FROM account is external w\ attachments'                  => [false, true,  false, true],     // test 8/25
            'neither account is external w\ attachments'               => [false, false, false, true],     // test 9/25
            'TO account is external w\ rawTagsData & attachments'      => [true,  false, true,  true],     // test 10/25
            'FROM account is external w\ rawTagsData & attachments'    => [false, true,  true,  true],     // test 11/25
            'neither account is external w\ rawTagsData & attachments' => [false, false, true,  true],     // test 12/25
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
     * test (see provider)/25
     */
    public function testSaveTransferEntry(bool $is_to_account_external, bool $is_from_account_external, bool $has_tags, bool $has_attachments){
        $this->browse(function(Browser $browser) use ($is_to_account_external, $is_from_account_external, $has_tags, $has_attachments){
            $all_account_types = $this->getApiAccountTypes();
            $account_types = $this->faker->randomElements($all_account_types, 2);
            $tag = '';
            if($has_tags){
                $all_tags = $this->getApiTags();
                $tag = $this->faker->randomElement($all_tags);
                $tag = $tag['name'];
            }

            // get locale date string from browser
            $browser_locale_date = $browser->getBrowserLocaleDate();
            $browser_locale_date_for_typing = $browser->processLocaleDateForTyping($browser_locale_date);

            // generate some test values
            $transfer_entry_data = [
                'memo'=>"Test transfer - save".($has_tags?" w/ tags":'').($has_attachments?" w/ attachments":'').' - '.$this->faker->uuid,
                'value'=>$this->faker->randomFloat(2, 0, 100),
                'from_account_type_id'=>($is_from_account_external ? self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[0]['id']),
                'to_account_type_id'=>($is_to_account_external ? self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[1]['id']),
                'tag'=>$tag,
                'attachment_path'=>Storage::path($this->getRandomTestFileStoragePath()),
            ];

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($transfer_entry_data, $browser_locale_date_for_typing, $has_tags, $has_attachments){
                    // laravel dusk has an issue typing into input[type="date"] fields
                    // work-around for this is to use individual keystrokes
                    $backspace_count = strlen($modal->inputValue($this->_selector_modal_transfer_field_date));
                    for($i=0; $i<$backspace_count; $i++){
                        $modal->keys($this->_selector_modal_transfer_field_date, "{backspace}");
                    }

                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $browser_locale_date_for_typing)
                        ->type($this->_selector_modal_transfer_field_value, $transfer_entry_data['value'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_from_is_loading)
                        ->select($this->_selector_modal_transfer_field_from, $transfer_entry_data['from_account_type_id'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_to_is_loading)
                        ->select($this->_selector_modal_transfer_field_to, $transfer_entry_data['to_account_type_id'])
                        ->type($this->_selector_modal_transfer_field_memo, $transfer_entry_data['memo']);

                    if($has_tags){
                        $this->fillTagsInputUsingAutocomplete($modal, $transfer_entry_data['tag']);
                    }

                    if($has_attachments){
                        $this->uploadAttachmentUsingDragNDropAndSuccess($modal, $this->_selector_modal_transfer_field_upload, $this->_selector_modal_transfer_dropzone_hidden_file_input, $transfer_entry_data['attachment_path']);
                    }
                });

            if($has_attachments){
                $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($transfer_entry_data['attachment_path'])));
                $this->dismissNotification($browser);
            }

            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal){
                    $modal->click($this->_selector_modal_transfer_btn_save);
                });

            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, $this->_label_notification_transfer_saved);
            $this->dismissNotification($browser);
            $browser->assertMissing($this->_selector_modal_transfer);

            $entry_modal_date_input_value = date("Y-m-d", strtotime($browser_locale_date));
            if(!$is_from_account_external){
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
            if(!$is_to_account_external){
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
     * test 13/25
     */
    public function testOpeningMoreThanOneTransferEntryPairPerSession(){
        // GIVEN:
        $account_type_id1 = AccountType::where('disabled', true)->get()->random();
        $account_type_id2 = AccountType::where('disabled', true)->whereNotIn('id', [$account_type_id1->id])->get()->random();
        $default_entry_data = ['disabled'=>false, 'entry_date'=>date('Y-m-d'), 'expense'=>true, 'entry_value'=>$this->faker->randomFloat(2)];
        $entry_data_income = ['account_type_id'=>$account_type_id2->id, 'entry_date'=>date("Y-m-d", strtotime("-18 months")), 'expense'=>false];

        // transfer pair 1
        $e1_1 = factory(Entry::class)->create(array_merge(
            $default_entry_data,
            ['account_type_id'=>$account_type_id1->id, 'memo'=>$this->getName(false).'1']
        ));
        $e1_2 = factory(Entry::class)->create(array_merge(
            $default_entry_data,
            $entry_data_income,
            ['transfer_entry_id'=>$e1_1->id, 'memo'=>$this->getName(false).'1']
        ));
        $e1_1->transfer_entry_id = $e1_2->id;
        $e1_1->save();

        // transfer pair 2
        $e2_1 = factory(Entry::class)->create(array_merge(
            $default_entry_data,
            ['account_type_id'=>$account_type_id1->id, 'memo'=>$this->getName(false).'2']
        ));
        $e2_2 = factory(Entry::class)->create(array_merge(
            $default_entry_data,
            $entry_data_income,
            ['transfer_entry_id'=>$e2_1->id, 'memo'=>$this->getName(false).'2']
        ));
        $e2_1->transfer_entry_id = $e2_2->id;
        $e2_1->save();

        //  WHEN/THEN:
        $this->browse(function (Browser $browser) use ($e1_1, $e1_2, $e2_1, $e2_2){
            $browser
                ->visit(new HomePage())
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $e1_1->id))
                ->with($this->_selector_modal_entry, function($modal) use ($e1_1, $e1_2){
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
                ->with($this->_selector_modal_entry, function($modal) use ($e2_1, $e2_2){
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
    private function assertTransferEntrySaved(Browser $browser, string $table_row_selector, array $transfer_entry_data, string $entry_modal_date_input_value, string $account_type_key, string $entry_switch_expense_label, bool $has_tags, bool $has_attachments){
        if($has_tags){
            $table_row_selector .= '.has-tags';
        }
        if($has_attachments){
            $table_row_selector .= '.has-attachments';
        }
        $browser
            ->with($table_row_selector, function(Browser $table_row) use ($transfer_entry_data, $entry_modal_date_input_value, $has_tags, $has_attachments){
                $table_row
                    ->assertSeeIn($this->_selector_table_row_date, $entry_modal_date_input_value)
                    ->assertSeeIn($this->_selector_table_row_memo, $transfer_entry_data['memo'])
                    ->assertSeeIn($this->_selector_table_row_value, $transfer_entry_data['value'])
                    ->assertVisible($this->_selector_table_row_transfer_checkbox.' '.$this->_selector_table_is_checked_checkbox);
                if($has_tags){
                    $table_row->assertSeeIn($this->_selector_table_row_tags, $transfer_entry_data['tag']);
                }
                if($has_attachments){
                    $table_row->assertVisible($this->_selector_table_row_attachment_checkbox.' '.$this->_selector_table_is_checked_checkbox);
                }
            })
            ->openExistingEntryModal($table_row_selector)
            ->with($this->_selector_modal_entry, function(Browser $modal) use ($transfer_entry_data, $entry_modal_date_input_value, $entry_switch_expense_label, $account_type_key, $has_tags, $has_attachments){
                $modal
                    ->assertInputValue($this->_selector_modal_entry_field_date, $entry_modal_date_input_value)
                    ->assertInputValue($this->_selector_modal_entry_field_value, $transfer_entry_data['value'])
                    ->assertSelected($this->_selector_modal_entry_field_account_type, $transfer_entry_data[$account_type_key])
                    ->assertSee($this->_label_account_type_meta_account_name)
                    ->assertSee($this->_label_account_type_meta_last_digits)
                    ->assertInputValue($this->_selector_modal_entry_field_memo, $transfer_entry_data['memo'])
                    ->assertSee($entry_switch_expense_label);

                if($has_tags){
                    $modal->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER);
                    $this->assertTagInInput($modal, $transfer_entry_data['tag']);
                }

                if($has_attachments){
                    $modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $modal->driver->findElements(WebDriverBy::className('existing-attachment'));
                    $this->assertGreaterThan(0, count($elements));
                }

                $modal->click($this->_selector_modal_entry_btn_cancel);
            })
            ->assertMissing($this->_selector_modal_entry);
    }

}