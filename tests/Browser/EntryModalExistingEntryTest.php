<?php

namespace Tests\Browser;

use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Tag;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\Dusk\FileDragNDrop as DuskTraitFileDragNDrop;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\WaitTimes;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use LengthException;
use Storage;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Tests\Traits\HomePageSelectors;
use Throwable;

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

    use DuskTraitFileDragNDrop;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use DuskTraitTagsInput;
    use DuskTraitToggleButton;
    use EntryTransferKeys;
    use HomePageSelectors;
    use WaitTimes;
    use WithFaker;

    const INI_POSTMAXSIZE = 'post_max_size';
    const INI_UPLOADMAXFILESIZE = 'upload_max_filesize';

    private static $HTACCESS_FILEPATH = 'public/.htaccess';
    private static $BKUP_EXT = '.bkup';

    private static $TEST_NAME_OVERRIDE_HTACCESS = 'testAttemptToAddAnAttachmentTooLargeToAnExistingEntry';

    private $_class_lock = "fa-lock";
    private $_class_unlock = "fa-unlock-alt";
    private $_class_disabled = "disabled";
    private $_class_white_text = "has-text-white";
    private $_class_light_grey_text = "has-text-grey-light";
    private $_class_has_attachments = "has-attachments";
    private $_class_is_transfer = "is-transfer";
    private $_class_has_tags = "has-tags";
    private $_class_existing_attachment = "existing-attachment";

    private $_cached_entries_collection = [];

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->initColors();
    }

    public function setUp(): void{
        parent::setUp();
        $this->_cached_entries_collection = [];
        if($this->getName(false) === self::$TEST_NAME_OVERRIDE_HTACCESS){
            $this->addRulesToHtaccessToDisableDisplayErrors();
        }
    }

    protected function tearDown(): void{
        if($this->getName(false) === self::$TEST_NAME_OVERRIDE_HTACCESS){
            $this->revertHtaccessToOriginalState();
            // remove any files that any tests may have created
            Storage::delete(
                // need to remove the filepath prefix before we can delete the file from storage
                str_replace(Storage::path(''), '',$this->getTestDummyFilename())
            );
        }
        $this->assertFileNotExists($this->getTestDummyFilename());
        parent::tearDown();
    }

    public function providerUnconfirmedEntry(): array{
        return [
            // test 1/25
            "Expense"=>[$this->_selector_table_unconfirmed_expense, $this->_label_expense_switch_expense, $this->_color_expense_switch_expense],
            // test 2/25
            "Income"=>[$this->_selector_table_unconfirmed_income, $this->_label_expense_switch_income, $this->_color_expense_switch_income],
        ];
    }

    /**
     * @dataProvider providerUnconfirmedEntry
     * @param string $data_entry_selector
     * @param string $data_expense_switch_label
     * @param string $expense_switch_colour
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testClickingOnEntryTableEditButtonOfUnconfirmedEntry(string $data_entry_selector, string $data_expense_switch_label, string $expense_switch_colour){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_expense_switch_label, $expense_switch_colour){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($data_expense_switch_label, $expense_switch_colour){
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                            $entry_confirm_class = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, 'class');
                            $this->assertStringContainsString($this->_class_light_grey_text, $entry_confirm_class);
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data, $data_expense_switch_label, $expense_switch_colour){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo'])
                                ->assertSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_account_name)
                                ->assertSeeIn($this->_selector_modal_entry_meta, $this->_label_account_type_meta_last_digits);
                            $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $data_expense_switch_label, $expense_switch_colour);
                        })

                        ->within($this->_selector_modal_foot, function(Browser $modal_foot){
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

    public function providerConfirmedEntry(): array{
        return [
            // test 3/25
            "Expense"=>[$this->_selector_table_confirmed_expense, $this->_label_expense_switch_expense, $this->_color_expense_switch_expense],
            // test 4/25
            "Income"=>[$this->_selector_table_confirmed_income, $this->_label_expense_switch_income, $this->_color_expense_switch_income],
        ];
    }

    /**
     * @dataProvider providerConfirmedEntry
     * @param string $data_entry_selector
     * @param string $data_expense_switch_label
     * @param string $expense_switch_color
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testClickingOnEntryTableEditButtonOfConfirmedEntry(string $data_entry_selector, string $data_expense_switch_label, string $expense_switch_color){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_expense_switch_label, $expense_switch_color){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($data_expense_switch_label, $expense_switch_color){
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);

                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head){
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);

                            $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                            $this->assertStringContainsString($this->_class_white_text, $classes);
                            $this->assertStringNotContainsString($this->_class_light_grey_text, $classes);
                            $this->assertEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_confirmed, "disabled"));
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data, $data_expense_switch_label, $expense_switch_color){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertSee($this->_label_account_type_meta_account_name)
                                ->assertSee($this->_label_account_type_meta_last_digits)
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo']);
                            $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $data_expense_switch_label, $expense_switch_color);
                            $modal_body
                                ->assertMissing($this->_selector_modal_entry_field_upload)
                                ->assertDontSee(self::$LABEL_FILE_DRAG_N_DROP);

                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, "disabled"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, "readonly"));

                            $classes = $modal_body->attribute($this->_selector_modal_entry_field_expense, "class");
                            $this->assertStringContainsString($this->_class_disabled, $classes);
                        })

                        ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                            $modal_foot
                                ->assertVisible($this->_selector_modal_entry_btn_delete)
                                ->assertSee($this->_label_btn_delete)
                                ->assertVisible($this->_selector_modal_entry_btn_lock)
                                ->assertVisible($this->_selector_modal_entry_btn_cancel)
                                ->assertSee($this->_label_btn_cancel)
                                ->assertMissing($this->_selector_modal_entry_btn_save)
                                ->assertDontSee($this->_label_btn_save);

                            $classes = $modal_foot->attribute($this->_selector_modal_entry_btn_lock_icon, 'class');
                            $this->assertStringContainsString($this->_class_unlock, $classes);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 5/25
     */
    public function testClickingOnEntryTableEditButtonOfConfirmedEntryThenUnlock(){
        $this->browse(function(Browser $browser){
            $confirmed_entry_selector = $this->randomConfirmedEntrySelector(true);
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($confirmed_entry_selector)
                ->click($this->_selector_modal_entry_btn_lock)
                ->within($this->_selector_modal_head, function(Browser $modal_head){
                    $modal_head
                        ->assertDontSee($this->_label_entry_new)
                        ->assertSee($this->_label_entry_not_new)
                        ->assertSee($this->_label_btn_confirmed);

                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    $this->assertStringContainsString($this->_class_white_text, $classes, $this->_selector_modal_entry_btn_confirmed_label." is missing class:".$this->_class_white_text);
                    $this->assertStringNotContainsString($this->_class_light_grey_text, $classes, $this->_selector_modal_entry_btn_confirmed_label." has missing class:".$this->_class_light_grey_text);

                    $this->assertNotEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_confirmed, "disabled"));
                })

                ->within($this->_selector_modal_body, function(Browser $modal_body){
                    $modal_body
                        ->assertVisible($this->_selector_modal_entry_field_upload)
                        ->assertSee(self::$LABEL_FILE_DRAG_N_DROP);

                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, 'disabled'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, 'readonly'));

                    $classes = $modal_body->attribute($this->_selector_modal_entry_field_expense, "class");
                    $this->assertStringNotContainsString($this->_class_disabled, $classes);
                })

                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot
                        ->assertVisible($this->_selector_modal_entry_btn_delete)
                        ->assertSee($this->_label_btn_delete)
                        ->assertVisible($this->_selector_modal_entry_btn_lock)
                        ->assertVisible($this->_selector_modal_entry_btn_cancel)
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_entry_btn_save)
                        ->assertSee($this->_label_btn_save);

                    $classes = $modal_foot->attribute($this->_selector_modal_entry_btn_lock_icon, "class");
                    $this->assertStringContainsString($this->_class_lock, $classes);
                    $this->assertStringNotContainsString($this->_class_unlock, $classes);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 6/25
     */
    public function testClickingOnEntryTableEditButtonOfEntryWithAttachments(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal){
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertGreaterThan(0, count($elements));
                });
        });
    }

    public function providerEntryWithTags(): array{
        // [$data_entry_selector, $data_is_tags_input_visible]
        return [
            // test 7/25
            "Confirmed"=>[$this->randomConfirmedEntrySelector().'.'.$this->_class_has_tags, false],
            // test 8/25
            "Unconfirmed"=>[$this->randomUnconfirmedEntrySelector().'.'.$this->_class_has_tags, true],
        ];
    }

    /**
     * @dataProvider providerEntryWithTags
     * @param string $data_entry_selector
     * @param bool $data_is_tags_input_visible
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testClickingOnEntryTableEditButtonOfEntryWithTags(string $data_entry_selector, bool $data_is_tags_input_visible){
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_is_tags_input_visible){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($data_is_tags_input_visible){
                    $entry_id = $entry_modal->value($this->_selector_modal_entry_field_entry_id);
                    $entry_data = Entry::findOrFail($entry_id);
                    $this->assertTrue($entry_data->has_tags());
                    $entry_tags = $entry_data->tags->pluck('name');

                    if($data_is_tags_input_visible){
                        $entry_modal->assertVisible(self::$SELECTOR_TAGS_INPUT_INPUT);
                        foreach($entry_tags as $entry_tag){
                            $this->assertTagInInput($entry_modal, $entry_tag);
                        }
                    } else {
                        $entry_modal
                            ->assertVisible($this->_selector_tags)
                            ->assertVisible($this->_selector_tags_tag);
                        foreach($entry_tags as $entry_tag){
                            $entry_modal->assertSeeIn($this->_selector_tags, $entry_tag);
                        }
                    }
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 9/25
     */
    public function testOpenAttachment(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            $attachment_name = '';
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_name){
                    $entry_modal
                        ->assertVisible($this->_selector_modal_entry_existing_attachments)
                        ->within($this->_selector_modal_entry_existing_attachments.' '.$this->_selector_modal_entry_existing_attachments_first_attachment, function(Browser $existing_attachment) use (&$attachment_name){
                            $attachment_name = $existing_attachment->text($this->_selector_modal_entry_existing_attachments_attachment_name);
                            $this->assertNotEmpty($attachment_name);
                            $existing_attachment
                                ->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_view)
                                ->click($this->_selector_modal_entry_existing_attachments_attachment_btn_view);
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

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 10/25
     */
    public function testDeleteAttachmentFromExistingEntry(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector(['has_attachments'=>true]).'.'.$this->_class_has_attachments;
            // initialising this variable here, then pass it as a reference so that we can update its value.
            $attachment_count = 0;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_count){
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $attachment_count = count($attachments);

                    $entry_modal->with($this->_selector_modal_entry_existing_attachments, function($existing_attachment){
                        $attachment_name = trim($existing_attachment->text('.'.$this->_class_existing_attachment));
                        $existing_attachment
                            ->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->click($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->assertDialogOpened("Are you sure you want to delete attachment: ".$attachment_name)
                            ->acceptDialog();
                    });
                });
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "Attachment has been deleted");
            $browser
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_count){
                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className($this->_class_existing_attachment));
                    $this->assertCount($attachment_count-1, $attachments, "Attachment was NOT removed from UI");
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 11/25
     */
    public function testUpdateExistingEntryDate(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, &$new_value){
                    $old_value = $modal_body->inputValue($this->_selector_modal_entry_field_date);
                    // just in case the old and new values match
                    $day_diff = -10;
                    do{
                        $new_value = date("Y-m-d", strtotime(sprintf("%d days", $day_diff)));
                        $day_diff--;
                    } while ($new_value === $old_value);

                    // clear input[type="date"]
                    for($i=0; $i<strlen($old_value); $i++){
                        $modal_body->keys($this->_selector_modal_entry_field_date, "{backspace}");
                    }

                    $browser_date = $modal_body->getDateFromLocale($modal_body->getBrowserLocale(), $new_value);
                    $new_value_to_type = $modal_body->processLocaleDateForTyping($browser_date);
                    $modal_body->type($this->_selector_modal_entry_field_date, $new_value_to_type);
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->scrollToElement($entry_selector)
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_date));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_date));
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 12/25
     */
    public function testUpdateExistingEntryAccountType(){
        $account_types = $this->getApiAccountTypes();
        $this->assertGreaterThan(1, count($account_types), "Account-types available are not suffient for running this test");
        $this->browse(function(Browser $browser) use ($account_types){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = "";

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, &$new_value, $account_types){
                    $old_value = $modal_body->value($this->_selector_modal_entry_field_account_type);
                    do{
                        $account_type = $this->faker->randomElement($account_types);
                        $new_value = $account_type['id'];
                    }while($old_value == $new_value);
                    $modal_body->select($this->_selector_modal_entry_field_account_type, $new_value);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                });
        });
    }

    public function providerUpdateEntry(): array{
        return [
            'entry_value'=>[$this->_selector_modal_entry_field_value, 0.01],                                    // test 13/25
            'memo'=>[$this->_selector_modal_entry_field_memo, "hfrsighesiugbeusigbweuisgbeisugsebuibseiugbg"],  // test 14/25
        ];
    }

    /**
     * @dataProvider providerUpdateEntry
     * @param string $field_selector
     * @param $new_value
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testUpdateExistingEntryValue(string $field_selector, $new_value){
        $this->browse(function(Browser $browser) use ($field_selector, $new_value){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($field_selector, &$old_value, $new_value){
                    $old_value = $modal_body->inputValue($field_selector);
                    $modal_body->clear($field_selector);
                    $modal_body->type($field_selector, $new_value);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($field_selector, &$old_value, $new_value){
                    $this->assertNotEquals($old_value, $modal_body->inputValue($field_selector));
                    $this->assertEquals($new_value, $modal_body->inputValue($field_selector));
                });
        });
    }

    public function providerOpenExistingEntryInModalThenChangeConfirmSwitch(): array{
        return [
            'unconfirmed->confirmed'=>[false],  // test 15/25
            'confirmed->unconfirmed'=>[true]    // test 16/25
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeConfirmSwitch
     * @param bool $selector_bool
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testOpenExistingEntryInModalThenChangeConfirmSwitch(bool $selector_bool){
        $entry_selector = $this->randomEntrySelector(['confirm'=>$selector_bool]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?'is-confirmed':'has-background-warning'))
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) use ($selector_bool){
                    if($selector_bool){
                        $modal_foot->click($this->_selector_modal_entry_btn_lock);
                    }
                })
                ->within($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool){
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    if($selector_bool){
                        $this->assertStringContainsString($this->_class_white_text, $classes);
                        $this->assertStringNotContainsString($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertStringContainsString($this->_class_light_grey_text, $classes);
                        $this->assertStringNotContainsString($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    }

                    $modal_head->click($this->_selector_modal_entry_btn_confirmed_label);
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");

                    if($selector_bool){
                        $this->assertStringContainsString($this->_class_light_grey_text, $classes);
                        $this->assertStringNotContainsString($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertStringContainsString($this->_class_white_text, $classes);
                        $this->assertStringNotContainsString($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    }
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?'has-background-warning':'is-confirmed'))
                ->with($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool){
                    $classes = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, "class");
                    if($selector_bool){
                        $this->assertStringContainsString($this->_class_light_grey_text, $classes);
                        $this->assertStringNotContainsString($this->_class_white_text, $classes);
                        $modal_head->assertNotChecked($this->_selector_modal_entry_btn_confirmed);
                    } else {
                        $this->assertStringContainsString($this->_class_white_text, $classes);
                        $this->assertStringNotContainsString($this->_class_light_grey_text, $classes);
                        $modal_head->assertChecked($this->_selector_modal_entry_btn_confirmed);
                    }
                });
        });
    }

    public function providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch(): array{
        return [
            'expense->income'=>[true],  // test 17/25
            'income->expense'=>[false], // test 18/25
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch
     * @param bool $selector_bool
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/25
     */
    public function testOpenExistingEntryInModalThenChangeExpenseIncomeSwitch(bool $selector_bool){
        $entry_selector = $this->randomEntrySelector(['expense'=>$selector_bool, 'confirm'=>false]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool ? $this->_class_is_expense:$this->_class_is_income))
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool){
                    $toggle_label = $selector_bool ? $this->_label_expense_switch_expense:$this->_label_expense_switch_income;
                    $toggle_colour = $selector_bool ? $this->_color_expense_switch_expense:$this->_color_expense_switch_income;
                    $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $toggle_label, $toggle_colour);
                    $this->toggleToggleButton($modal_body, $this->_selector_modal_entry_field_expense);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool?$this->_class_is_income:$this->_class_is_expense))
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool){
                    $toggle_label = $selector_bool ? $this->_label_expense_switch_income:$this->_label_expense_switch_expense;
                    $toggle_colour = $selector_bool ? $this->_color_expense_switch_income:$this->_color_expense_switch_expense;
                    $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $toggle_label, $toggle_colour);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 19/25
     */
    public function testExistingTransferEntryHasEntryButton(){
        $this->browse(function(Browser $browser){
            $invalid_entry_ids = [];
            do{
                $entry_selector = $this->randomEntrySelector(['is_transfer'=>true]);
                $entry_id = $this->getEntryIdFromSelector($entry_selector);
                if(in_array($entry_id, $invalid_entry_ids)){
                    // already processed this ID, continue to the next iteration
                    continue;
                }
                $invalid_entry_ids[] = $entry_id;
                $entry_data = $this->getApiEntry($entry_id);
            }while($entry_data['transfer_entry_id'] === self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID);
            unset($invalid_entry_ids);
            $transfer_entry_data = $this->getApiEntry($entry_data['transfer_entry_id']);
            $this->assertEquals($entry_id, $entry_data['id']);
            $this->assertEquals($entry_data['transfer_entry_id'], $transfer_entry_data['id']);
            $this->assertEquals($transfer_entry_data['transfer_entry_id'], $entry_data['id']);
            $entry_selector .= '.'.$this->_class_is_transfer;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id){
                    $entry_modal->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id){
                        $modal_entry_id = $modal_head->inputValue($this->_selector_modal_entry_field_entry_id);
                        $this->assertNotEmpty($modal_entry_id);
                        $this->assertEquals($entry_id, $modal_entry_id);

                        $modal_head->assertVisible($this->_selector_modal_entry_btn_transfer);
                        $this->assertNotEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_transfer, "disabled"));
                        $modal_head->click($this->_selector_modal_entry_btn_transfer);
                    });
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertVisible($this->_selector_modal_entry)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($transfer_entry_data){
                    $entry_modal
                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($transfer_entry_data){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $transfer_entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $transfer_entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $transfer_entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $transfer_entry_data['memo']);
                            $expense_switch_label = $transfer_entry_data['expense'] ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;
                            $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $expense_switch_label);
                        })
                        ->within($this->_selector_modal_head, function(Browser $modal_head) use ($transfer_entry_data){
                            $modal_entry_id = $modal_head->inputValue($this->_selector_modal_entry_field_entry_id);
                            $this->assertNotEmpty($modal_entry_id);
                            $this->assertEquals($transfer_entry_data['id'], $modal_entry_id);

                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertVisible($this->_selector_modal_entry_btn_transfer)
                                ->click($this->_selector_modal_entry_btn_transfer);
                        });
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertVisible($this->_selector_modal_entry)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_data){
                    $entry_modal
                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data){
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, $entry_data['entry_date'])
                                ->assertInputValue($this->_selector_modal_entry_field_value, $entry_data['entry_value'])
                                ->assertSelected($this->_selector_modal_entry_field_account_type, $entry_data['account_type_id'])
                                ->assertInputValue($this->_selector_modal_entry_field_memo, $entry_data['memo']);
                            $expense_switch_label = $entry_data['expense'] ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;
                            $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $expense_switch_label);
                        })
                        ->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_data){
                            $modal_entry_id = $modal_head->inputValue($this->_selector_modal_entry_field_entry_id);
                            $this->assertNotEmpty($modal_entry_id);
                            $this->assertEquals($entry_data['id'], $modal_entry_id);

                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertVisible($this->_selector_modal_entry_btn_transfer);
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 20/25
     */
    public function testExistingExternalTransferEntryHasButtonButIsDisabled(){
        $this->browse(function(Browser $browser){
            $invalid_entry_id = [];
            do{
                $entry_selector = $this->randomEntrySelector(['is_transfer'=>true]);
                $entry_id = $this->getEntryIdFromSelector($entry_selector);
                if(in_array($entry_id, $invalid_entry_id)){
                    // entry ID has already been processed (unsuccessfully), continue to the next iteration
                    continue;
                }
                $invalid_entry_id[] = $entry_id;
                $entry_data = $this->getApiEntry($entry_id);
            } while($entry_data['transfer_entry_id'] !== self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID);
            unset($invalid_entry_id);
            $this->assertEquals($entry_id, $entry_data['id']);
            $entry_selector .= '.'.$this->_class_is_transfer;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id){
                    $entry_modal->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id){
                        $modal_entry_id = $modal_head->value($this->_selector_modal_entry_field_entry_id);
                        $this->assertNotEmpty($modal_entry_id);
                        $this->assertEquals($entry_id, $modal_entry_id);

                        $modal_head->assertVisible($this->_selector_modal_entry_btn_transfer);
                        $this->assertEquals("true", $modal_head->attribute($this->_selector_modal_entry_btn_transfer, "disabled"));
                    });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 21/25
     */
    public function testDeleteTagsFromExistingEntry(){
        // catch/create potentially missed database entries
        $account_type_id = AccountType::all()->random()->pluck('id')->first();
        $tag_ids = Tag::all()->pluck('id')->toArray();
        $entry = factory(Entry::class)->create(['entry_date'=>date('Y-m-d'),'expense'=>true, 'confirm'=>false, 'account_type_id'=>$account_type_id]);
        $entry->tags()->syncWithoutDetaching($this->faker->randomElements($tag_ids, 2));
        $entry = factory(Entry::class)->create(['entry_date'=>date('Y-m-d'),'expense'=>false, 'confirm'=>false, 'account_type_id'=>$account_type_id]);
        $entry->tags()->syncWithoutDetaching($this->faker->randomElements($tag_ids, 2));
        unset($entry, $tag_ids, $account_type_id);

        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomUnconfirmedEntrySelector(false);
            $entry_id = null;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.$this->_class_has_tags)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector, &$entry_id){
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $entry = Entry::findOrFail($entry_id);
                    foreach($entry->tags->pluck('name')->unique()->values() as $tag){
                        $this->assertTagInInput($entry_modal, $tag);
                        $entry_modal->click(self::$SELECTOR_TAGS_INPUT_TAG.' .tags-input-remove');
                    }
                    $entry_modal->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);

            $browser
                ->assertMissing(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id).'.'.$this->_class_has_tags)
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector){
                    $this->assertDefaultStateOfTagsInput($entry_modal);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 22/25
     */
    public function testUpdateTagsInExistingEntry(){
        // make sure there is at least one tag that doesn't belong to an entry
        // to doubly make sure there is no overlap, name the tag after the test
        // which is outside the typical tag name assignment
        factory(Tag::class)->create(['name'=>$this->getName(false)]);
        $tags_from_api = collect($this->getApiTags());

        $this->browse(function(Browser $browser) use ($tags_from_api){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $entry_id = $this->getEntryIdFromSelector($entry_selector);
            $new_tag = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id, $tags_from_api, &$new_tag){
                    $entry = Entry::findOrFail($entry_id);

                    $existing_entry_tags = $entry->tags->pluck('name')->all();
                    do{
                        $new_tag = $tags_from_api->pluck('name')->random();
                    }while(in_array($new_tag , $existing_entry_tags));
                    $this->fillTagsInputUsingAutocomplete($entry_modal, $new_tag);
                    $this->assertTagInInput($entry_modal, $new_tag);
                    $entry_modal->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);

            $browser
                ->assertVisible(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id).'.'.$this->_class_has_tags)
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector, $new_tag){
                    $this->assertTagInInput($entry_modal, $new_tag);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 23/25
     */
    public function testUploadAttachmentToExistingEntryWithoutSaving(){
        $this->browse(function(Browser $browser){
            $upload_file_path = Storage::path($this->getRandomTestFileStoragePath());
            $entry_selector = $this->randomEntrySelector(['confirm'=>false]);

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) use ($upload_file_path){
                    $this->uploadAttachmentUsingDragNDropAndSuccess($entry_modal_body, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path);
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS, basename($upload_file_path)));
            $this->dismissNotification($browser);
            // remove upload
            $browser->within($this->_selector_modal_body, function(Browser $entry_modal_body) {
                $this->removeUploadedAttachmentFromDragNDrop($entry_modal_body, $this->_selector_modal_entry_field_upload);
            });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 24/25
     */
    public function testOpenExistingEntryInModalThenCloseModalAndOpenNewEntryModal(){
        $this->browse(function(Browser $browser){
            $entry_selector = $this->randomEntrySelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            // open existing entry in modal and confirm fields are filled
            $browser->openExistingEntryModal($entry_selector)
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
                ->waitUntilMissing($this->_selector_modal_entry, self::$WAIT_SECONDS);

            // open entry-modal from navbar; fields should be empty
            $this->openNewEntryModal($browser);
            $browser
                ->with($this->_selector_modal_head, function($modal_head){
                    $modal_head
                        ->assertSee($this->_label_entry_new)
                        ->assertSee($this->_label_btn_confirmed)
                        ->assertNotChecked($this->_selector_modal_entry_btn_confirmed);

                    $entry_confirm_class = $modal_head->attribute($this->_selector_modal_entry_btn_confirmed_label, 'class');
                    $this->assertStringContainsString($this->_class_light_grey_text, $entry_confirm_class);
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

    public function providerAttemptToAddAnAttachmentTooLargeToAnExistingEntry(): array{
        $upload_max_filesize=$this->convertPhpIniFileSizeToBytes(ini_get(self::INI_UPLOADMAXFILESIZE));
        $post_max_size=$this->convertPhpIniFileSizeToBytes(ini_get(self::INI_POSTMAXSIZE));

        return [
            self::INI_UPLOADMAXFILESIZE.'+1'=>[  // test 1/25
                $upload_max_filesize+1,
                'The file "%s" exceeds your upload_max_filesize ini directive'   // this text is lifted from vendor/symfony/http-foundation/File/UploadedFile.php#266
            ],
            self::INI_POSTMAXSIZE=>[          // test 2/25
                $post_max_size,
                'The uploaded file exceeds your post_max_size ini directive.'   // this text is lifted from app/Exceptions/Handler.php#61
            ],
            self::INI_POSTMAXSIZE.'+1'=>[        // test 3/25
                $post_max_size+1,
                'The uploaded file exceeds your post_max_size ini directive.'   // this text is lifted from app/Exceptions/Handler.php#61
            ]
        ];
    }

    /**
     * @dataProvider providerAttemptToAddAnAttachmentTooLargeToAnExistingEntry
     * @param int $max_upload_filesize
     * @param string $error_message
     *
     * @throws Throwable
     * @group entry-modal-3
     * test (see provider)/25
     */
    public function testAttemptToAddAnAttachmentTooLargeToAnExistingEntry(int $max_upload_filesize, string $error_message){
        $dummy_filename = $this->getTestDummyFilename();
        $this->generateDummyFile(
            $dummy_filename,
            $max_upload_filesize
        );
        $this->assertFileExists($dummy_filename);
        $this->assertEquals(filesize($dummy_filename), $max_upload_filesize);

        $this->browse(function(Browser $browser) use ($dummy_filename, $error_message){
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($dummy_filename, $error_message){
                    $this->uploadAttachmentUsingDragNDropAndFailure($entry_modal, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $dummy_filename, sprintf($error_message, basename($dummy_filename)));
                });

            $this->assertNotificationContents(
                $browser,
                self::$NOTIFICATION_TYPE_WARNING,
                sprintf($error_message, basename($dummy_filename))
            );
        });
    }

    public function providerOpeningAnExistingEntryDoesNotResetEntryTableValues():array{
        return [
            'unconfirmed income'=>['is_expense'=>false, 'is_confirmed'=>false], // test 4/25
            'unconfirmed expense'=>['is_expense'=>true, 'is_confirmed'=>false], // test 5/25
            'confirmed income'=>['is_expense'=>false, 'is_confirmed'=>true],    // test 6/25
            'confirmed expense'=>['is_expense'=>true, 'is_confirmed'=>true],    // test 7/25
        ];
    }

    /**
     * @dataProvider providerOpeningAnExistingEntryDoesNotResetEntryTableValues
     * @param bool $is_expense
     * @param bool $is_confirmed
     * @throws Throwable
     *
     * @group entry-modal-3
     * test (see provider)/25
     */
    public function testOpeningAnExistingEntryDoesNotResetEntryTableValues(bool $is_expense, bool $is_confirmed){
        // GIVEN
        $account_type_id = AccountType::all()->pluck('id')->random();

        $entry = factory(Entry::class)->create([
            'entry_date'=>Carbon::tomorrow()->format('Y-m-d'),
            'account_type_id'=>$account_type_id,
            'disabled'=>false,
            'transfer_entry_id'=>0,
            'expense'=>$is_expense,
            'confirm'=>$is_confirmed
        ]);
        // assign tags to entry
        $tag_count = 2;
        $tags = Tag::all()->random($tag_count);
        $tag_ids = $tags->pluck('id')->toArray();
        $entry->tags()->syncWithoutDetaching($tag_ids);
        // attach attachments to this entry
        $attachment = factory(Attachment::class)->create(['entry_id'=>$entry->id]);
        $test_file_path = $this->getTestFileStoragePathFromFilename($attachment->name);
        if(Storage::exists($test_file_path)){
            Storage::copy($test_file_path, $attachment->get_storage_file_path());
        }
        // generate the selector
        $selector_entry_id = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry->id);

        // WHEN
        $this->browse(function(Browser $browser) use ($selector_entry_id, $tags, $is_confirmed, $is_expense){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($selector_entry_id)
                ->within($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_cancel);
                });

            // THEN
            $class_selectors  = '';
            $class_selectors .= $is_expense ? '.is-expense' : '.is-income';
            $class_selectors .= $is_confirmed ? '.is-confirmed' : '';
            $class_selectors .= '.is-transfer';
            $class_selectors .= '.has-attachments';
            $class_selectors .= '.has-tags';
            $browser
                ->scrollToElement($selector_entry_id)
                ->assertVisible($selector_entry_id.$class_selectors)
                ->within($selector_entry_id.$class_selectors, function(Browser $entry_table_record) use ($tags){
                    $entry_table_record
                        ->assertVisible('.row-entry-transfer-checkbox .fas.fa-check-square')
                        ->assertVisible('.row-entry-attachment-checkbox .fas.fa-check-square')
                        ->assertVisible('.row-entry-tags .tags');

                    $this->assertCount($tags->count(), $entry_table_record->elements('.row-entry-tags .tags .tag'));

                    foreach ($tags as $tag){
                        $entry_table_record->assertSeeIn('.row-entry-tags .tags', $tag->name);
                    }
                });
        });
    }

    /**
     * @param int|bool $get_id
     * @return string
     */
    private function randomConfirmedEntrySelector($get_id=false): string{
        if($get_id){
            return $this->randomEntrySelector(['confirm'=>true]);
        } else {
            $confirmed_entry_selectors = [$this->_selector_table_confirmed_expense, $this->_selector_table_confirmed_income];
            return $confirmed_entry_selectors[array_rand($confirmed_entry_selectors, 1)];
        }
    }

    /**
     * @param bool $get_id
     * @return string
     */
    private function randomUnconfirmedEntrySelector(bool $get_id=false): string{
        if($get_id){
            return $this->randomEntrySelector(['confirm'=>false]);
        } else {
            $unconfirmed_entry_selectors = [$this->_selector_table_unconfirmed_expense, $this->_selector_table_unconfirmed_income];
            return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
        }
    }

    /**
     * @throws LengthException
     *
     * @param array $entry_constraints
     * @return string
     */
    private function randomEntrySelector(array $entry_constraints = []): string{
        $entries_collection = $this->getCachedEntriesAsCollection();
        if(!empty($entry_constraints)){
            foreach(array_keys($entry_constraints) as $constraint){
                $entries_collection = $entries_collection->where($constraint, $entry_constraints[$constraint]);
            }
        }
        if($entries_collection->isEmpty()){
            throw new LengthException("Entry collection is empty given entry constraints:".print_r($entry_constraints, true));
        }
        $entry_id = $entries_collection->pluck('id')->random();
        return sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id);
    }

    /**
     * @param string $selector
     * @return string
     */
    private function getEntryIdFromSelector(string $selector): string{
        return str_replace(
            str_replace("%s", '', self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW),
            '',
            $selector
        );
    }

    /**
     * Helps to reduce the amount of HTTP requests made while testing
     *
     * @return Collection
     */
    private function getCachedEntriesAsCollection(){
        if(empty($this->_cached_entries_collection)){
            $this->_cached_entries_collection = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        }
        return $this->_cached_entries_collection;
    }

    /**
     * @param string $ini_value
     * @return int
     */
    private function convertPhpIniFileSizeToBytes(string $ini_value): int{
        $size_type = strtolower($ini_value[strlen($ini_value)-1]);
        $val = intval($ini_value);
        switch($size_type) {
            case 'g':
                return $val * 1024*1024*1024;
            case 'm':
                return $val * 1024*1024;
            case 'k':
                return $val * 1024;
            default:
                return $val;
        }
    }

    private function addRulesToHtaccessToDisableDisplayErrors(){
        copy(self::$HTACCESS_FILEPATH, self::$HTACCESS_FILEPATH.self::$BKUP_EXT);
        $new_rules = <<<HTACCESS_RULES


##### {$this->getName(false)} #####
php_flag display_errors off
php_flag display_startup_errors off
HTACCESS_RULES;
        file_put_contents(self::$HTACCESS_FILEPATH, $new_rules, FILE_APPEND);
    }

    private function revertHtaccessToOriginalState(){
        unlink(self::$HTACCESS_FILEPATH);
        rename(self::$HTACCESS_FILEPATH.self::$BKUP_EXT, self::$HTACCESS_FILEPATH);
    }

}