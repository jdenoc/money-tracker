<?php

namespace Tests\Browser;

use App\Traits\ExportsHelper;
use App\Traits\Tests\Dusk\FilterModal as DuskTraitFilterModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * @group filter-modal-export
 * @group filter-modal
 * @group modal
 * @group home
 */
class ExportsTest extends DuskTestCase {
    use DuskTraitFilterModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use DuskTraitNotification;
    use ExportsHelper;
    use HomePageSelectors;

    private static string $SELECTOR_EXPORT_BTN = '#filter-export-btn';

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->initFilterModalTogglingSelectorLabelId();
    }

    public function setUp(): void {
        parent::setUp();
        $this->clearStorageDownloadDir();
        $this->initFilterModalColors();
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-export-1
     * test 1/20
     */
    public function testExportButtonNotVisibleByDefault() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal->assertMissing(self::$SELECTOR_EXPORT_BTN);
                });
        });
    }

    public function providerExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared(): array {
        return $this->filterModalInputs();    // test (2 - 13)/20
    }

    /**
     * @dataProvider providerExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared
     * @param string $filter_input_selector
     * @throws Throwable
     *
     * @group filter-modal-export-1
     * test (see provider)/20
     */
    public function testExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared(string $filter_input_selector) {
        $this->browse(function(Browser $browser) use ($filter_input_selector) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function(Browser $modal) use ($filter_input_selector) {
                    $modal
                        ->assertMissing($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);

                    $this->filterModalInputInteraction($modal, $filter_input_selector);

                    $modal->assertVisible($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                    $this->assertElementBackgroundColor($modal, $this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN, $this->_color_filter_btn_export);
                    $modal
                        ->click($this->_selector_modal_foot.' '.$this->_selector_modal_filter_btn_reset)
                        ->assertMissing($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                });
        });
    }

    public function providerPerformExport(): array {
        $filter_options = $this->filterModalInputs();
        unset($filter_options['Unconfirmed']);  // export does not include information indicating if an entry has been confirmed
        return $filter_options;    // test (1 - 11)/20
    }

    /**
     * @dataProvider providerPerformExport
     *
     * @group filter-modal-export-2
     * test (see provider)/20
     */
    public function testPerformExport(string $filter_input_selector) {
        $this->browse(function(Browser $browser) use ($filter_input_selector) {
            $filter_value = null;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function(Browser $modal) use ($filter_input_selector, &$filter_value) {
                    $filter_value = $this->filterModalInputInteraction($modal, $filter_input_selector);
                    $modal->assertVisible($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                    $modal->click($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                });

            $timestamp = now()->getTimestamp();

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "Export Process started");
            $this->dismissNotification($browser);

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, "Export Complete");

            $files = File::glob(Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(self::$STORAGE_DOWNLOAD_DIR.'entries.*.csv'));
            $this->assertNotEmpty($files);
            $file_path = $files[array_key_last($files)];
            $this->assertNotEmpty($file_path);

            // make sure export filename is within a time period variance after clicking on the export button
            $timestamp_variance = 2;
            $file_timestamp=filter_var(basename($file_path), FILTER_SANITIZE_NUMBER_INT);
            $this->assertTrue(
                $file_timestamp >= ($timestamp-$timestamp_variance) && $file_timestamp <= ($timestamp+$timestamp_variance),
                sprintf("Failed asserting %d >= %d <= %d", ($timestamp-$timestamp_variance), $file_timestamp, ($timestamp+$timestamp_variance))
            );

            $this->assertFileExists($file_path, "Directory [".$this->getAbsoluteDownloadDir()."] contents: ".print_r(File::files($this->getAbsoluteDownloadDir()), true));
            $fp = fopen($file_path, 'r');

            // assert header line of file
            $header = fgetcsv($fp);
            $this->assertEquals($this->getCsvHeaderLine(), $header);

            $line_number = 0;
            while ($line = fgetcsv($fp)) {
                $line_number++;
                $error_msg = "item not found on line:%d; containing:".json_encode($line).'; column:%d';
                switch($filter_input_selector) {
                    case $this->_selector_modal_filter_field_start_date:
                        $this->assertGreaterThanOrEqual($filter_value['actual'], $line[1], sprintf($error_msg, $line_number, 1));
                        break;
                    case $this->_selector_modal_filter_field_end_date:
                        $this->assertLessThanOrEqual($filter_value['actual'], $line[1], sprintf($error_msg, $line_number, 1));
                        break;
                    case self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT:
                        if(is_array($filter_value)) {    // account
                            $account_type_ids = $filter_value;
                        } else {    // account-type
                            $account_type_ids = [$filter_value];
                        }
                        $this->assertContains((int)$line[5], $account_type_ids, sprintf($error_msg, $line_number, 5));
                        break;
                    case $this->_selector_modal_filter_field_tags:
                        $this->assertNotEmpty($line[8], sprintf($error_msg, $line_number, 8));
                        $row_tags = json_decode($line[8], true);
                        foreach ($filter_value as $filtered_tag) {
                            $this->assertContains($filtered_tag['id'], $row_tags, sprintf($error_msg, $line_number, 8));
                        }
                        break;
                    case $this->_selector_modal_filter_field_switch_income:
                        $this->assertNotEmpty($line[3], sprintf($error_msg, $line_number, 3));
                        $this->assertEmpty($line[4], sprintf($error_msg, $line_number, 4));
                        break;
                    case $this->_selector_modal_filter_field_switch_expense:
                        $this->assertEmpty($line[3], sprintf($error_msg, $line_number, 3));
                        $this->assertNotEmpty($line[4], sprintf($error_msg, $line_number, 4));
                        break;
                    case $this->_selector_modal_filter_field_switch_has_attachment:
                        $this->assertNotEmpty($line[6], sprintf($error_msg, $line_number, 6));
                        $this->assertTrue(filter_var($line[6], FILTER_VALIDATE_BOOL), sprintf($error_msg, $line_number, 6));
                        break;
                    case $this->_selector_modal_filter_field_switch_no_attachment:
                        $this->assertEmpty($line[6], sprintf($error_msg, $line_number, 6));
                        break;
                    case $this->_selector_modal_filter_field_switch_transfer:
                        $this->assertNotEmpty($line[7], sprintf($error_msg, $line_number, 7));
                        $this->assertTrue(filter_var($line[7], FILTER_VALIDATE_BOOL), sprintf($error_msg, $line_number, 7));
                        break;
                    case $this->_selector_modal_filter_field_switch_unconfirmed:
                        // export does not include information related to entry confirmation
                        break;
                    case $this->_selector_modal_filter_field_min_value:
                        if (!empty($line[3])) {           // income
                            $this->assertGreaterThanOrEqual($filter_value, $line[3], sprintf($error_msg, $line_number, 3));
                        } elseif (!empty($line[4])) {     // expense
                            $this->assertGreaterThanOrEqual($filter_value, $line[4], sprintf($error_msg, $line_number, 4));
                        } else {
                            $this->fail("The income OR expense field was expected to contain a value\n".sprintf($error_msg, $line_number, -1));
                        }
                        break;
                    case $this->_selector_modal_filter_field_max_value:
                        if (!empty($line[3])) {       // income
                            $this->assertLessThanOrEqual($filter_value, $line[3], sprintf($error_msg, $line_number, 3));
                        } elseif (!empty($line[4])) { // expense
                            $this->assertLessThanOrEqual($filter_value, $line[4], sprintf($error_msg, $line_number, 4));
                        } else {
                            $this->fail("The income OR expense field was expected to contain a value\n".sprintf($error_msg, $line_number, -1));
                        }
                        break;
                }
            }
        });
    }

}
