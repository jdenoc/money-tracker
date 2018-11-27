<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class NotificationsTest extends DuskTestCase {

    use DatabaseMigrations;

    private $_selector_notification = ".snotifyToast";
    private $_selector_notification_info = ".snotify-info";
    private $_selector_notification_error = ".snotify-error";
    private $_selector_notification_success = ".snotify-success";

    private $_message_error_occurred = "An error occurred while attempting to retrieve %s";
    private $_message_not_found = "No %s currently available";

    private $_selector_modal = "@entry-modal";
    private $_selector_modal_body = "#entry-modal .modal-card-body";
    private $_selector_modal_body_value = "input#entry-value";
    private $_selector_modal_body_account_type = "select#entry-account-type";
    private $_selector_modal_body_account_type_is_loading = ".select.is-loading select#entry-account-type";
    private $_selector_modal_body_memo = "textarea#entry-memo";

    private $_selector_modal_foot = "#entry-modal .modal-card-foot";
    private $_selector_modal_foot_save_btn = "button#entry-save-btn";


    public function setUp(){
        parent::setUp();
        Artisan::call('db:seed', ['--class'=>'UiSampleDatabaseSeeder']);
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
     */
    public function testNoNotificationOnFetch200(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_notification);
        });
    }

    public function testNotificationFetchAccounts404(){
        // FORCE 404 from `GET /api/accounts`
        DB::statement("DELETE FROM accounts");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS_LONG)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_info)
                ->assertSee(sprintf($this->_message_not_found, "accounts"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_info, HomePage::WAIT_SECONDS);
        });
    }

    public function testNotificationFetchAccounts500(){
        // This query is accurate as of migration:
        // 2018_07_17_160556_add_accounts_column_currency.php
        $recreate_table_query = <<<MYSQL
CREATE TABLE accounts (
  id             int(10)  unsigned   PRIMARY KEY  auto_increment,
  name           varchar(100),
  institution_id int(10) unsigned,
  disabled       tinyint(3) unsigned DEFAULT 0,
  total          decimal(10,2)       DEFAULT 0.00,
  currency       char(3)             DEFAULT "USD",
  create_stamp   timestamp           NULL DEFAULT NULL,
  modified_stamp timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  disabled_stamp timestamp           NULL DEFAULT NULL
);
MYSQL;

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/accounts`
            DB::statement("DROP TABLE accounts");

            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_error)
                ->assertSee(sprintf($this->_message_error_occurred, "accounts"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_error, HomePage::WAIT_SECONDS);

            DB::statement($recreate_table_query);
        });
    }

    public function testNotificationFetchAccountTypes404(){
        // FORCE 404 from `GET /api/account-types`
        DB::statement("DELETE FROM account_types");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_info)
                ->assertSee(sprintf($this->_message_not_found, "account types"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_info, HomePage::WAIT_SECONDS);
        });
    }

    public function testNotificationFetchAccountTypes500(){
        // This query is accurate as of migration:
        // 2018_09_25_011033_update_account_types_column_type.php
        $recreate_table_query = <<<MYSQL
CREATE TABLE account_types (
  id             int(10)  unsigned   PRIMARY KEY  auto_increment,
  type           enum('checking','savings','credit card','debit card','loan'),
  last_digits    varchar(4),
  name           varchar(100),
  account_id int(10) unsigned,
  disabled       tinyint(3) unsigned DEFAULT 0,
  create_stamp   timestamp           NULL DEFAULT NULL,
  modified_stamp timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  disabled_stamp timestamp           NULL DEFAULT NULL
);
MYSQL;

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/account-types`
            DB::statement("DROP TABLE account_types");

            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_error)
                ->assertSee(sprintf($this->_message_error_occurred, "account types"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_error, HomePage::WAIT_SECONDS);

            DB::statement($recreate_table_query);
        });
    }

    public function testNotificationDeleteAttachment204(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to stop
            // TODO: select an existing entry with an attachment from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" attachment button
            // TODO: wait for notification to pop up
            // TODO: notification is type:info
            // TODO: notification text:"Attachment has been deleted"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

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

    public function testNotificationFetchEntries404(){
        // TODO: see note: -----------------VVV-----------------
        $this->markTestIncomplete("Can't do this right now. Need to move notifications into their own component");

        // FORCE 404 from `GET /api/entries`
        DB::statement("DELETE FROM entries");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_info)
                ->assertSee("No entries were found")
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_info, HomePage::WAIT_SECONDS);
        });
    }

    public function testNotificationFetchEntries500(){
        // TODO: see note: -----------------VVV-----------------
        $this->markTestIncomplete("Can't do this right now. Need to move notifications into their own component");

        // This query is accurate as of migration:
        // 2017_11_21_161444_rename_tags_tag_column_to_name.php
        $recreate_table_query = <<<MYSQL
CREATE TABLE entries (
  id               int(10) unsigned      PRIMARY KEY  auto_increment,
  entry_date       date,
  account_type_id  int(10) unsigned,
  entry_value      decimal(10,2),
  memo             text,
  expense          tinyint(3) unsigned,
  confirm          tinyint(3) unsigned,
  disabled         tinyint(1)            DEFAULT 0,
  create_stamp     timestamp             NULL DEFAULT NULL,
  modified_stamp   timestamp             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  disabled_stamp   timestamp             NULL DEFAULT NULL 
);
MYSQL;

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/entries`
            DB::statement("DROP TABLE entries");

            $browser->visit(new HomePage())->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_error)
                ->assertSee(sprintf($this->_message_error_occurred, "entries"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_error, HomePage::WAIT_SECONDS);

            DB::statement($recreate_table_query);


        });
    }

    public function testNotificationSaveNewEntry201(){
        $account_types = $this->getApiAccountTypes();
        $account_type = $account_types[array_rand($account_types, 1)];

        $this->browse(function(Browser $browser) use ($account_type){
            $memo_field = "Test entry - new save - notification";
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openNewEntryModal()
                ->with($this->_selector_modal_body, function($modal_body) use ($account_type, $memo_field){
                    $modal_body
                        ->type($this->_selector_modal_body_value, "9.87")
                        ->waitUntilMissing($this->_selector_modal_body_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_body_account_type, $account_type['id'])
                        ->type($this->_selector_modal_body_memo, $memo_field);
                })
                ->with($this->_selector_modal_foot, function($modal_foot){
                    $modal_foot->click($this->_selector_modal_foot_save_btn);
                })
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS_LONG)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_success)
                ->assertSee("New entry created")
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_success, HomePage::WAIT_SECONDS);
        });
    }

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

    public function testNotificationFetchEntry200(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: make sure no notification pops up
        });
    }

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

    public function testNotificationFetchEntry500(){
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select an existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: FORCE 500 from `GET /api/entries`
            // TODO: wait for notification to pop up
            // TODO: notification is type:error
            // TODO: notification text:"Error occurred while attempting to retrieve entry"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

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
            400=>[400, 'bad input | force failure'],
            404=>[404, 'entry not found | force failure']
        ];
    }

    /**
     * @dataProvider providerNotificationSaveExistingEntry4XX
     *
     * @throws \Throwable
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

    public function testNotificationDeleteEntry200(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select and existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" entry button in the modal footer
            // TODO: wait for notification to pop up
            // TODO: notification is type:success
            // TODO: notification text:"Entry was deleted"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

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

    public function testNotificationDeleteEntry500(){
        // TODO: write me...
        $this->markTestIncomplete();
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage());
            // TODO: wait for loading to hide
            // TODO: select and existing entry from the entries-table
            // TODO: open said entry in an entry-modal
            // TODO: click the "delete" entry button in the modal footer
            // TODO: FORCE 500 from `GET /api/entry/{entry_id}`
            // TODO: wait for notification to pop up
            // TODO: notification is type:error
            // TODO: notification text:"An error occurred while attempting to delete entry [%s]"
            // TODO: wait 5 seconds for notification to disappear
        });
    }

    public function testNotificationFetchInstitutions404(){
        // FORCE 404 from `GET /api/institutions`
        DB::statement("DELETE FROM institutions");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_info)
                ->assertSee(sprintf($this->_message_not_found, "institutions"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_info, HomePage::WAIT_SECONDS);
        });
    }

    public function testNotificationFetchInstitutions500(){
        // This query is accurate as of migration:
        // 2017_06_26_170141_create_institutions_table.php
        $recreate_table_query = <<<MYSQL
CREATE TABLE institutions (
  id             int(10) unsigned  PRIMARY KEY  auto_increment,
  name           varchar(50),
  active         tinyint(4)        DEFAULT 1,
  create_stamp   timestamp         NULL DEFAULT NULL,
  modified_stamp timestamp         NOT NULL DEFAULT CURRENT_TIMESTAMP
);
MYSQL;

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/institutions`
            DB::statement("DROP TABLE institutions");

            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_error)
                ->assertSee(sprintf($this->_message_error_occurred, "institutions"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_error, HomePage::WAIT_SECONDS);

            DB::statement($recreate_table_query);
        });
    }

    public function testNotificationFetchTags404(){
        // FORCE 404 from `GET /api/tags`
        DB::statement("DELETE FROM tags");

        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_notification);
        });
    }

    public function testNotificationFetchTags500(){
        // This query is accurate as of migration:
        // 2017_11_21_161444_rename_tags_tag_column_to_name.php
        $recreate_table_query = <<<MYSQL
CREATE TABLE tags (
  id    int(10) unsigned  PRIMARY KEY  auto_increment,
  name  varchar(50)
);
MYSQL;

        $this->browse(function (Browser $browser) use ($recreate_table_query){
            // FORCE 500 from `GET /api/tags`
            DB::statement("DROP TABLE tags");

            $browser->visit(new HomePage())
                ->waitFor($this->_selector_notification, HomePage::WAIT_SECONDS)
                ->assertVisible($this->_selector_notification.$this->_selector_notification_error)
                ->assertSee(sprintf($this->_message_error_occurred, "tags"))
                ->waitUntilMissing($this->_selector_notification.$this->_selector_notification_error, HomePage::WAIT_SECONDS);

            DB::statement($recreate_table_query);
        });
    }

}

