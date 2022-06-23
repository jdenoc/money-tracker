<?php

namespace Tests\Browser;

use App\Models\AccountType;
use App\Traits\ExportsHelper;
use App\Traits\Tests\Dusk\FilterModal as DuskTraitFilterModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
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
    use WithFaker;

    private static $SELECTOR_EXPORT_BTN = '#filter-export-btn';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->initFilterModalTogglingSelectorLabelId();
    }

    public function setUp(): void{
        parent::setUp();
        $this->setAbsoluteDownloadDir();
        $this->clearStorageDownloadDir();
        $this->initFilterModalColors();
    }

    /**
     * @throws Throwable
     *
     * @group filter-modal-export-1
     * test 1/25
     */
    public function testExportButtonNotVisibleByDefault(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal->assertMissing(self::$SELECTOR_EXPORT_BTN);
                });
        });
    }

    public function providerExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared():array{
        return $this->filterModalInputs();    // test (?)/25
    }

    /**
     * @dataProvider providerExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared
     * @param string $filter_input_selector
     * @throws Throwable
     *
     * @group filter-modal-export-1
     * test (see provider)/25
     */
    public function testExportButtonVisibleAfterFilterSetAndNotVisibleWhenCleared(string $filter_input_selector){
        $this->browse(function(Browser $browser) use ($filter_input_selector){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function(Browser $modal) use ($filter_input_selector){
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

    public function providerPerformExport():array{
        $filter_options = $this->filterModalInputs();
        unset($filter_options['Unconfirmed']);  // export does not include information indicating if an entry has been confirmed
        return $filter_options;    // test (?)/25
    }

    /**
     * @dataProvider providerPerformExport
     *
     * @param string $filter_input_selector
     * @throws Throwable
     *
     * @group filter-modal-export-2
     * test (see provider)/25
     */
    public function testPerformExport(string $filter_input_selector){
        $this->browse(function(Browser $browser) use ($filter_input_selector) {
            $filter_value = null;

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter, function (Browser $modal) use ($filter_input_selector, &$filter_value) {
                    $filter_value = $this->filterModalInputInteraction($modal, $filter_input_selector);
                    $modal->assertVisible($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                    $modal->click($this->_selector_modal_foot.' '.self::$SELECTOR_EXPORT_BTN);
                });

            $timestamp = now()->getTimestamp();

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_INFO, "Export Process started");
            $this->dismissNotification($browser);

            $this->assertNotificationContents($browser, self::$NOTIFICATION_TYPE_SUCCESS, "Export Complete");

            $files =  File::glob(storage_path('app/test/downloads/entries.*.csv'));
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

            while($line = fgetcsv($fp)){
                switch($filter_input_selector){
                    case $this->_selector_modal_filter_field_start_date:
                        $this->assertGreaterThanOrEqual($filter_value['actual'], $line[1]);
                        break;
                    case $this->_selector_modal_filter_field_end_date:
                        $this->assertLessThanOrEqual($filter_value['actual'], $line[1]);
                        break;

                    case self::$SELECTOR_FIELD_ACCOUNT_AND_ACCOUNT_TYPE_SELECT:
                        if(is_array($filter_value)){    // account
                            $account_type_ids = AccountType::whereIn('name', $filter_value)->pluck('id')->all();
                            $this->assertContains((int)$line[5], $account_type_ids);
                        } else {    // account-type
                            $account_type_id = AccountType::where('name', $filter_value)->pluck('id')->first();
                            $this->assertEquals((int)$line[5], $account_type_id);
                        }
                        break;

                    case $this->_selector_modal_filter_field_tags:
                        $this->assertNotEmpty($line[8]);
                        $row_tags = json_decode($line[8], true);
                        foreach ($filter_value as $filtered_tag){
                            $this->assertContains($filtered_tag['id'], $row_tags);
                        }
                        break;

                    case $this->_selector_modal_filter_field_switch_income:
                        $this->assertNotEmpty($line[3]);
                        $this->assertEmpty($line[4]);
                        break;
                    case $this->_selector_modal_filter_field_switch_expense:
                        $this->assertEmpty($line[3]);
                        $this->assertNotEmpty($line[4]);
                        break;
                    case $this->_selector_modal_filter_field_switch_has_attachment:
                        $this->assertNotEmpty($line[6]);
                        $this->assertTrue(filter_var($line[6], FILTER_VALIDATE_BOOL));
                        break;
                    case $this->_selector_modal_filter_field_switch_no_attachment:
                        $this->assertEmpty($line[6]);
                        break;
                    case $this->_selector_modal_filter_field_switch_transfer:
                        $this->assertNotEmpty($line[7]);
                        $this->assertTrue(filter_var($line[7], FILTER_VALIDATE_BOOL));
                        break;

                    case $this->_selector_modal_filter_field_switch_unconfirmed:
                        // export does not include information related to entry confirmation
                        break;

                    case $this->_selector_modal_filter_field_min_value:
                        if(!empty($line[3])){           // income
                            $this->assertGreaterThanOrEqual($filter_value, $line[3]);
                        } elseif(!empty($line[4])){     // expense
                            $this->assertGreaterThanOrEqual($filter_value, $line[4]);
                        } else {
                            $this->fail("The income OR expense field was expected to contain a value");
                        }
                        break;
                    case $this->_selector_modal_filter_field_max_value:
                        if(!empty($line[3])){       // income
                            $this->assertLessThanOrEqual($filter_value, $line[3]);
                        } elseif(!empty($line[4])){ // expense
                            $this->assertLessThanOrEqual($filter_value, $line[4]);
                        } else {
                            $this->fail("The income OR expense field was expected to contain a value");
                        }
                        break;
                }
            }
        });
    }

}
