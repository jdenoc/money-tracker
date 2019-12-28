<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\InjectDatabaseStateIntoException;

/**
 * Class NotificationsTest
 *
 * @package Tests\Browser
 *
 * @group notifications
 */
class NotificationsTest extends DuskTestCase {

    use InjectDatabaseStateIntoException;

    private $_selector_unconfirmed_expense = "tr.has-background-warning.is-expense";
    private $_selector_unconfirmed_income = 'tr.has-background-warning.is-income';

    private $_selector_modal = "@entry-modal";
    private $_selector_modal_body = "#entry-modal .modal-card-body";
    private $_selector_modal_body_value = "input#entry-value";
    private $_selector_modal_body_account_type = "select#entry-account-type";
    private $_selector_modal_body_account_type_is_loading = ".select.is-loading select#entry-account-type";
    private $_selector_modal_body_memo = "textarea#entry-memo";
    private $_selector_modal_foot = "#entry-modal .modal-card-foot";
    private $_selector_modal_foot_save_btn = "button#entry-save-btn";
    private $_selector_modal_foot_delete_btn = "button#entry-delete-btn";

    private $_selector_notification = "@notification";

    private $_message_error_occurred = "An error occurred while attempting to retrieve %s";
    private $_message_not_found = "No %s currently available";

    public function setUp(){
        parent::setUp();
        $this->setDatabaseStateInjectionPermission(self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION);
    }

    /**
     * There should be no notifications when the following are successful:
     *  - accounts
     *  - account-types
     *  - entries
     *  - institutions
     *  - tags
     *
     * @throws \Throwable
     *
     * @group notifications-1
     * test 1/25
     */
    public function testNoNotificationOnFetch200(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_notification);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 2/25
     */
    public function testNotificationFetchAccounts404(){
        // FORCE 404 from `GET /api/accounts`
        DB::statement("TRUNCATE accounts");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_INFO, sprintf($this->_message_not_found, "accounts"));
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 3/25
     */
    public function testNotificationFetchAccounts500(){
        $recreate_table_query = $this->getTableRecreationQuery('accounts');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/accounts`
            DB::statement("DROP TABLE accounts");

            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "accounts"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 4/25
     */
    public function testNotificationFetchAccountTypes404(){
        // FORCE 404 from `GET /api/account-types`
        DB::statement("TRUNCATE account_types");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_INFO, sprintf($this->_message_not_found, "account types"));
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 5/25
     */
    public function testNotificationFetchAccountTypes500(){
        $recreate_table_query = $this->getTableRecreationQuery('account_types');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/account-types`
            DB::statement("DROP TABLE account_types");

            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "account types"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 6/25
     */
    public function testNotificationDeleteAttachment404(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to stop
            // TODO: select an existing entry with an attachment from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" attachment button
            // TODO: FORCE 404 from `DELETE /api/attachment/{uuid}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:warning
            // TODO: notification text:"Could not delete attachment"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @groups notifications-1
     * test 7/25
     */
    public function testNotificationDeleteAttachment500(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to stop
            // TODO: select an existing entry with an attachment from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" attachment button
            // TODO: FORCE 500 from `DELETE /api/attachment/{uuid}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:error
            // TODO: notification text:"An error occurred while attempting to delete entry attachment [%s]"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 8/25
     */
    public function testNotificationFetchEntries404(){
        // FORCE 404 from `GET /api/entries`
        DB::statement("TRUNCATE entries");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_INFO, "No entries were found");
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 9/25
     */
    public function testNotificationFetchEntries500(){
        $recreate_table_query = $this->getTableRecreationQuery('entries');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/entries`
            DB::statement("DROP TABLE entries");

            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "entries"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 10/25
     */
    public function testNotificationSaveNewEntry400(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: click the "New Entry" navbar button
            // TODO: wait for modal to load
            // TODO: fill in minimum required fields
            // TODO: click the save button in the modal footer
            // TODO: FORCE 400 from `POST /api/entry`
            // TODO: FORCE this response: {error: "Forced failure"}
            // TODO: wait for notification to pop up
            // TODO: notification is type:warning
            // TODO: notification text:"Forced failure"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 11/25
     */
    public function testNotificationSaveNewEntry500(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: click the "New Entry" navbar button
            // TODO: wait for modal to load
            // TODO: fill in minimum required fields
            // TODO: click the save button in the modal footer
            // TODO: FORCE 500 from `POST /api/entry`
            // TODO: wait for notification to pop up
            // TODO: notification is type:error
            // TODO: notification text:"An error occurred while attempting to create an entry"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 12/25
     */
    public function testNotificationFetchEntry404(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: FORCE 404 from `GET /api/entry{entry_id}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:warning
            // TODO: notification text:"Entry does not exist"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 13/25
     */
    public function testNotificationFetchEntry500(){
        $recreate_table_query = $this->getTableRecreationQuery("entries");
        $this->browse(function(Browser $browser) use ($recreate_table_query){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop();

            DB::statement("DROP TABLE entries");

            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "entry"));
            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 14/25
     */
    public function testNotificationSaveExistingEntry200(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: wait for modal to load
            // TODO: change the value of one of the _required_ fields
            // TODO: click the save button in the modal footer
            // TODO: wait for notification to pop up
            // TODO: notification is type:success
            // TODO: notification text:"Entry updated"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    public function providerNotificationSaveExistingEntry4XX(){
        return [
            400=>[400, 'bad input | force failure'],       // test 15/25
            404=>[404, 'entry not found | force failure']  // test 16/25
        ];
    }

    /**
     * @dataProvider providerNotificationSaveExistingEntry4XX
     *
     * @param int $http_status
     * @param string $error_response_message
     *
     * @throws \Throwable
     *
     * @group notifications-1
     * test (see provider)/25
     */
    public function testNotificationSaveExistingEntry4XX($http_status, $error_response_message){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
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
     * @throws \Throwable
     *
     * @group notifications-1
     * test 17/25
     */
    public function testNotificationSaveExistingEntry500(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: wait for modal to load
            // TODO: change the value of one of the _required_ fields
            // TODO: click the save button in the modal footer
            // TODO: FORCE 500 from `GET /api/entry{entry_id}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:error
            // TODO: notification text:"An error occurred while attempting to update entry [%s]"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 18/25
     */
    public function testNotificationDeleteEntry200(){
        $this->browse(function (Browser $browser) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_table_row_selector)
                ->click($this->_selector_modal_foot_delete_btn)
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, "Entry was deleted");
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 19/25
     */
    public function testNotificationDeleteEntry404(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select and existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" entry button in the modal footer
            // TODO: FORCE 404 from `GET /api/entry/{entry_id}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:success
            // TODO: notification text:"Entry [%s] does not exist and cannot be deleted"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 20/25
     */
    public function testNotificationDeleteEntry500(){
        $recreate_table_query = $this->getTableRecreationQuery('entries');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openExistingEntryModal($entry_table_row_selector);

            DB::statement("DROP TABLE entries");

            $browser
                ->click($this->_selector_modal_foot_delete_btn)
                ->assertNotification(HomePage::NOTIFICATION_ERROR, "An error occurred while attempting to delete entry [");
            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 21/25
     */
    public function testNotificationFetchInstitutions404(){
        // FORCE 404 from `GET /api/institutions`
        DB::statement("TRUNCATE institutions");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_INFO, sprintf($this->_message_not_found, "institutions"));
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 22/25
     */
    public function testNotificationFetchInstitutions500(){
        $recreate_table_query = $this->getTableRecreationQuery('institutions');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/institutions`
            DB::statement("DROP TABLE institutions");

            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "institutions"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 23/25
     */
    public function testNotificationFetchTags404(){
        // FORCE 404 from `GET /api/tags`
        DB::statement("TRUNCATE tags");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_notification);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group notifications-1
     * test 24/25
     */
    public function testNotificationFetchTags500(){
        $recreate_table_query = $this->getTableRecreationQuery('tags');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/tags`
            DB::statement("DROP TABLE tags");

            $browser->visit(new HomePage())
                ->assertNotification(HomePage::NOTIFICATION_ERROR, sprintf($this->_message_error_occurred, "tags"));

            DB::statement($recreate_table_query);
        });
    }

    private function getTableRecreationQuery($table_name){
        $create_query = DB::select("SHOW CREATE TABLE ".$table_name);
        return $create_query[0]->{"Create Table"};
    }

    private function getEntryTableRowSelector(){
        $unconfirmed_entry_selectors = [$this->_selector_unconfirmed_expense, $this->_selector_unconfirmed_income];
        return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
    }

}

