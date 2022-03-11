<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeSelector as DuskTraitAccountOrAccountTypeSelector;
use App\Traits\Tests\Dusk\EntryModalSelectors as DuskTraitEntryModalSelectors;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use Illuminate\Support\Facades\DB;
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
    use DuskTraitEntryModalSelectors;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use HomePageSelectors;

    private $_message_error_occurred = "An error occurred while attempting to retrieve %s";
    private $_message_not_found = "No %s currently available";

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
     * test 1/25
     */
    public function testNoNotificationOnFetch200(){
        $this->browse(function (Browser $browser) {
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
     * test 2/25
     */
    public function testNotificationFetchAccounts404(){
        // FORCE 404 from `GET /api/accounts`
        DB::table('accounts')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf($this->_message_not_found, "accounts"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 3/25
     */
    public function testNotificationFetchAccounts500(){
        $table = 'accounts';
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/accounts`
        $this->dropTable($table);

        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "accounts"));
            $this->waitForLoadingToStop($browser);
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 4/25
     */
    public function testNotificationFetchAccountTypes404(){
        // FORCE 404 from `GET /api/account-types`
        DB::table('account_types')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf($this->_message_not_found, "account types"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 5/25
     */
    public function testNotificationFetchAccountTypes500(){
        $table = 'account_types';
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/account-types`
        $this->dropTable($table);

        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "account types"));
            $this->waitForLoadingToStop($browser);
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 6/25
     */
    public function testNotificationDeleteAttachment404(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->within($this->_selector_modal_body, function(Browser $entry_modal){
                    // FORCE 404 from `DELETE /api/attachment/{uuid}`
                    DB::table('attachments')->truncate();
                    $entry_modal->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment){
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
     * test 7/25
     */
    public function testNotificationDeleteAttachment500(){
        $table = 'attachments';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function (Browser $browser) use ($table){
            $entry_id = 0;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->within($this->_selector_modal_head, function(Browser $modal_head) use (&$entry_id){
                    $entry_id = trim($modal_head->inputValue($this->_selector_modal_entry_field_entry_id));
                })
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($table){
                    // FORCE 500 from `DELETE /api/attachment/{uuid}`
                    $this->dropTable($table);

                    $modal_body->within($this->_selector_modal_entry_existing_attachments, function(Browser $existing_attachment){
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

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 8/25
     */
    public function testNotificationFetchEntries404(){
        // FORCE 404 from `GET /api/entries`
        DB::table('entries')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "No entries were found");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 9/25
     */
    public function testNotificationFetchEntries500(){
        $table = 'entries';
        $recreate_table_query = $this->getTableRecreationQuery($table);
        // FORCE 500 from `GET /api/entries`
        $this->dropTable($table);

        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "entries"));
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 10/25
     */
    public function testNotificationSaveNewEntry400(){
        // TODO: finish writing me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);
            // TODO: fill in minimum required fields
//                    'account_type_id',
//                    'entry_date',
//                    'entry_value',
//                    'expense',
//                    'memo'
            // TODO: click the save button in the modal footer
            // TODO: FORCE 400 from `POST /api/entry`
            // TODO: FORCE this response: {error: "Forced failure"}
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 11/25
     */
    public function testNotificationSaveNewEntry500(){
        $table = 'entries';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $account_types = collect($this->getApiAccountTypes());
        $account_type = $account_types->where('disabled', false)->random();

        $this->browse(function(Browser $browser) use ($account_type, $table){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);

            // fill in minimum required fields
            $memo_field = "Test entry - 500 ERROR saving requirements";
            $browser
                ->within($this->_selector_modal_body, function(Browser $modal_body) use ($account_type, $memo_field){
                    $this->waitUntilSelectLoadingIsMissing($modal_body, $this->_selector_modal_entry_field_account_type);
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);
                });

            // FORCE 500 from `POST /api/entry`
            $this->dropTable($table);

            $browser->with($this->_selector_modal_foot, function($modal_foot){
                $modal_foot->click($this->_selector_modal_entry_btn_save);
            });

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to create an entry");
            $this->dismissNotification($browser);
            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "entries"));
        });

        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 12/25
     */
    public function testNotificationFetchEntry404(){
        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->pluck('id')->random();

        $this->browse(function (Browser $browser) use($entry_id) {
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
     * test 13/25
     */
    public function testNotificationFetchEntry500(){
        $table = 'entries';
        $recreate_table_query = $this->getTableRecreationQuery($table);
        $this->browse(function(Browser $browser) use ($table){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $this->dropTable($table);

            $browser->openExistingEntryModal($entry_table_row_selector);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "entry"));
        });
        DB::statement($recreate_table_query);
    }

    public function providerNotificationSaveExistingEntry4XX():array{
        return [
            400=>[400, 'bad input | force failure'],       // test 14/25
            404=>[404, 'entry not found | force failure']  // test 15/25
        ];
    }

    /**
     * @dataProvider providerNotificationSaveExistingEntry4XX
     *
     * @param int $http_status
     * @param string $error_response_message
     *
     * @throws Throwable
     *
     * @group notifications-1
     * test (see provider)/25
     */
    public function testNotificationSaveExistingEntry4XX($http_status, string $error_response_message){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: wait for modal to load
            // TODO: change the value of one of the _required_ fields
            // TODO: click the save button in the modal footer
            // TODO: FORCE `$http_status` from `GET /api/entry{entry_id}`
            // TODO: FORCE this response: {error: `$error_response_message`}
            // TODO: wait for notification to pop up
            // TODO: notification is type:warning
            // TODO: notification text:`$error_response_message`
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 16/25
     */
    public function testNotificationSaveExistingEntry500(){
        $table = 'entries';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->where('confirm', 0)->pluck('id')->random();

        $this->browse(function (Browser $browser) use ($entry_id, $table){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->with($this->_selector_modal_body, function(Browser $modal){
                    // We have tests for other fields so lets just update the easiest to update
                    $old_value = floatval($modal->inputValue($this->_selector_modal_entry_field_value));
                    $modal
                        ->clear($this->_selector_modal_entry_field_value)
                        ->type($this->_selector_modal_entry_field_value, $old_value+10);
                    $old_memo = $modal->inputValue($this->_selector_modal_entry_field_memo);
                    $modal
                        ->clear($this->_selector_modal_entry_field_memo)
                        ->type($this->_selector_modal_entry_field_memo, $old_memo.' [UPDATE]');
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) use ($table){
                    // FORCE 500 from `GET /api/entry{entry_id}`
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
     * test 17/25
     */
    public function testNotificationDeleteEntry200(){
        $this->browse(function (Browser $browser) {
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
     * test 18/25
     */
    public function testNotificationDeleteEntry404(){
        $this->browse(function (Browser $browser) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $entry_id = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->within($this->_selector_modal_entry, function(Browser $modal) use (&$entry_id){
                    $entry_id = $modal->inputValue($this->_selector_modal_entry_field_entry_id);

                    // FORCE 404 from `GET /api/entry/{entry_id}`
                    DB::table('entries')->truncate();

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
     * test 20/25
     */
    public function testNotificationDeleteEntry500(){
        $table = 'entries';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function (Browser $browser) use ($table){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $entry_id = '';

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->within($this->_selector_modal_entry, function(Browser $modal) use (&$entry_id, $table){
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
     * test 20/25
     */
    public function testNotificationFetchInstitutions404(){
        // FORCE 404 from `GET /api/institutions`
        DB::table('institutions')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf($this->_message_not_found, "institutions"));
            $this->waitForLoadingToStop($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 21/25
     */
    public function testNotificationFetchInstitutions500(){
        $table = 'institutions';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function (Browser $browser) use ($table){
            // FORCE 500 from `GET /api/institutions`
            $this->dropTable($table);

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "institutions"));
            $this->waitForLoadingToStop($browser);
        });
        DB::statement($recreate_table_query);
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 22/25
     */
    public function testNotificationFetchTags404(){
        // FORCE 404 from `GET /api/tags`
        DB::table('tags')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing(self::$SELECTOR_NOTIFICATION);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 23/25
     */
    public function testNotificationFetchTags500(){
        $table = 'tags';
        $recreate_table_query = $this->getTableRecreationQuery($table);

        $this->browse(function (Browser $browser) use ($table){
            // FORCE 500 from `GET /api/tags`
            $this->dropTable($table);

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "tags"));
            $this->waitForLoadingToStop($browser);
        });
        DB::statement($recreate_table_query);
    }

    /**
     * @param string $table_name
     * @return string
     */
    private function getTableRecreationQuery(string $table_name):string{
        $create_query = DB::select("SHOW CREATE TABLE ".$table_name);
        return $create_query[0]->{"Create Table"};
    }

    /**
     * @param string $table_name
     */
    private function dropTable(string $table_name){
        DB::statement(sprintf("DROP TABLE %s", $table_name));
    }

    private function getEntryTableRowSelector():string{
        $unconfirmed_entry_selectors = [$this->_selector_table_unconfirmed_expense, $this->_selector_table_unconfirmed_income];
        return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
    }

}

