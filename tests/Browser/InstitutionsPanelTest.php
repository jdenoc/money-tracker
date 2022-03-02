<?php

namespace Tests\Browser;

use App\Account;
use App\Institution;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\WaitTimes;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * Class InstitutionsPanelTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group home
 */
class InstitutionsPanelTest extends DuskTestCase {

    use WaitTimes;
    use HomePageSelectors;
    use DuskTraitLoading;

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 1/25
     */
    public function testOverviewOptionIsVisibleAndActiveByDefault(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->with($this->_selector_panel_institutions, function(Browser $panel){
                    $panel
                        ->assertSeeIn($this->_selector_panel_institutions_heading, "Institutions")
                        ->assertVisible($this->_selector_panel_institutions_overview)
                        ->assertSeeIn($this->_selector_panel_institutions_overview, "Overview");

                    $overview_class = $panel->attribute($this->_selector_panel_institutions_overview, 'class');
                    $this->assertStringContainsString($this->_class_is_active, $overview_class);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 2/25
     */
    public function testActiveInstitutionsAreVisibleWithAccountsAndClickingOnAnAccountFiltersEntries(){
        $institutions_collection = $this->getInstitutionsCollection();
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($institutions_collection, $accounts_collection, $account_types_collection){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->with($this->_selector_panel_institutions, function(Browser $panel) use ($institutions_collection, $accounts_collection, $account_types_collection){
                    $active_institutions_collection = $this->getInstitutionsCollection(false)->sortBy('name');

                    foreach($active_institutions_collection as $active_institution){
                        $panel->with('#institution-'.$active_institution['id'], function(Browser $institution_node) use ($active_institution, $accounts_collection, $account_types_collection){
                            // confirm "accordion" is closed
                            $open_close_node_class = $institution_node->attribute($this->_selector_panel_institutions_institution_open_close, 'class');
                            $this->assertStringContainsString('fa-chevron-down', $open_close_node_class);

                            // confirm institution name is within collection
                            $institutions_node_name = $institution_node->text($this->_selector_panel_institutions_institution_name);
                            $this->assertEquals($active_institution['name'], $institutions_node_name);

                            $institution_node
                                // confirm accounts NOT visible
                                ->assertMissing($this->_selector_panel_institutions_accounts)
                                // click institutions node;
                                ->click('')
                                ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                                // accounts now visible
                                ->assertVisible($this->_selector_panel_institutions_accounts);

                            // confirm "accordion" is open
                            $open_close_node_class = $institution_node->attribute($this->_selector_panel_institutions_institution_open_close, 'class');
                            $this->assertStringContainsString('fa-chevron-up', $open_close_node_class);

                            $institution_accounts_collection = $accounts_collection
                                ->where('disabled', false)
                                ->where('institution_id', $active_institution['id'])
                                ->sortBy('name');
                            foreach($institution_accounts_collection as $institution_account){
                                $institution_node->with($this->_selector_panel_institutions_accounts.' #account-'.$institution_account['id'], function(Browser $account_node) use ($institution_account, $account_types_collection){
                                    $this->assertAccountNodeName($account_node, $institution_account['name']);
                                    $account_account_types_collection = $account_types_collection->where('account_id', $institution_account['id']);
                                    $this->assertInstitutionPanelAccountNode($account_node, $account_account_types_collection);
                                    $this->assertInstitutionPanelAccountNodeClickInteraction($account_node, $account_types_collection);
                                });
                            }
                        });
                    }
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 3/25
     */
    public function testDisabledAccountsElementNotVisibleIfNoDisabledAccountsExist(){
        $institutions_collection = $this->getInstitutionsCollection(false);
        $institution_id = $institutions_collection->pluck('id')->random(1)->first();
        DB::table('accounts')->truncate();
        factory(Account::class, 3)->create(['disabled'=>0, 'institution_id'=>$institution_id]);

        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing("#closed-accounts");
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 4/25
     */
    public function testDisabledAccountsAreVisibleAndClickingOnADisabledAccountFiltersEntries(){
        $institutions_collection = $this->getInstitutionsCollection(false);
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $disabled_accounts_collection = $accounts_collection->where('disabled', true);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($disabled_accounts_collection, $account_types_collection){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertVisible("#closed-accounts")
                ->with("#closed-accounts", function(Browser $closed_accounts) use ($disabled_accounts_collection, $account_types_collection){
                    // confirm the label says "closed accounts"
                    $closed_accounts_label = $closed_accounts->text('span.name-label');
                    $this->assertEquals("Closed Accounts", $closed_accounts_label);

                    // confirm "accordion" is closed
                    $open_close_node_class = $closed_accounts->attribute('span.panel-icon i', 'class');
                    $this->assertStringContainsString('fa-chevron-up', $open_close_node_class);

                    $closed_accounts
                        // confirm accounts NOT visible
                        ->assertMissing($this->_selector_panel_institutions_accounts)
                        // click "closed accounts"
                        ->click('')
                        ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                        // accounts now visible
                        ->assertVisible($this->_selector_panel_institutions_accounts);

                    // confirm "accordion" is open
                    $open_close_node_class = $closed_accounts->attribute('span.panel-icon i', 'class');
                    $this->assertStringContainsString('fa-chevron-down', $open_close_node_class);

                    foreach($disabled_accounts_collection as $account_data){
                        $closed_accounts->with($this->_selector_panel_institutions_accounts.' #account-'.$account_data['id'], function(Browser $account_node) use ($account_data, $account_types_collection){
                            $account_account_types_collection = $account_types_collection->where('account_id', $account_data['id']);
                            $this->assertInstitutionPanelAccountNode($account_node, $account_account_types_collection, false);
                            $this->assertInstitutionPanelAccountNodeClickInteraction($account_node, $account_account_types_collection);
                        });
                    }
                });
        });
    }

    public function providerAccountTotalValueIsTwoDecimalPlaces(){
        return [
            ["0.12"],   // test 5/25
            ["0.10"],   // test 6/25
            ["0.01"],   // test 7/25
            ["0.00"],   // test 8/25
            ["-0.01"],  // test 9/25
            ["-0.10"],  // test 10/25
            ["-0.12"]   // test 11/25
        ];
    }

    /**
     * @dataProvider providerAccountTotalValueIsTwoDecimalPlaces
     * @param string $test_total
     *
     * @throws Throwable
     *
     * @group navigation-1
     * test (see provider)/25
     */
    public function testAccountTotalValueIsTwoDecimalPlaces($test_total){
        DB::table('institutions')->truncate();
        $new_institution = factory(Institution::class)->create(['active'=>true]);
        $institution_id = $new_institution->id;
        DB::table('accounts')->truncate();
        $new_account = factory(Account::class)->create(['institution_id'=>$institution_id, 'total'=>$test_total, 'disabled'=>false]);
        DB::statement("UPDATE account_types SET account_id=:id", ['id'=>$new_account->id]);

        $this->browse(function(Browser $browser) use ($institution_id, $new_account, $test_total){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->with($this->_selector_panel_institutions, function(Browser $panel) use ($institution_id, $new_account, $test_total){
                    $panel->with("#institution-".$institution_id, function(Browser $institution_node) use ($new_account, $test_total){
                        $institution_node
                            ->click('')         // click institutions node;
                            ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                            ->with($this->_selector_panel_institutions_accounts.' #account-'.$new_account->id, function(Browser $account_node) use ($new_account, $test_total){
                                $this->assertAccountNodeName($account_node, $new_account->name);
                                 // confirm total value is to two decimal places
                                $account_total_text = $account_node->text($this->_selector_panel_institutions_accounts_account_total);
                                $this->assertEquals($test_total, $test_total);
                                $this->assertEquals($test_total, $account_total_text);
                                $this->assertEquals(1, preg_match("/\d+\.\d{2}/", $account_total_text));
                        });

                    });
                });
        });
    }

    /**
     * @param Browser $account_node
     * @param string $account_name
     */
    private function assertAccountNodeName(Browser $account_node, string $account_name){
        // confirm account name is within collection
        $account_node_name = $account_node->text($this->_selector_panel_institutions_accounts_account_name.' span:first-child');
        $this->assertStringContainsString($account_name, $account_node_name, "account name NOT found within institution-account node");
    }

    /**
     * @param Browser $account_node
     * @param Collection $account_types_collection
     * @param boolean $has_tooltip
     */
    private function assertInstitutionPanelAccountNode(Browser $account_node, $account_types_collection, bool $has_tooltip=true){
        // hover over account element
        $account_node->mouseover('');

        if($has_tooltip){
            // account-types tooltip appears to right
            $account_node_tooltip_id = $account_node->attribute('', 'aria-describedby');    // get the tooltip element id
            $account_node
                ->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS)
                ->assertVisible('#'.$account_node_tooltip_id);
            $account_types_tooltip_text = $account_node->text('#'.$account_node_tooltip_id);
            foreach($account_types_collection as $account_account_type){
                $account_type_record_tooltip_text = $account_account_type['name']." (".$account_account_type['last_digits'].")";
                if($account_account_type['disabled']){
                    $this->assertStringNotContainsString($account_type_record_tooltip_text, $account_types_tooltip_text);
                } else {
                    $this->assertStringContainsString($account_type_record_tooltip_text, $account_types_tooltip_text);
                }
            }
        } else {
            $account_css_prefix = $account_node->resolver->prefix;
            $account_node->resolver->prefix = '';
            $account_node->assertMissing('body .tooltip');
            $account_node->resolver->prefix = $account_css_prefix;
        }

        // account is NOT "active"
        $account_name_class = $account_node->attribute($this->_selector_panel_institutions_accounts_account_name, 'class');
        $this->assertStringNotContainsString('is-active', $account_name_class);
    }

    /**
     * @param Browser $account_node
     * @param Collection $account_types_collection
     */
    private function assertInstitutionPanelAccountNodeClickInteraction(Browser $account_node, $account_types_collection){
        $account_node->click('');
        // wait for loading to finish
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $this->waitForLoadingToStop($account_node);
        $account_node->resolver->prefix = $account_css_prefix;
        // account is "active"
        $account_name_class = $account_node->attribute($this->_selector_panel_institutions_accounts_account_name, 'class');
        $this->assertStringContainsString('is-active', $account_name_class);
        // "overview" is NOT active
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $overview_class = $account_node->attribute($this->_selector_panel_institutions.' '.$this->_selector_panel_institutions_overview, 'class');
        $this->assertStringNotContainsString('is-active', $overview_class);
        $account_node->resolver->prefix = $account_css_prefix;

        // entries table has been updated with entries associated with account
        $account_node->with($this->_selector_table.' '.$this->_selector_table_body, function (Browser $table) use ($account_types_collection){
            $table_rows = $table->elements('tr');
            foreach($table_rows as $table_row){
                $row_entry_account_type = $table_row
                    ->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))
                    ->getText();
                $this->assertContains($row_entry_account_type, $account_types_collection->where('disabled', false)->pluck('name')->all());
            }
        });
    }

    /**
     * @param bool $include_inactive_institutions
     * @return Collection
     */
    private function getInstitutionsCollection(bool $include_inactive_institutions = true){
        // make sure we have at least 1 "inactive" institution
        $institutions = $this->getApiInstitutions();
        $institutions_collection = collect($institutions);
        if($include_inactive_institutions){
            if($institutions_collection->where('active', false)->count() == 0){
                $inactive_institution = factory(Institution::class, 1)->create(['active'=>false]);
                $institutions_collection->push($inactive_institution);
            }
        } else {
            $institutions_collection = $institutions_collection->where('active', true);
        }
        return $institutions_collection;
    }

    /**
     * @param Collection $institutions_collection
     * @param bool $include_disabled_accounts
     * @return Collection
     */
    private function getAccountsCollection($institutions_collection, bool $include_disabled_accounts = true){
        // make sure we have at least 1 "disabled" account
        $accounts = $this->getApiAccounts();
        $accounts_collection = collect($accounts);
        if($include_disabled_accounts){
            if($accounts_collection->where('disabled', true)->count() == 0){
                $disabled_account = factory(Account::class, 1)->create(['disabled'=>true, 'institution_id'=>$institutions_collection->random(1)->pluck('id')->first()]);
                $accounts_collection->push($disabled_account);
            }
        } else {
            $accounts_collection = $accounts_collection->where('disabled', false);
        }
        return $accounts_collection;
    }

    /**
     * @return Collection
     */
    private function getAccountTypesCollection(){
        $account_types = $this->getApiAccountTypes();
        return collect($account_types);
    }

}