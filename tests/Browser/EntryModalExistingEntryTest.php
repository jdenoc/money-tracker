<?php

namespace Tests\Browser;

use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\Dusk\BrowserDateUtil as DuskTraitBrowserDateUtil;
use App\Traits\Tests\Dusk\EntryModal as DuskTraitEntryModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\Dusk\Tooltip as DuskTraitTooltip;
use App\Traits\Tests\WaitTimes;
use App\Traits\Tests\WithTailwindColors;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Laravel\Dusk\Browser;
use LengthException;
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
    use DuskTraitBrowserDateUtil;
    use DuskTraitEntryModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use DuskTraitTagsInput;
    use DuskTraitToggleButton;
    use DuskTraitTooltip;
    use EntryTransferKeys;
    use HomePageSelectors;
    use WaitTimes;
    use WithTailwindColors;

    // server stuff
    const INI_POSTMAXSIZE = 'post_max_size';
    const INI_UPLOADMAXFILESIZE = 'upload_max_filesize';
    private const HTACCESS_FILEPATH = 'public/.htaccess';
    private const BKUP_EXT = '.bkup';

    // labels
    private const LABEL_SUCCESS_NOTIFICATION = "Entry updated";

    // classes
    private const CLASS_UNCONFIRMED = '.unconfirmed';
    private const CLASS_IS_CONFIRMED = '.is-confirmed';
    private const CLASS_DISABLED = "disabled";
    private const CLASS_HAS_ATTACHMENTS = ".has-attachments";
    private const CLASS_IS_TRANSFER = ".is-transfer";
    private const CLASS_HAS_TAGS = ".has-tags";
    private const CLASS_EXISTING_ATTACHMENT = "existing-attachment";

    // variables
    private $_cached_entries_collection = [];
    private array $_tests_that_override_htaccess = [];

    public function __construct(?string $name = null) {
        parent::__construct($name);
        $this->_tests_that_override_htaccess[] = 'testAttemptToAddAnAttachmentTooLargeToAnExistingEntry';
    }

    public function setUp(): void {
        parent::setUp();
        $this->_cached_entries_collection = [];
        if (in_array($this->name(), $this->_tests_that_override_htaccess)) {
            $this->addRulesToHtaccessToDisableDisplayErrors();
        }
        $this->initEntryModalColours();
        $this->initTagsInputColors();
    }

    public function tearDown(): void {
        if (in_array($this->name(), $this->_tests_that_override_htaccess)) {
            $this->revertHtaccessToOriginalState();
            // remove any files that any tests may have created
            Storage::disk(self::$TEST_STORAGE_DISK_NAME)->delete(
                // need to remove the filepath prefix before we can delete the file from storage
                str_replace(Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(''), '', $this->getTestDummyFilename())
            );
        }
        $this->assertFileDoesNotExist($this->getTestDummyFilename());
        parent::tearDown();
    }

    public static function providerUnconfirmedEntry(): array {
        return [
            'Expense' => [true],  // test 1/20
            'Income' => [false],  // test 2/20
        ];
    }

    /**
     * @dataProvider providerUnconfirmedEntry
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testClickingOnEntryTableEditButtonOfUnconfirmedEntry(bool $is_expense) {
        $this->browse(function(Browser $browser) use ($is_expense) {
            $data_entry_selector = $is_expense ? $this->_selector_table_unconfirmed_expense : $this->_selector_table_unconfirmed_income;
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($is_expense) {
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);
                    $entry_data['entry_value'] = number_format($entry_data['entry_value'], 2, '.', '');

                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head) {
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                            $this->assertConfirmedButtonInactive($modal_head);
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data, $is_expense) {
                            $this->assertEntryModalDate($modal_body, $entry_data['entry_date']);
                            $this->assertEntryModalValue($modal_body, $entry_data['entry_value']);
                            $this->assertEntryModalAccountType($modal_body, $entry_data['account_type_id']);
                            $this->assertEntryModalMemo($modal_body, $entry_data['memo']);
                            $this->assertEntryModalExpenseState($modal_body, $is_expense);
                        })

                        ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
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

    public static function providerConfirmedEntry(): array {
        return [
            "Expense" => [true],  // test 3/20
            "Income" => [false],  // test 4/20
        ];
    }

    /**
     * @dataProvider providerConfirmedEntry
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testClickingOnEntryTableEditButtonOfConfirmedEntry(bool $is_expense) {
        $this->browse(function(Browser $browser) use ($is_expense) {
            $data_entry_selector = $is_expense ? $this->_selector_table_confirmed_expense : $this->_selector_table_confirmed_income;
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($is_expense) {
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $this->assertNotEmpty($entry_id);
                    $entry_data = $this->getApiEntry($entry_id);
                    $entry_data['entry_value'] = number_format($entry_data['entry_value'], 2, '.', '');

                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head) {
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                            $this->assertConfirmedButtonActive($modal_head);
                            $this->assertEquals("true", $modal_head->attribute($this->_selector_modal_entry_confirmed, "disabled"));
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data, $is_expense) {
                            $this->assertEntryModalDate($modal_body, $entry_data['entry_date']);
                            $this->assertEntryModalValue($modal_body, $entry_data['entry_value']);
                            $this->assertEntryModalAccountType($modal_body, $entry_data['account_type_id']);
                            $this->assertEntryModalMemo($modal_body, $entry_data['memo']);
                            $this->assertEntryModalExpenseState($modal_body, $is_expense);
                            $modal_body
                                ->assertMissing($this->_selector_modal_entry_field_upload)
                                ->assertDontSee(self::$LABEL_FILE_DRAG_N_DROP);

                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, "disabled"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, "readonly"));
                            $this->assertEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_expense, "aria-readonly"));
                        })

                        ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                            $modal_foot
                                ->assertVisible($this->_selector_modal_entry_btn_delete)
                                ->assertSee($this->_label_btn_delete)
                                ->assertVisible($this->_selector_modal_entry_btn_lock)
                                ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.lock-icon')
                                ->assertMissing($this->_selector_modal_entry_btn_lock.' svg.unlock-icon')
                                ->assertVisible($this->_selector_modal_entry_btn_cancel)
                                ->assertSee($this->_label_btn_cancel)
                                ->assertMissing($this->_selector_modal_entry_btn_save)
                                ->assertDontSee($this->_label_btn_save);
                        });
                })
                ->assertEntryModalSaveButtonIsNotDisabled();
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 5/20
     */
    public function testClickingOnEntryTableEditButtonOfConfirmedEntryThenUnlock() {
        $this->browse(function(Browser $browser) {
            $confirmed_entry_selector = $this->randomConfirmedEntrySelector(true);
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($confirmed_entry_selector)
                ->click($this->_selector_modal_entry_btn_lock)
                ->within($this->_selector_modal_head, function(Browser $modal_head) {
                    $modal_head
                        ->assertDontSee($this->_label_entry_new)
                        ->assertSee($this->_label_entry_not_new)
                        ->assertSee($this->_label_btn_confirmed);
                    $this->assertConfirmedButtonActive($modal_head);
                    $this->assertNotEquals("true", $modal_head->attribute($this->_selector_modal_entry_confirmed, "disabled"));
                })

                ->within($this->_selector_modal_body, function(Browser $modal_body) {
                    $this->assertDragNDropDefaultState($modal_body, $this->_selector_modal_entry_field_upload);

                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_date, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_value, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_account_type, 'disabled'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_memo, 'readonly'));
                    $this->assertNotEquals("true", $modal_body->attribute($this->_selector_modal_entry_field_expense, 'readonly'));
                })

                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot
                        ->assertVisible($this->_selector_modal_entry_btn_delete)
                        ->assertSee($this->_label_btn_delete)
                        ->assertVisible($this->_selector_modal_entry_btn_lock)
                        ->assertVisible($this->_selector_modal_entry_btn_lock.' svg.unlock-icon')
                        ->assertMissing($this->_selector_modal_entry_btn_lock.' svg.lock-icon')
                        ->assertVisible($this->_selector_modal_entry_btn_cancel)
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_entry_btn_save)
                        ->assertSee($this->_label_btn_save);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 6/20
     */
    public function testClickingOnEntryTableEditButtonOfEntryWithAttachments() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $entry_selector = $this->randomEntrySelector(['has_attachments' => true]).self::CLASS_EXISTING_ATTACHMENT;
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) {
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $entry_modal->driver->findElements(WebDriverBy::className(self::CLASS_EXISTING_ATTACHMENT));
                    $this->assertGreaterThan(0, count($elements));
                });
        });
    }

    public function providerEntryWithTags(): array {
        // [$data_entry_selector, $data_is_tags_input_visible]
        return [
            // test 7/20
            "Confirmed" => [$this->randomConfirmedEntrySelector().self::CLASS_HAS_TAGS, false],
            // test 8/20
            "Unconfirmed" => [$this->randomUnconfirmedEntrySelector().self::CLASS_HAS_TAGS, true],
        ];
    }

    /**
     * @dataProvider providerEntryWithTags
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testClickingOnEntryTableEditButtonOfEntryWithTags(string $data_entry_selector, bool $data_is_tags_input_visible) {
        $this->browse(function(Browser $browser) use ($data_entry_selector, $data_is_tags_input_visible) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($data_entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($data_is_tags_input_visible) {
                    $entry_id = $entry_modal->value($this->_selector_modal_entry_field_entry_id);
                    $entry_data = Entry::findOrFail($entry_id);
                    $this->assertTrue($entry_data->hasTags);
                    $entry_tags = $entry_data->tags->pluck('name');

                    if ($data_is_tags_input_visible) {
                        $entry_modal->assertVisible(self::$SELECTOR_TAGS_INPUT_INPUT);
                        foreach ($entry_tags as $entry_tag) {
                            $this->assertTagInInput($entry_modal, $entry_tag);
                        }
                    } else {
                        $entry_modal->assertVisible($this->_selector_modal_entry_tags_locked);
                        foreach ($entry_tags as $entry_tag) {
                            $this->assertTagInEntryModalLockedTags($entry_modal, $entry_tag);
                        }
                    }
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 9/20
     */
    public function testOpenAttachment() {
        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomEntrySelector(['has_attachments' => true]).self::CLASS_EXISTING_ATTACHMENT;
            $attachment_name = '';
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_name) {
                    $entry_modal
                        ->assertVisible($this->_selector_modal_entry_existing_attachments)
                        ->within($this->_selector_modal_entry_existing_attachments.' '.$this->_selector_modal_entry_existing_attachments_first_attachment, function(Browser $existing_attachment) use (&$attachment_name) {
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
     * test 10/20
     */
    public function testDeleteAttachmentFromUnconfirmedExistingEntry() {
        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomEntrySelector(['has_attachments' => true, 'confirm' => false]).self::CLASS_EXISTING_ATTACHMENT;
            // initialising this variable here, then pass it as a reference so that we can update its value.
            $attachment_count = 0;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_count) {
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className(self::CLASS_EXISTING_ATTACHMENT));
                    $attachment_count = count($attachments);

                    $entry_modal->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment) {
                        $attachment_name = trim($existing_attachment->text('.'.self::CLASS_EXISTING_ATTACHMENT));
                        $existing_attachment
                            ->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->click($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->assertDialogOpened(sprintf("Are you sure you want to delete attachment: %s", $attachment_name))
                            ->acceptDialog();
                    });
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "Attachment has been deleted");
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $browser
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use (&$attachment_count) {
                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className(self::CLASS_EXISTING_ATTACHMENT));
                    $this->assertCount($attachment_count - 1, $attachments, "Attachment was NOT removed from UI");
                });
        });
    }

    /**
     * @group entry-modal-1
     * test 11/20
     */
    public function testExistingConfirmedEntryWithAttachmentsHasDeleteAttachmentButtonDisabled() {
        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomEntrySelector(['confirm' => true, 'has_attachments' => true]).self::CLASS_EXISTING_ATTACHMENT.self::CLASS_IS_CONFIRMED;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) {
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    // make sure there are attachments in the modal
                    $attachments = $entry_modal->driver->findElements(WebDriverBy::className(self::CLASS_EXISTING_ATTACHMENT));
                    $this->assertGreaterThan(0, count($attachments));

                    $entry_modal->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment) {
                        $existing_attachment->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_delete);
                        $this->assertEquals("true", $existing_attachment->attribute($this->_selector_modal_entry_existing_attachments_attachment_btn_delete, "disabled"));
                    });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 12/20
     */
    public function testUpdateExistingEntryDate() {
        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, &$new_value) {
                    $old_value = $modal_body->inputValue($this->_selector_modal_entry_field_date);
                    // just in case the old and new values match
                    $day_diff = -10;
                    do {
                        $new_value = date("Y-m-d", strtotime(sprintf("%d days", $day_diff)));
                        $day_diff--;
                    } while ($new_value === $old_value);

                    // clear input[type="date"]
                    for ($i = 0; $i < strlen($old_value); $i++) {
                        $modal_body->keys($this->_selector_modal_entry_field_date, "{backspace}");
                    }

                    $browser_date = $this->getDateFromLocale($this->getBrowserLocale($modal_body), $new_value);
                    $new_value_to_type = $this->processLocaleDateForTyping($browser_date);
                    $modal_body->type($this->_selector_modal_entry_field_date, $new_value_to_type);
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);
            $browser
                ->scrollIntoView($entry_selector)
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, $new_value) {
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_date));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_date));
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 13/20
     */
    public function testUpdateExistingEntryAccountType() {
        $account_types = $this->getApiAccountTypes();
        $this->assertGreaterThan(1, count($account_types), "Account-types available are not sufficient for running this test");
        $this->browse(function(Browser $browser) use ($account_types) {
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";
            $new_value = "";

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use (&$old_value, &$new_value, $account_types) {
                    $old_value = $modal_body->value($this->_selector_modal_entry_field_account_type);
                    do {
                        $account_type = fake()->randomElement($account_types);
                        $new_value = $account_type['id'];
                    } while ($old_value == $new_value);
                    $modal_body->select($this->_selector_modal_entry_field_account_type, $new_value);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($old_value, $new_value) {
                    $this->assertNotEquals($old_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                    $this->assertEquals($new_value, $modal_body->value($this->_selector_modal_entry_field_account_type));
                });
        });
    }

    public function providerUpdateEntry(): array {
        return [
            'entry_value' => [$this->_selector_modal_entry_field_value, 0.01],                                    // test 14/20
            'memo' => [$this->_selector_modal_entry_field_memo, "hfrsighesiugbeusigbweuisgbeisugsebuibseiugbg"],  // test 15/20
        ];
    }

    /**
     * @dataProvider providerUpdateEntry
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testUpdateExistingEntryValue(string $field_selector, $new_value) {
        $this->browse(function(Browser $browser) use ($field_selector, $new_value) {
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);
            $old_value = "";

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($field_selector, &$old_value, $new_value) {
                    $old_value = $modal_body->inputValue($field_selector);
                    $modal_body->clear($field_selector);
                    $modal_body->type($field_selector, $new_value);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($field_selector, &$old_value, $new_value) {
                    $this->assertNotEquals($old_value, $modal_body->inputValue($field_selector));
                    $this->assertEquals($new_value, $modal_body->inputValue($field_selector));
                });
        });
    }

    public static function providerOpenExistingEntryInModalThenChangeConfirmSwitch(): array {
        return [
            'unconfirmed->confirmed' => [false],  // test 16/20
            'confirmed->unconfirmed' => [true],   // test 17/20
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeConfirmSwitch
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testOpenExistingEntryInModalThenChangeConfirmSwitch(bool $selector_bool) {
        $entry_selector = $this->randomEntrySelector(['confirm' => $selector_bool]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.($selector_bool ? self::CLASS_IS_CONFIRMED : self::CLASS_UNCONFIRMED))
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) use ($selector_bool) {
                    if ($selector_bool) {
                        $modal_foot->click($this->_selector_modal_entry_btn_lock);
                    }
                })
                ->within($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool) {
                    if ($selector_bool) {
                        $this->assertConfirmedButtonActive($modal_head);
                    } else {
                        $this->assertConfirmedButtonInactive($modal_head);
                    }

                    $this->interactWithConfirmButton($modal_head);

                    if ($selector_bool) {
                        $this->assertConfirmedButtonInactive($modal_head);
                    } else {
                        $this->assertConfirmedButtonActive($modal_head);
                    }
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.($selector_bool ? self::CLASS_UNCONFIRMED : self::CLASS_IS_CONFIRMED))
                ->within($this->_selector_modal_head, function(Browser $modal_head) use ($selector_bool) {
                    if ($selector_bool) {
                        $this->assertConfirmedButtonInactive($modal_head);
                    } else {
                        $this->assertConfirmedButtonActive($modal_head);
                    }
                });
        });
    }

    public static function providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch(): array {
        return [
            'expense->income' => [true],  // test 18/20
            'income->expense' => [false], // test 19/20
        ];
    }

    /**
     * @dataProvider providerOpenExistingEntryInModalThenChangeExpenseIncomeSwitch
     *
     * @throws Throwable
     *
     * @group entry-modal-1
     * test (see provider)/20
     */
    public function testOpenExistingEntryInModalThenChangeExpenseIncomeSwitch(bool $selector_bool) {
        $entry_selector = $this->randomEntrySelector(['expense' => $selector_bool, 'confirm' => false]);
        $this->browse(function(Browser $browser) use ($entry_selector, $selector_bool) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool ? $this->_class_is_expense : $this->_class_is_income))
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool) {
                    $toggle_label = $selector_bool ? $this->_label_expense_switch_expense : $this->_label_expense_switch_income;
                    $toggle_colour = $selector_bool ? $this->_color_expense_switch_expense : $this->_color_expense_switch_income;
                    $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $toggle_label, $toggle_colour);
                    $this->toggleToggleButton($modal_body, $this->_selector_modal_entry_field_expense);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.'.'.($selector_bool ? $this->_class_is_income : $this->_class_is_expense))
                ->with($this->_selector_modal_body, function(Browser $modal_body) use ($selector_bool) {
                    $toggle_label = $selector_bool ? $this->_label_expense_switch_income : $this->_label_expense_switch_expense;
                    $toggle_colour = $selector_bool ? $this->_color_expense_switch_income : $this->_color_expense_switch_expense;
                    $this->assertToggleButtonState($modal_body, $this->_selector_modal_entry_field_expense, $toggle_label, $toggle_colour);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-1
     * test 20/20
     */
    public function testExistingTransferEntryHasEntryButton() {
        $this->browse(function(Browser $browser) {
            $invalid_entry_ids = [];
            do {
                $entry_selector = $this->randomEntrySelector(['is_transfer' => true]);
                $entry_id = $this->getEntryIdFromSelector($entry_selector);
                if (in_array($entry_id, $invalid_entry_ids)) {
                    // already processed this ID, continue to the next iteration
                    continue;
                }
                $invalid_entry_ids[] = $entry_id;
                $entry_data = $this->getApiEntry($entry_id);
            } while ($entry_data['transfer_entry_id'] === self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID);
            unset($invalid_entry_ids);
            $transfer_entry_data = $this->getApiEntry($entry_data['transfer_entry_id']);
            $this->assertEquals($entry_id, $entry_data['id']);
            $this->assertEquals($entry_data['transfer_entry_id'], $transfer_entry_data['id']);
            $this->assertEquals($transfer_entry_data['transfer_entry_id'], $entry_data['id']);
            $entry_selector .= self::CLASS_IS_TRANSFER;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id) {
                    $entry_modal->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id) {
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
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($transfer_entry_data) {
                    $entry_modal
                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($transfer_entry_data) {
                            $this->assertEntryModalDate($modal_body, $transfer_entry_data['entry_date']);
                            $transfer_entry_data['entry_value'] = number_format($transfer_entry_data['entry_value'], 2, '.', '');
                            $this->assertEntryModalValue($modal_body, $transfer_entry_data['entry_value']);
                            $this->assertEntryModalAccountType($modal_body, $transfer_entry_data['account_type_id']);
                            $this->assertEntryModalMemo($modal_body, $transfer_entry_data['memo']);
                            $this->assertEntryModalExpenseState($modal_body, $transfer_entry_data['expense']);
                        })
                        ->within($this->_selector_modal_head, function(Browser $modal_head) use ($transfer_entry_data) {
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
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_data) {
                    $entry_modal
                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data) {
                            $this->assertEntryModalDate($modal_body, $entry_data['entry_date']);
                            $entry_data['entry_value'] = number_format($entry_data['entry_value'], 2, '.', '');
                            $this->assertEntryModalValue($modal_body, $entry_data['entry_value']);
                            $this->assertEntryModalAccountType($modal_body, $entry_data['account_type_id']);
                            $this->assertEntryModalMemo($modal_body, $entry_data['memo']);
                            $this->assertEntryModalExpenseState($modal_body, $entry_data['expense']);
                        })
                        ->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_data) {
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
     * @group entry-modal-2
     * test 1/20
     */
    public function testExistingExternalTransferEntryHasButtonButIsDisabled() {
        $this->browse(function(Browser $browser) {
            $invalid_entry_id = [];
            do {
                $entry_selector = $this->randomEntrySelector(['is_transfer' => true]);
                $entry_id = $this->getEntryIdFromSelector($entry_selector);
                if (in_array($entry_id, $invalid_entry_id)) {
                    // entry ID has already been processed (unsuccessfully), continue to the next iteration
                    continue;
                }
                $invalid_entry_id[] = $entry_id;
                $entry_data = $this->getApiEntry($entry_id);
            } while ($entry_data['transfer_entry_id'] !== self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID);
            unset($invalid_entry_id);
            $this->assertEquals($entry_id, $entry_data['id']);
            $entry_selector .= self::CLASS_IS_TRANSFER;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id) {
                    $entry_modal->within($this->_selector_modal_head, function(Browser $modal_head) use ($entry_id) {
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
     * @group entry-modal-2
     * test 2/20
     */
    public function testDeleteTagsFromExistingEntry() {
        // catch/create potentially missed database entries
        $account_type_id = AccountType::all()->random()->pluck('id')->first();
        $tag_ids = Tag::all()->pluck('id')->toArray();
        $entry = Entry::factory()->create(['entry_date' => date('Y-m-d'), 'expense' => true, 'confirm' => false, 'account_type_id' => $account_type_id]);
        $entry->tags()->syncWithoutDetaching(fake()->randomElements($tag_ids, 2));
        $entry = Entry::factory()->create(['entry_date' => date('Y-m-d'), 'expense' => false, 'confirm' => false, 'account_type_id' => $account_type_id]);
        $entry->tags()->syncWithoutDetaching(fake()->randomElements($tag_ids, 2));
        unset($entry, $tag_ids, $account_type_id);

        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomUnconfirmedEntrySelector(false);
            $entry_id = null;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector.self::CLASS_HAS_TAGS)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector, &$entry_id) {
                    $entry_id = $entry_modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $entry = Entry::findOrFail($entry_id);
                    foreach ($entry->tags->pluck('name')->unique()->values() as $tag) {
                        $this->assertTagInInput($entry_modal, $tag);
                        $entry_modal->click(self::$SELECTOR_TAGS_INPUT_TAG.' .tags-input-remove');
                    }
                    $entry_modal->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);

            $browser
                ->assertMissing(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id).self::CLASS_HAS_TAGS)
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector) {
                    $this->assertDefaultStateOfTagsInput($entry_modal);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-2
     * test 3/20
     */
    public function testUpdateTagsInExistingEntry() {
        // make sure there is at least one tag that doesn't belong to an entry
        // to doubly make sure there is no overlap, name the tag after the test
        // which is outside the typical tag name assignment
        Tag::factory()->create(['name' => $this->name()]);
        $tags_from_api = collect($this->getApiTags());

        $this->browse(function(Browser $browser) use ($tags_from_api) {
            $invalid_entry_ids = [];
            do {
                $entry_selector = $this->randomUnconfirmedEntrySelector(true);
                $entry_id = $this->getEntryIdFromSelector($entry_selector);
                if (in_array($entry_id, $invalid_entry_ids)) {
                    continue;
                }
                $entry = Entry::findOrFail($entry_id);
                $invalid_entry_ids[] = $entry_id;
                // make sure the entry selected doesn't already have all the tags assigned to it
            } while ($entry->tags->count() == $tags_from_api->count());
            unset($invalid_entry_ids);

            $new_tag = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_id, $tags_from_api, &$new_tag) {
                    $entry = Entry::findOrFail($entry_id);

                    $existing_entry_tags = $entry->tags->pluck('name')->all();
                    do {
                        $new_tag = $tags_from_api->pluck('name')->random();
                    } while (in_array($new_tag, $existing_entry_tags));
                    $this->fillTagsInputUsingAutocomplete($entry_modal, $new_tag);
                    $this->assertTagInInput($entry_modal, $new_tag);
                    $entry_modal->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, self::LABEL_SUCCESS_NOTIFICATION);
            $this->waitForLoadingToStop($browser);

            $browser
                ->assertVisible(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id).self::CLASS_HAS_TAGS)
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector, $new_tag) {
                    $this->assertTagInInput($entry_modal, $new_tag);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-2
     * test 4/20
     */
    public function testUploadAttachmentToExistingEntryWithoutSaving() {
        $this->browse(function(Browser $browser) {
            $upload_file_path = $this->getFullPathOfRandomAttachmentFromTestStorage();
            $entry_selector = $this->randomEntrySelector(['confirm' => false]);

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) use ($upload_file_path) {
                    $this->uploadAttachmentUsingDragNDropAndSuccess($entry_modal_body, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $upload_file_path);
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::$LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION, basename($upload_file_path)));
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
     * @group entry-modal-2
     * test 5/20
     */
    public function testOpenExistingEntryInModalThenCloseModalAndOpenNewEntryModal() {
        $this->browse(function(Browser $browser) {
            $entry_selector = $this->randomEntrySelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            // open existing entry in modal and confirm fields are filled
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) {
                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head) {
                            $modal_head
                                ->assertDontSee($this->_label_entry_new)
                                ->assertSee($this->_label_entry_not_new)
                                ->assertSee($this->_label_btn_confirmed);
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) {
                            $modal_body
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_date, "")
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_value, "")
                                ->assertNotSelected($this->_selector_modal_entry_field_account_type, "")
                                ->assertSee($this->_label_account_type_meta_account_name)
                                ->assertSee($this->_label_account_type_meta_last_digits)
                                ->assertInputValueIsNot($this->_selector_modal_entry_field_memo, "");
                        })

                        ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
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
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) {
                    $entry_modal
                        ->within($this->_selector_modal_head, function(Browser $modal_head) {
                            $modal_head
                                ->assertSee($this->_label_entry_new)
                                ->assertSee($this->_label_btn_confirmed);
                            $this->assertConfirmedButtonInactive($modal_head);
                        })

                        ->within($this->_selector_modal_body, function(Browser $modal_body) {
                            $modal_body
                                ->assertInputValue($this->_selector_modal_entry_field_date, date("Y-m-d"))
                                ->assertInputValue($this->_selector_modal_entry_field_value, "")
                                ->assertSelected($this->_selector_modal_entry_field_account_type, "")
                                ->assertDontSee($this->_label_account_type_meta_account_name)
                                ->assertDontSee($this->_label_account_type_meta_last_digits)
                                ->assertInputValue($this->_selector_modal_entry_field_memo, "");
                            $this->assertDefaultStateOfTagsInput($modal_body);
                            $this->assertDragNDropDefaultState($modal_body, $this->_selector_modal_entry_field_upload);
                        })

                        ->with($this->_selector_modal_foot, function(Browser $modal_foot) {
                            $modal_foot
                                ->assertMissing($this->_selector_modal_entry_btn_delete)   // delete button
                                ->assertMissing($this->_selector_modal_entry_btn_lock)     // lock/unlock button
                                ->assertVisible($this->_selector_modal_entry_btn_save);    // save button
                        })
                        ->assertEntryModalSaveButtonIsDisabled();
                });
        });
    }

    public static function providerAttemptToAddAnAttachmentTooLargeToAnExistingEntry(): array {
        $upload_max_filesize = self::convertPhpIniFileSizeToBytes(ini_get(self::INI_UPLOADMAXFILESIZE));
        $post_max_size = self::convertPhpIniFileSizeToBytes(ini_get(self::INI_POSTMAXSIZE));

        return [
            self::INI_UPLOADMAXFILESIZE.'+1' => [  // test 6/20
                $upload_max_filesize + 1,
                'The file "%s" exceeds your upload_max_filesize ini directive',  // this text is lifted from vendor/symfony/http-foundation/File/UploadedFile.php#266
            ],
            self::INI_POSTMAXSIZE => [             // test 7/20
                $post_max_size,
                'The uploaded file exceeds your post_max_size ini directive.',  // this text is lifted from app/Exceptions/Handler.php#61
            ],
            self::INI_POSTMAXSIZE.'+1' => [        // test 8/20
                $post_max_size + 1,
                'The uploaded file exceeds your post_max_size ini directive.',  // this text is lifted from app/Exceptions/Handler.php#61
            ],
        ];
    }

    /**
     * @dataProvider providerAttemptToAddAnAttachmentTooLargeToAnExistingEntry
     *
     * @throws Throwable
     * @group entry-modal-2
     * test (see provider)/20
     */
    public function testAttemptToAddAnAttachmentTooLargeToAnExistingEntry(int $max_upload_filesize, string $error_message) {
        $dummy_filename = $this->getTestDummyFilename();
        $this->generateDummyFile(
            $dummy_filename,
            $max_upload_filesize
        );
        $this->assertFileExists($dummy_filename);
        $this->assertEquals(filesize($dummy_filename), $max_upload_filesize);

        $this->browse(function(Browser $browser) use ($dummy_filename, $error_message) {
            $entry_selector = $this->randomUnconfirmedEntrySelector(true);

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($dummy_filename, $error_message) {
                    $this->uploadAttachmentUsingDragNDropAndFailure($entry_modal, $this->_selector_modal_entry_field_upload, $this->_selector_modal_entry_dropzone_hidden_file_input, $dummy_filename, sprintf($error_message, basename($dummy_filename)));
                });

            $this->assertNotificationContents(
                $browser,
                self::$NOTIFICATION_TYPE_WARNING,
                sprintf($error_message, basename($dummy_filename))
            );
        });
    }

    public function providerOpeningAnExistingEntryDoesNotResetEntryTableValues(): array {
        return [
            'unconfirmed income' => ['is_expense' => false, 'is_confirmed' => false],  // test 9/20
            'unconfirmed expense' => ['is_expense' => true, 'is_confirmed' => false],  // test 10/20
            'confirmed income' => ['is_expense' => false, 'is_confirmed' => true],     // test 11/20
            'confirmed expense' => ['is_expense' => true, 'is_confirmed' => true],     // test 12/20
        ];
    }

    /**
     * @dataProvider providerOpeningAnExistingEntryDoesNotResetEntryTableValues
     * @throws Throwable
     *
     * @group entry-modal-2
     * test (see provider)/20
     */
    public function testOpeningAnExistingEntryDoesNotResetEntryTableValues(bool $is_expense, bool $is_confirmed) {
        // GIVEN
        $account_type_id = AccountType::all()->pluck('id')->random();

        $entry = Entry::factory()->create([
            'entry_date' => Carbon::tomorrow()->format('Y-m-d'),
            'account_type_id' => $account_type_id,
            'transfer_entry_id' => 0,
            'expense' => $is_expense,
            'confirm' => $is_confirmed,
        ]);
        // assign tags to entry
        $tag_count = 2;
        $tags = Tag::all()->random($tag_count);
        $tag_ids = $tags->pluck('id')->toArray();
        $entry->tags()->syncWithoutDetaching($tag_ids);
        // attach attachments to this entry
        $attachment = Attachment::factory()->create(['entry_id' => $entry->id]);
        $test_file_path = $this->getTestStorageFileAttachmentFilePathFromFilename($attachment->name);
        if (Storage::disk(self::$TEST_STORAGE_DISK_NAME)->exists($test_file_path)) {
            $this->copyFromTestDiskToAppDisk($test_file_path, $attachment->get_storage_file_path());
        }
        // generate the selector
        $selector_entry_id = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry->id);

        // WHEN
        $this->browse(function(Browser $browser) use ($selector_entry_id, $tags, $is_confirmed, $is_expense) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($selector_entry_id)
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_cancel);
                });

            // THEN
            $class_selectors  = '';
            $class_selectors .= $is_expense ? '.is-expense' : '.is-income';
            $class_selectors .= $is_confirmed ? self::CLASS_IS_CONFIRMED : self::CLASS_UNCONFIRMED;
            $class_selectors .= self::CLASS_IS_TRANSFER;
            $class_selectors .= self::CLASS_EXISTING_ATTACHMENT;
            $class_selectors .= self::CLASS_HAS_TAGS;
            $browser
                ->scrollIntoView($selector_entry_id)
                ->assertVisible($selector_entry_id.$class_selectors)
                ->within($selector_entry_id.$class_selectors, function(Browser $entry_table_record) use ($tags) {
                    $entry_table_record
                        ->assertVisible($this->_selector_table_row_attachment_checkmark)
                        ->assertVisible($this->_selector_table_row_transfer_checkmark)
                        ->assertVisible($this->_selector_table_row_tags.' .tags');

                    $this->assertCount($tags->count(), $entry_table_record->elements($this->_selector_table_row_tags.' .tags .tag'));

                    foreach ($tags as $tag) {
                        $entry_table_record->assertSeeIn($this->_selector_table_row_tags.' .tags', $tag->name);
                    }
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-2
     * test 13/20
     */
    public function testMarkingEntryUnconfirmedAfterUnlockingMakesLockButtonDisappear() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $entry_selector = $this->randomConfirmedEntrySelector(true);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($entry_selector) {
                    // unlock modal
                    $this->unlockEntryModal($entry_modal);
                    // toggle the confirmed button
                    $this->assertConfirmedButtonActive($entry_modal);
                    $this->interactWithConfirmButton($entry_modal);
                    $this->assertConfirmedButtonInActive($entry_modal);
                    // lock button missing
                    $entry_modal->assertMissing($this->_selector_modal_foot.' '.$this->_selector_modal_entry_btn_lock);
                });
        });
    }

    public function providerOpenExistingConfirmedEntryUnlockingChangingValuesAndRelockingResetsValues(): array {
        return [
            'date' => [$this->_selector_modal_entry_field_date],                  // test 14/20
            'value' => [$this->_selector_modal_entry_field_value],                // test 15/20
            'account-type' => [$this->_selector_modal_entry_field_account_type],  // test 16/20
            'memo' => [$this->_selector_modal_entry_field_memo],                  // test 17/20
            'expense' => [$this->_selector_modal_entry_field_expense],            // test 18/20
            'tags' => [$this->_selector_modal_entry_tags_locked],                 // test 19/20
        ];
    }

    /**
     * @dataProvider providerOpenExistingConfirmedEntryUnlockingChangingValuesAndRelockingResetsValues
     * @throws Throwable
     *
     * @group entry-modal-2
     * test (see provider)/20
     */
    public function testOpenExistingConfirmedEntryUnlockingChangingValuesAndRelockingResetsValues(string $modal_input_selector) {
        if ($modal_input_selector === $this->_selector_modal_entry_tags_locked) {
            // make sure there is at least one tag that doesn't belong to an entry
            $tag_to_test = fake()->word();
            Tag::factory()->create(['name' => $tag_to_test]);
        } else {
            $tag_to_test = null;
        }

        $this->browse(function(Browser $browser) use ($modal_input_selector, $tag_to_test) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $entry_selector = $this->randomConfirmedEntrySelector(true);
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($modal_input_selector, $entry_selector, $tag_to_test) {
                    // retrieve entry data prior to input changes to avoid worry of values changing unintentionally
                    $entry_id = $this->getEntryIdFromSelector($entry_selector);
                    $entry_data = $this->getApiEntry($entry_id);
                    $entry_data['entry_value'] = number_format($entry_data['entry_value'], 2, '.', '');

                    // unlock modal
                    $entry_modal->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                        $this->unlockEntryModal($modal_foot);
                    });

                    // modify an input
                    $entry_modal->within($this->_selector_modal_body, function(Browser $modal_body) use ($modal_input_selector, $entry_data, $tag_to_test) {
                        switch($modal_input_selector) {
                            case $this->_selector_modal_entry_field_date:
                                $temp_value = fake()->date('Y-m-d');
                                $this->setEntryModalDate($modal_body, $temp_value);
                                break;
                            case $this->_selector_modal_entry_field_value:
                                $temp_value = fake()->randomFloat(2);
                                $this->setEntryModalValue($modal_body, $temp_value);
                                break;
                            case $this->_selector_modal_entry_field_account_type:
                                $account_types = $this->getApiAccountTypes();
                                $this->assertGreaterThan(1, count($account_types), "Account-types available are not suffient for running this test");
                                do {
                                    $account_type = fake()->randomElement($account_types);
                                    $temp_value = $account_type['id'];
                                } while ($entry_data['account_type_id'] == $temp_value);

                                $this->setEntryModalAccountType($modal_body, $temp_value);
                                break;
                            case $this->_selector_modal_entry_field_memo:
                                $temp_value = fake()->sentence();
                                $this->setEntryModalMemo($modal_body, $temp_value);
                                break;
                            case $this->_selector_modal_entry_field_expense:
                                $this->toggleEntryModalExpense($modal_body);
                                $this->assertEntryModalExpenseState($modal_body, !$entry_data['expense']);
                                break;
                            case $this->_selector_modal_entry_tags_locked:
                                $this->fillTagsInputUsingAutocomplete($modal_body, $tag_to_test);
                                break;
                            default:
                                throw new InvalidArgumentException($modal_input_selector.' is not valid or accounted for');
                        }
                    });

                    // re-lock modal and confirm input values are unchanged
                    $entry_modal
                        ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                            $this->lockEntryModal($modal_foot);
                        })
                        ->within($this->_selector_modal_body, function(Browser $modal_body) use ($entry_data) {
                            $this->assertEntryModalDate($modal_body, $entry_data['entry_date']);
                            $this->assertEntryModalValue($modal_body, $entry_data['entry_value']);
                            $this->assertEntryModalAccountType($modal_body, $entry_data['account_type_id']);
                            $this->assertEntryModalMemo($modal_body, $entry_data['memo']);
                            $this->assertEntryModalExpenseState($modal_body, $entry_data['expense']);

                            foreach ($entry_data['tags'] as $tag) {
                                $this->assertTagInEntryModalLockedTags($modal_body, $tag['name']);
                            }
                            $this->assertCountOfLockedTagsInEntryModal($modal_body, count($entry_data['tags']));
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group entry-modal-2
     * test 20/20
     */
    public function testLongAttachmentNameIsTruncatedAndHoveringOverAttachmentNameShowsTooltip() {
        $account_type_id = AccountType::all()->pluck('id')->random();
        $entry = Entry::factory()->create([
            'entry_date' => Carbon::today()->format('Y-m-d'),
            'account_type_id' => $account_type_id,
            'confirm' => false,
        ]);
        $attachment_name = 'this-is-a-super-long-attachment-name-that-should-be-truncated.txt';
        Attachment::factory()->create([
            'name' => $attachment_name,
            'entry_id' => $entry->id,
        ]);

        $this->browse(function(Browser $browser) use ($entry, $attachment_name) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry->id))
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) use ($attachment_name) {
                    // confirm there is exactly one attachment
                    $entry_modal->assertVisible($this->_selector_modal_entry_existing_attachments);
                    $elements = $entry_modal->driver->findElements(WebDriverBy::className(self::CLASS_EXISTING_ATTACHMENT));
                    $this->assertCount(1, $elements);

                    // confirm attachment name is truncated
                    $attachment_name_element = '.'.self::CLASS_EXISTING_ATTACHMENT.' .attachment-name';
                    $is_attachment_name_truncated = $entry_modal->script(
                        // if the offsetWidth < scrollWidth; then the string has been truncated in some fashion
                        "return document.querySelector('$attachment_name_element').offsetWidth < document.querySelector('$attachment_name_element').scrollWidth;"
                    )[0];
                    $this->assertTrue($is_attachment_name_truncated);

                    // confirm tooltip is visible after hover and contains full attachment name
                    $this->assertTooltipMissing($entry_modal);
                    $this->interactWithElementToTriggerTooltip($entry_modal, $attachment_name_element);
                    $this->assertStringInTooltipContentsByTriggerElement($entry_modal, $attachment_name, $attachment_name_element);
                });
        });
    }

    private function randomConfirmedEntrySelector(bool $get_id = false): string {
        if ($get_id) {
            return $this->randomEntrySelector(['confirm' => true]);
        } else {
            $confirmed_entry_selectors = [$this->_selector_table_confirmed_expense, $this->_selector_table_confirmed_income];
            return $confirmed_entry_selectors[array_rand($confirmed_entry_selectors, 1)];
        }
    }

    private function randomUnconfirmedEntrySelector(bool $get_id = false): string {
        if ($get_id) {
            return $this->randomEntrySelector(['confirm' => false]);
        } else {
            $unconfirmed_entry_selectors = [$this->_selector_table_unconfirmed_expense, $this->_selector_table_unconfirmed_income];
            return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
        }
    }

    /**
     * @throws LengthException
     */
    private function randomEntrySelector(array $entry_constraints = []): string {
        $entries_collection = $this->getCachedEntriesAsCollection();
        if (!empty($entry_constraints)) {
            foreach (array_keys($entry_constraints) as $constraint) {
                $entries_collection = $entries_collection->where($constraint, $entry_constraints[$constraint]);
            }
        }
        if ($entries_collection->isEmpty()) {
            throw new LengthException("Entry collection is empty given entry constraints:".print_r($entry_constraints, true));
        }
        $entry_id = $entries_collection->pluck('id')->random();
        return sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id);
    }

    private function getEntryIdFromSelector(string $selector): string {
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
    private function getCachedEntriesAsCollection() {
        if (empty($this->_cached_entries_collection)) {
            $this->_cached_entries_collection = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        }
        return $this->_cached_entries_collection;
    }

    private static function convertPhpIniFileSizeToBytes(string $ini_value): int {
        $size_type = strtolower($ini_value[strlen($ini_value) - 1]);
        $val = intval($ini_value);
        switch($size_type) {
            case 'g':
                return $val * 1024 * 1024 * 1024;
            case 'm':
                return $val * 1024 * 1024;
            case 'k':
                return $val * 1024;
            default:
                return $val;
        }
    }

    private function generateDummyFile(string $filename, int $file_size_in_bytes): void {
        $fp = fopen($filename, 'w');
        fseek($fp, $file_size_in_bytes - 1, SEEK_CUR);
        fwrite($fp, 'z');
        fclose($fp);
    }

    private function getTestDummyFilename(): string {
        return Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(self::$storage_test_attachment_path.$this->name().'.txt');
    }

    private function addRulesToHtaccessToDisableDisplayErrors(): void {
        copy(self::HTACCESS_FILEPATH, self::HTACCESS_FILEPATH.self::BKUP_EXT);
        $new_rules = <<<HTACCESS_RULES


##### {$this->nameWithDataSet()} #####
php_flag display_errors off
php_flag display_startup_errors off
HTACCESS_RULES;
        file_put_contents(self::HTACCESS_FILEPATH, $new_rules, FILE_APPEND);
    }

    private function revertHtaccessToOriginalState(): void {
        unlink(self::HTACCESS_FILEPATH);
        rename(self::HTACCESS_FILEPATH.self::BKUP_EXT, self::HTACCESS_FILEPATH);
    }

}
