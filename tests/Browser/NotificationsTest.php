<?php

namespace Tests\Browser;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\BaseModel;
use App\Models\Entry;
use App\Models\Institution;
use App\Models\Tag;
use App\Traits\EntryResponseKeys;
use App\Traits\Tests\Dusk\AccountOrAccountTypeSelector as DuskTraitAccountOrAccountTypeSelector;
use App\Traits\Tests\Dusk\EntryModal as DuskTraitEntryModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * Class NotificationsTest
 *
 * @package Tests\Browser
 *
 * @group notifications
 */
class NotificationsTest extends DuskTestCase {
    use DuskTraitAccountOrAccountTypeSelector;
    use DuskTraitEntryModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use EntryResponseKeys;
    use HomePageSelectors;

    // message templates
    private const TEMPLATE_MESSAGE_ERROR_OCCURRED = "An error occurred while attempting to retrieve %s";
    private const TEMPLATE_MESSAGE_NOT_FOUND = "No %s currently available";

    public function setUp(): void {
        parent::setUp();

        switch($this->name()) {
            // disable & clear cache prior to the dropping any tables
            case 'testNotificationFetchAccounts500':
                Account::cache()->disable();
                Cache::flush();
                break;
            case 'testNotificationFetchAccountTypes500':
                AccountType::cache()->disable();
                Cache::flush();
                break;
            case 'testNotificationFetchInstitutions500':
                Institution::cache()->disable();
                Cache::flush();
                break;
            case 'testNotificationFetchTags500':
                Tag::cache()->disable();
                Cache::flush();
                break;
        }
    }

    /**
     * There should be no notifications when the following are successful:
     *  - accounts
     *  - account-types
     *  - entries
     *  - institutions
     *  - tags
     *
     * @throws Throwable
     *
     * @group notifications-1
     * test 1/20
     */
    public function testNoNotificationOnFetch200() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertLogoVisible($browser);   // make sure that other elements are also visible
            $browser->assertMissing(self::$SELECTOR_NOTIFICATION);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 2/20
     */
    public function testNotificationFetchAccounts404() {
        // FORCE 404 from `GET /api/accounts`
        $this->truncateTable(Account::getTableName());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::TEMPLATE_MESSAGE_NOT_FOUND, "accounts"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 3/20
     */
    public function testNotificationFetchAccounts500() {
        $table = Account::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/accounts`
        $this->dropTable($table);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "accounts"));
            $this->waitForLoadingToStop($browser);
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 4/20
     */
    public function testNotificationFetchAccountTypes404() {
        // FORCE 404 from `GET /api/account-types`
        $this->truncateTable(AccountType::getTableName());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::TEMPLATE_MESSAGE_NOT_FOUND, "account types"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 5/20
     */
    public function testNotificationFetchAccountTypes500() {
        $table = AccountType::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/account-types`
        $this->dropTable($table);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "account types"));
            $this->waitForLoadingToStop($browser);
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 6/20
     */
    public function testNotificationDeleteAttachment404() {
        $this->generatedEntryWithAttachmentOneMonthInTheFuture();

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->within($this->_selector_modal_body, function(Browser $entry_modal) {
                    // FORCE 404 from `DELETE /api/attachment/{uuid}`
                    $this->truncateTable(Attachment::getTableName());
                    $entry_modal->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment) {
                        // grab the filename of the attachment
                        $filename = trim($existing_attachment->text($this->_selector_modal_entry_existing_attachments_attachment_name));

                        $existing_attachment
                            ->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->click($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->waitForDialog()
                            ->assertDialogOpened(sprintf("Are you sure you want to delete attachment: %s", $filename))
                            ->acceptDialog();
                    });
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_WARNING, "Could not delete attachment");
        });
    }

    /**
     * @throws Throwable
     *
     * @groups notifications-1
     * test 7/20
     */
    public function testNotificationDeleteAttachment500() {
        $this->generatedEntryWithAttachmentOneMonthInTheFuture();

        $table = Attachment::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function(Browser $browser) use ($table) {
            $entry_id = 0;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->within($this->_selector_modal_head, function(Browser $modal_head) use (&$entry_id) {
                    $entry_id = trim($modal_head->inputValue($this->_selector_modal_entry_field_entry_id));
                })
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($table) {
                    // FORCE 500 from `DELETE /api/attachment/{uuid}`
                    $this->dropTable($table);

                    $modal_body->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment) {
                        // grab the filename of the attachment
                        $filename = trim($existing_attachment->text($this->_selector_modal_entry_existing_attachments_attachment_name));

                        $existing_attachment
                            ->assertVisible($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->click($this->_selector_modal_entry_existing_attachments_attachment_btn_delete)
                            ->waitForDialog()
                            ->assertDialogOpened(sprintf("Are you sure you want to delete attachment: %s", $filename))
                            ->acceptDialog();
                    });
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf("An error occurred while attempting to delete entry attachment [%s]", $entry_id));
        });

        DB::statement($recreate_table_query);
    }

    private function generatedEntryWithAttachmentOneMonthInTheFuture() {
        $account_type = AccountType::all()->random();
        $new_entry = Entry::factory()->create([
            'account_type_id' => $account_type->id,
            'entry_date' => Carbon::now()->addDays(30),
        ]);
        Attachment::factory()->create(['entry_id' => $new_entry->id]);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 8/20
     */
    public function testNotificationFetchEntries404() {
        // FORCE 404 from `GET /api/entries`
        $this->truncateTable(Entry::getTableName());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "No entries were found");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 9/20
     */
    public function testNotificationFetchEntries500() {
        $table = Entry::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/entries`
        $this->dropTable($table);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "entries"));
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 10/20
     */
    public function testNotificationSaveNewEntry400() {
        // TODO: finish writing me...
        $this->markTestIncomplete();
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            // TODO: fill in minimum required fields
            //        'account_type_id',
            //        'entry_date',
            //        'entry_value',
            //        'expense',
            //        'memo'
            // TODO: click the save button in the modal footer
            // TODO: FORCE 400 from `POST /api/entry`
            // TODO: FORCE this response: {error: "Forced failure"}
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 11/20
     */
    public function testNotificationSaveNewEntry500() {
        $table = Entry::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $account_types = collect($this->getApiAccountTypes());
        $account_type = $account_types->where('disabled', false)->random();

        $this->browse(function(Browser $browser) use ($account_type, $table) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);

            // fill in minimum required fields
            $memo_field = "Test entry - 500 ERROR saving requirements";
            $browser
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($account_type, $memo_field) {
                    $this->waitUntilSelectLoadingIsMissing($modal_body, self::$SELECTOR_MODAL_ENTRY_FIELD_ACCOUNT_TYPE);
                    $modal_body
                        ->type(self::$SELECTOR_MODAL_ENTRY_FIELD_VALUE, "9.99")
                        ->select(self::$SELECTOR_MODAL_ENTRY_FIELD_ACCOUNT_TYPE, $account_type['id'])
                        ->type(self::$SELECTOR_MODAL_ENTRY_FIELD_MEMO, $memo_field);
                });

            // FORCE 500 from `POST /api/entry`
            $this->dropTable($table);

            $browser->with($this->_selector_modal_foot, function($modal_foot) {
                $modal_foot->click($this->_selector_modal_entry_btn_save);
            });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to create an entry");
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "entries"));
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 12/20
     */
    public function testNotificationFetchEntry404() {
        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->pluck('id')->random();

        $this->browse(function(Browser $browser) use ($entry_id) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // FORCE 404 from `GET /api/entry{entry_id}`
            DB::table('entries')->delete($entry_id);
            $browser->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id));

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_WARNING, "Entry does not exist");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 13/20
     */
    public function testNotificationFetchEntry500() {
        $this->markTestSkipped("previous implementation no longer works due to refactoring of EntryController");
        $table = Entry::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);
        $this->browse(function(Browser $browser) use ($table) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $this->dropTable($table);

            $browser->openExistingEntryModal($entry_table_row_selector);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "entry"));
        });
        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 14/20
     */
    public function testNotificationSaveExistingEntry404() {
        $entries_collection = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries_collection->where('confirm', false)->pluck('id')->random();

        $this->browse(function(Browser $browser) use ($entry_id) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_body, function(Browser $modal_body) {
                    $this->toggleToggleButton($modal_body, self::$SELECTOR_MODAL_ENTRY_FIELD_EXPENSE);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    // FORCE 404 from `GET /api/entry/{entry_id}`
                    $this->truncateTable(Entry::getTableName());

                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_WARNING, self::$ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST);
            $this->dismissNotification($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 15/20
     */
    public function testNotificationSaveExistingEntry500() {
        $this->markTestSkipped("previous implementation no longer works due to refactoring of EntryController");
        $table = Entry::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->where('confirm', 0)->pluck('id')->random();

        $this->browse(function(Browser $browser) use ($entry_id, $table) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->within($this->_selector_modal_body, function(Browser $modal) {
                    // We have tests for other fields so lets just update the easiest to update
                    $old_value = floatval($modal->inputValue(self::$SELECTOR_MODAL_ENTRY_FIELD_VALUE));
                    $modal
                       ->clear(self::$SELECTOR_MODAL_ENTRY_FIELD_VALUE)
                        ->type(self::$SELECTOR_MODAL_ENTRY_FIELD_VALUE, $old_value + 10);
                    $old_memo = $modal->inputValue(self::$SELECTOR_MODAL_ENTRY_FIELD_MEMO);
                    $modal
                        ->clear(self::$SELECTOR_MODAL_ENTRY_FIELD_MEMO)
                        ->type(self::$SELECTOR_MODAL_ENTRY_FIELD_MEMO, $old_memo.' [UPDATE]');
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) use ($table) {
                    // FORCE 500 from `GET /api/entry/{entry_id}`
                    $this->dropTable($table);

                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf("An error occurred while attempting to update entry [%s]", $entry_id));
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 16/20
     */
    public function testNotificationDeleteEntry200() {
        $this->browse(function(Browser $browser) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->click($this->_selector_modal_entry_btn_delete);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, "Entry was deleted");
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 17/20
     */
    public function testNotificationDeleteEntry404() {
        $this->browse(function(Browser $browser) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $entry_id = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->within($this->_selector_modal_entry, function(Browser $modal) use (&$entry_id) {
                    $entry_id = $modal->inputValue($this->_selector_modal_entry_field_entry_id);

                    // FORCE 404 from `GET /api/entry/{entry_id}`
                    $this->truncateTable(Entry::getTableName());

                    $modal->click($this->_selector_modal_entry_btn_delete);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_WARNING, sprintf("Entry [%s] does not exist and cannot be deleted", $entry_id));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 18/20
     */
    public function testNotificationDeleteEntry500() {
        $this->markTestSkipped("previous implementation no longer works due to refactoring of EntryController");
        $table = Entry::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function(Browser $browser) use ($table) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $entry_id = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->within($this->_selector_modal_entry, function(Browser $modal) use (&$entry_id, $table) {
                    $entry_id = $modal->inputValue($this->_selector_modal_entry_field_entry_id);
                    $this->dropTable($table);
                    $modal->click($this->_selector_modal_entry_btn_delete);
                });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf("An error occurred while attempting to delete entry [%s]", $entry_id));
        });
        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 19/20
     */
    public function testNotificationFetchInstitutions404() {
        // FORCE 404 from `GET /api/institutions`
        $this->truncateTable(Institution::getTableName());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf(self::TEMPLATE_MESSAGE_NOT_FOUND, "institutions"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 20/20
     */
    public function testNotificationFetchInstitutions500() {
        $table = Institution::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function(Browser $browser) use ($table) {
            // FORCE 500 from `GET /api/institutions`
            $this->dropTable($table);

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "institutions"));
            $this->waitForLoadingToStop($browser);
        });
        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-2
     * test 1/20
     */
    public function testNotificationFetchTags404() {
        // FORCE 404 from `GET /api/tags`
        $this->truncateTable(Tag::getTableName());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing(self::$SELECTOR_NOTIFICATION);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-2
     * test 2/20
     */
    public function testNotificationFetchTags500() {
        $table = Tag::getTableName();
        $recreate_table_query = $this->getTableRecreationQuery($table);

        // FORCE 500 from `GET /api/tags`
        $this->dropTable($table);
        Cache::clear();

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf(self::TEMPLATE_MESSAGE_ERROR_OCCURRED, "tags"));
            $this->waitForLoadingToStop($browser);
        });
        DB::statement($recreate_table_query);
    }

    private function getTableRecreationQuery(string $table_name): string {
        $create_query = DB::select(sprintf("SHOW CREATE TABLE %s", $table_name));
        return $create_query[0]->{"Create Table"};
    }

    private function dropTable(string $tableName): void {
        DB::statement(sprintf("DROP TABLE %s", $tableName));
    }

    private function truncateTable(string $tableName): void {
        DB::table($tableName)->truncate();
        $this->refreshCache($tableName);
    }

    private function getModelFromTableName(string $tableName): BaseModel {
        $model = 'App\Models\\'.Str::studly(Str::singular($tableName));
        if (class_exists($model)) {
            return new $model();
        } else {
            throw new \Exception();
        }
    }

    private function refreshCache(string $tableName) {
        $model = $this->getModelFromTableName($tableName);
        // Only "refresh" cache if model has a cache
        // Note: Full class name is needed for "needle" without it, the full class name isn't used.
        if (in_array(\Mostafaznv\LaraCache\Traits\LaraCache::class, class_uses($model))) {
            $model::cache()->updateAll();
        }
    }

    private function getEntryTableRowSelector(): string {
        $unconfirmed_entry_selectors = [$this->_selector_table_unconfirmed_expense, $this->_selector_table_unconfirmed_income];
        return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
    }

}
