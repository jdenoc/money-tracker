<?php

namespace Tests\Browser;

use App\Account;
use App\Institution;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Tests\Traits\HomePageSelectors;

class InstitutionsPanelTest extends DuskTestCase {

    use HomePageSelectors;
    use DatabaseMigrations;

    public function testOverviewOptionIsVisibleAndActiveByDefault(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_panel_institutions, function($panel){
                    $panel
                        ->assertSeeIn(".panel-heading", "Institutions")
                        ->assertVisible($this->_selector_panel_institutions_overview)
                        ->assertSeeIn($this->_selector_panel_institutions_overview, "Overview");

                    $overview_class = $panel->attribute($this->_selector_panel_institutions_overview, 'class');
                    $this->assertContains($this->_class_is_active, $overview_class);
                });
        });
    }

    public function testActiveInstitutionsAreVisibleWithAccountsAndClickingOnAnAccountFiltersEntries(){
        $institutions_collection = $this->getInstitutionsCollection();
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($institutions_collection, $accounts_collection, $account_types_collection){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_panel_institutions, function($panel) use ($institutions_collection, $accounts_collection, $account_types_collection){
                    $active_institutions_collection = $institutions_collection
                        ->where('active', true)
                        ->sortBy('name');

                    foreach($active_institutions_collection as $active_institution){
                        $panel->with('#institution-'.$active_institution['id'], function($institution_node) use ($active_institution, $accounts_collection, $account_types_collection){
                            // confirm "accordion" is closed
                            $open_close_node_class = $institution_node->attribute($this->_selector_panel_institutions_institution_open_close, 'class');
                            $this->assertContains('fa-chevron-down', $open_close_node_class);

                            // confirm institution name is within collection
                            $institutions_node_name = $institution_node->text($this->_selector_panel_institutions_institution_name);
                            $this->assertEquals($active_institution['name'], $institutions_node_name);

                            $institution_node
                                // confirm accounts NOT visible
                                ->assertMissing($this->_selector_panel_institutions_accounts)
                                // click institutions node;
                                ->click('')
                                ->pause(400)    // 0.4 seconds
                                // accounts now visible
                                ->assertVisible($this->_selector_panel_institutions_accounts);

                            // confirm "accordion" is open
                            $open_close_node_class = $institution_node->attribute($this->_selector_panel_institutions_institution_open_close, 'class');
                            $this->assertContains('fa-chevron-up', $open_close_node_class);

                            $institution_accounts_collection = $accounts_collection
                                ->where('disabled', false)
                                ->where('institution_id', $active_institution['id'])
                                ->sortBy('name');
                            foreach($institution_accounts_collection as $institution_account){
                                $institution_node->with($this->_selector_panel_institutions_accounts.' #account-'.$institution_account['id'], function($account_node) use ($institution_account, $account_types_collection){
                                    $this->assertInstitutionPanelAccountNode($account_node, $institution_account, $account_types_collection);
                                });
                            }
                        });
                    }
                });
        });
    }

    public function testDisabledAccountsElementNotVisibleIfNoDisabledAccountsExist(){
        $institutions_collection = $this->getInstitutionsCollection(false);
        $institution_id = $institutions_collection->pluck('id')->random(1)->first();
        DB::statement("TRUNCATE accounts");
        factory(Account::class, 3)->create(['disabled'=>0, 'institution_id'=>$institution_id]);

        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing("#closed-accounts");
        });
    }

    public function testDisabledAccountsAreVisibleAndClickingOnADisabledAccountFiltersEntries(){
        $institutions_collection = $this->getInstitutionsCollection(false);
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $disabled_accounts_collection = $accounts_collection->where('disabled', true);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($disabled_accounts_collection, $account_types_collection){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertVisible("#closed-accounts")
                ->with("#closed-accounts", function($closed_accounts) use ($disabled_accounts_collection, $account_types_collection){
                    // confirm the label says "closed accounts"
                    $closed_accounts_label = $closed_accounts->text('span.name-label');
                    $this->assertEquals("Closed Accounts", $closed_accounts_label);

                    // confirm "accordion" is closed
                    $open_close_node_class = $closed_accounts->attribute('span.panel-icon i', 'class');
                    $this->assertContains('fa-chevron-up', $open_close_node_class);

                    $closed_accounts
                        // confirm accounts NOT visible
                        ->assertMissing($this->_selector_panel_institutions_accounts)
                        // click "closed accounts"
                        ->click('')
                        ->pause(400)    // 0.4 seconds
                        // accounts now visible
                        ->assertVisible($this->_selector_panel_institutions_accounts);

                    // confirm "accordion" is open
                    $open_close_node_class = $closed_accounts->attribute('span.panel-icon i', 'class');
                    $this->assertContains('fa-chevron-down', $open_close_node_class);

                    foreach($disabled_accounts_collection as $account_data){
                        $closed_accounts->with($this->_selector_panel_institutions_accounts.' #account-'.$account_data['id'], function($account_node) use ($account_data, $account_types_collection){
                            $this->assertInstitutionPanelAccountNode($account_node, $account_data, $account_types_collection, false);
                        });
                    }
                });
        });
    }

    /**
     * @param Browser $account_node
     * @param array $account_data
     * @param Collection $account_types_collection
     * @param boolean $has_tooltip
     */
    private function assertInstitutionPanelAccountNode($account_node, $account_data, $account_types_collection, $has_tooltip=true){
        // confirm account name is within collection
        $account_node_name = $account_node->text($this->_selector_panel_institutions_accounts_account_name.' span:first-child');
        $this->assertContains($account_data['name'], $account_node_name);

        $account_account_types = $account_types_collection->where('disabled', false)
            ->where('account_id', $account_data['id']);

        // hover over account element
        $account_node->mouseover('');

        if($has_tooltip){
            // account-types tooltip appears to right
            $account_node_tooltip_id = $account_node->attribute('', 'aria-describedby');    // get the tooltip element id
            $account_node->assertVisible('#'.$account_node_tooltip_id);
            $account_types_tooltip_text = $account_node->text('#'.$account_node_tooltip_id);
            foreach($account_account_types as $account_account_type){
                $this->assertContains($account_account_type['name'], $account_types_tooltip_text);
            }
        } else {
            $account_css_prefix = $account_node->resolver->prefix;
            $account_node->resolver->prefix = '';
            $account_node->assertMissing('body .tooltip');
            $account_node->resolver->prefix = $account_css_prefix;
        }

        // account is NOT "active"
        $account_name_class = $account_node->attribute($this->_selector_panel_institutions_accounts_account_name, 'class');
        $this->assertNotContains('is-active', $account_name_class);
        $account_node->click('');
        // wait for loading to finish
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $account_node->waitForLoadingToStop();
        $account_node->resolver->prefix = $account_css_prefix;
        // account is "active"
        $account_name_class = $account_node->attribute($this->_selector_panel_institutions_accounts_account_name, 'class');
        $this->assertContains('is-active', $account_name_class);
        // "overview" is NOT active
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $overview_class = $account_node->attribute($this->_selector_panel_institutions.' '.$this->_selector_panel_institutions_overview, 'class');
        $this->assertNotContains('is-active', $overview_class);
        $account_node->resolver->prefix = $account_css_prefix;

        // entries table has been updated with entries associated with account
        $account_node->with($this->_selector_table.' '.$this->_selector_table_body, function ($table) use ($account_account_types){
            $table_rows = $table->elements('tr');
            foreach($table_rows as $table_row){
                $row_entry_account_type = $table_row
                    ->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))
                    ->getText();
                $this->assertContains($row_entry_account_type, $account_account_types->pluck('name')->all());
            }
        });
    }

    /**
     * @param bool $include_inactive_institutions
     * @return Collection
     */
    private function getInstitutionsCollection($include_inactive_institutions = true){
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
    private function getAccountsCollection($institutions_collection, $include_disabled_accounts = true){
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