<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
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

    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use HomePageSelectors;

    private $_selector_unconfirmed_expense = "tr.has-background-warning.is-expense";
    private $_selector_unconfirmed_income = 'tr.has-background-warning.is-income';

    private $_selector_modal = "@entry-modal";
    private $_selector_modal_body_value = "input#entry-value";
    private $_selector_modal_body_account_type = "select#entry-account-type";
    private $_selector_modal_body_account_type_is_loading = ".select.is-loading select#entry-account-type";
    private $_selector_modal_body_memo = "textarea#entry-memo";
    private $_selector_modal_foot_save_btn = "button#entry-save-btn";
    private $_selector_modal_foot_delete_btn = "button#entry-delete-btn";

    private $_selector_notification = "@notification";

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
            $browser->assertMissing($this->_selector_notification);
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
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 3/25
     */
    public function testNotificationFetchAccounts500(){
        $recreate_table_query = $this->getTableRecreationQuery('accounts');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/accounts`
            DB::statement("DROP TABLE accounts");

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "accounts"));

            DB::statement($recreate_table_query);
        });
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
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 5/25
     */
    public function testNotificationFetchAccountTypes500(){
        $recreate_table_query = $this->getTableRecreationQuery('account_types');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/account-types`
            DB::statement("DROP TABLE account_types");

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "account types"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 6/25
     */
    public function testNotificationDeleteAttachment404(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->with($this->_selector_modal_body, static function(){
                    // TODO: FORCE 404 from `DELETE /api/attachment/{uuid}`
                    $attachment_uuid = '';  // TODO: get attachment UUID
                    DB::table('attachments')->where('uuid', $attachment_uuid)->delete();
                    // TODO: click the "delete" attachment button
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
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($this->getEntryTableRowSelector().'.has-attachments')
                ->with($this->_selector_modal_body, static function(Browser $modal){
                    // TODO: FORCE 500 from `DELETE /api/attachment/{uuid}`
                    // TODO: click the "delete" attachment button
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to delete entry attachment [%s]");
        });
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
        $recreate_table_query = $this->getTableRecreationQuery('entries');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/entries`
            DB::statement("DROP TABLE entries");

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "entries"));

            DB::statement($recreate_table_query);
        });
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
            // TODO: click the save button in the modal footer
            // TODO: FORCE 400 from `POST /api/entry`
            // TODO: FORCE this response: {error: "Forced failure"}
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_WARNING, "Forced failure");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 11/25
     */
    public function testNotificationSaveNewEntry500(){
        $recreate_table_query = $this->getTableRecreationQuery('entries');

        $account_types = collect($this->getApiAccountTypes());
        $account_type = $account_types->where('disabled', false)->random();

        $this->browse(function(Browser $browser) use ($account_type){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openNewEntryModal($browser);

            // fill in minimum required fields
            $memo_field = "Test entry - 500 ERROR saving requirements";
            $browser
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_entry_field_value, "9.99")
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, self::$WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $account_type['id'])
                        ->type($this->_selector_modal_entry_field_memo, $memo_field);
                });

            // FORCE 500 from `POST /api/entry`
            DB::statement("DROP TABLE entries");

            $browser->with($this->_selector_modal_foot, function($modal_foot){
                $modal_foot->click($this->_selector_modal_entry_btn_save);
            });

            $this->waitForLoadingToStop($browser);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to retrieve entries");
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
        $recreate_table_query = $this->getTableRecreationQuery("entries");
        $this->browse(function(Browser $browser) use ($recreate_table_query){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            DB::statement("DROP TABLE entries");

            $browser->openExistingEntryModal($entry_table_row_selector);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "entry"));
            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 14/25
     */
    public function testNotificationSaveExistingEntry200(){
        // TODO: finish writing me...
        $this->markTestIncomplete();
        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->pluck('id')->random();

        $this->browse(function (Browser $browser) use ($entry_id) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->with($this->_selector_modal_body, static function(Browser $modal){
                    // TODO: change the value of one of the _required_ fields
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, "Entry updated");
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
     * @throws Throwable
     *
     * @group notifications-1
     * test (see provider)/25
     */
    public function testNotificationSaveExistingEntry4XX($http_status, $error_response_message){
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
     * test 17/25
     */
    public function testNotificationSaveExistingEntry500(){
        // TODO: write me...
        $this->markTestIncomplete();
        $entries = collect($this->removeCountFromApiResponse($this->getApiEntries()));
        $entry_id = $entries->pluck('id')->random();

        $this->browse(function (Browser $browser) use ($entry_id){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal(sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry_id))
                ->with($this->_selector_modal_body, static function(Browser $modal){
                    // TODO: change the value of one of the _required_ fields
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    // TODO: FORCE 500 from `GET /api/entry{entry_id}`
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to update entry [%s]");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 18/25
     */
    public function testNotificationDeleteEntry200(){
        $this->browse(function (Browser $browser) {
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector)
                ->click($this->_selector_modal_foot_delete_btn);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, "Entry was deleted");
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 19/25
     */
    public function testNotificationDeleteEntry404(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
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
     * @throws Throwable
     *
     * @group notifications-1
     * test 20/25
     */
    public function testNotificationDeleteEntry500(){
        $recreate_table_query = $this->getTableRecreationQuery('entries');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            $entry_table_row_selector = $this->getEntryTableRowSelector();
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->openExistingEntryModal($entry_table_row_selector);

            DB::statement("DROP TABLE entries");

            $browser->click($this->_selector_modal_foot_delete_btn);
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, "An error occurred while attempting to delete entry [");
            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 21/25
     */
    public function testNotificationFetchInstitutions404(){
        // FORCE 404 from `GET /api/institutions`
        DB::table('institutions')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, sprintf($this->_message_not_found, "institutions"));
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 22/25
     */
    public function testNotificationFetchInstitutions500(){
        $recreate_table_query = $this->getTableRecreationQuery('institutions');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/institutions`
            DB::statement("DROP TABLE institutions");

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "institutions"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 23/25
     */
    public function testNotificationFetchTags404(){
        // FORCE 404 from `GET /api/tags`
        DB::table('tags')->truncate();

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_notification);
        });
    }

    /**
     * @throws Throwable
     *
     * @group notifications-1
     * test 24/25
     */
    public function testNotificationFetchTags500(){
        $recreate_table_query = $this->getTableRecreationQuery('tags');

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/tags`
            DB::statement("DROP TABLE tags");

            $browser->visit(new HomePage());
            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_ERROR, sprintf($this->_message_error_occurred, "tags"));

            DB::statement($recreate_table_query);
        });
    }

    /**
     * @param $table_name
     * @return string
     */
    private function getTableRecreationQuery($table_name){
        $create_query = DB::select("SHOW CREATE TABLE ".$table_name);
        return $create_query[0]->{"Create Table"};
    }

    private function getEntryTableRowSelector(){
        $unconfirmed_entry_selectors = [$this->_selector_unconfirmed_expense, $this->_selector_unconfirmed_income];
        return $unconfirmed_entry_selectors[array_rand($unconfirmed_entry_selectors, 1)];
    }

}

