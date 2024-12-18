<?php

namespace Tests\Browser;

use App\Models\Account;
use App\Models\Institution;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Tooltip as DuskTraitTooltip;
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
    use DuskTraitTooltip;

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 1/20
     */
    public function testOverviewOptionIsVisibleAndActiveByDefault() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->within($this->_selector_panel_institutions, function(Browser $panel) {
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
     * test 2/20
     */
    public function testActiveInstitutionsAreVisibleWithAccountsAndClickingOnAnAccountFiltersEntries() {
        $institutions_collection = $this->getInstitutionsCollection();
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($institutions_collection, $accounts_collection, $account_types_collection) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->within($this->_selector_panel_institutions, function(Browser $panel) use ($institutions_collection, $accounts_collection, $account_types_collection) {
                    $active_institutions_collection = $this->getInstitutionsCollection(false)->sortBy('name');

                    foreach ($active_institutions_collection as $active_institution) {
                        $panel->within('#institution-'.$active_institution['id'], function(Browser $institution_node) use ($active_institution, $accounts_collection, $account_types_collection) {
                            // confirm "accordion" is closed, i.e.: accounts not visible
                            $institution_node->assertMissing($this->_selector_panel_institutions_accounts);

                            // confirm institution name is within collection
                            $institutions_node_name = $institution_node->text($this->_selector_panel_institutions_institution_name);
                            $this->assertEquals($active_institution['name'], $institutions_node_name);

                            $institution_node
                                // click institutions node;
                                ->click('')
                                ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                                // "accordion" is open, i.e.: accounts now visible
                                ->assertVisible($this->_selector_panel_institutions_accounts);

                            $institution_accounts_collection = $accounts_collection
                                ->where('active', true)
                                ->where('institution_id', $active_institution['id'])
                                ->sortBy('name');
                            foreach ($institution_accounts_collection as $institution_account) {
                                $institution_node->within($this->_selector_panel_institutions_accounts.' #account-'.$institution_account['id'], function(Browser $account_node) use ($institution_account, $account_types_collection) {
                                    $account_account_types_collection = $account_types_collection->where('account_id', $institution_account['id']);
                                    $this->assertInstitutionPanelAccountNode($account_node, $institution_account['name'], $account_account_types_collection);
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
     * test 3/20
     */
    public function testDisabledAccountsElementNotVisibleIfNoDisabledAccountsExist() {
        $institutions_collection = $this->getInstitutionsCollection(false);
        $institution_id = $institutions_collection->pluck('id')->random(1)->first();
        DB::table('accounts')->truncate();
        Account::factory()->count(4)->create(['institution_id' => $institution_id]);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_panel_institutions_closed_accounts);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-1
     * test 4/20
     */
    public function testDisabledAccountsAreVisibleAndClickingOnADisabledAccountFiltersEntries() {
        $institutions_collection = $this->getInstitutionsCollection(false);
        $accounts_collection = $this->getAccountsCollection($institutions_collection);
        $disabled_accounts_collection = $accounts_collection->where('active', false);
        $account_types_collection = $this->getAccountTypesCollection();

        $this->browse(function(Browser $browser) use ($disabled_accounts_collection, $account_types_collection) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertVisible($this->_selector_panel_institutions_closed_accounts)
                ->within($this->_selector_panel_institutions_closed_accounts, function(Browser $closed_accounts) use ($disabled_accounts_collection, $account_types_collection) {
                    // confirm the label says "closed accounts"
                    $closed_accounts_label = $closed_accounts->text('');
                    $this->assertEquals("Closed Accounts", $closed_accounts_label);

                    $closed_accounts
                        // confirm "accordion" is closed, i.e.: accounts NOT visible
                        ->assertMissing($this->_selector_panel_institutions_accounts)
                        // click "closed accounts"
                        ->click('')
                        ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                        // confirm "accordion" is open, i.e.: accounts now visible
                        ->assertVisible($this->_selector_panel_institutions_accounts);

                    foreach ($disabled_accounts_collection as $account_data) {
                        $closed_accounts->within($this->_selector_panel_institutions_accounts.' #account-'.$account_data['id'], function(Browser $account_node) use ($account_data, $account_types_collection) {
                            $account_account_types_collection = $account_types_collection->where('account_id', $account_data['id']);
                            $this->assertInstitutionPanelAccountNode($account_node, $account_data['name'], $account_account_types_collection, false);
                            $this->assertInstitutionPanelAccountNodeClickInteraction($account_node, $account_account_types_collection);
                        });
                    }
                });
        });
    }

    public static function providerAccountTotalValueIsTwoDecimalPlaces(): array {
        return [
            ["0.12"],   // test 5/20
            ["0.10"],   // test 6/20
            ["0.01"],   // test 7/20
            ["0.00"],   // test 8/20
            ["-0.01"],  // test 9/20
            ["-0.10"],  // test 10/20
            ["-0.12"],  // test 11/20
        ];
    }

    /**
     * @dataProvider providerAccountTotalValueIsTwoDecimalPlaces
     *
     * @throws Throwable
     *
     * @group navigation-1
     * test (see provider)/20
     */
    public function testAccountTotalValueIsTwoDecimalPlaces(string $test_total) {
        DB::table('institutions')->truncate();
        $new_institution = Institution::factory()->create();
        $institution_id = $new_institution->id;
        DB::table('accounts')->truncate();
        $new_account = Account::factory()->create(['institution_id' => $institution_id, 'total' => $test_total]);
        DB::statement("UPDATE account_types SET account_id=:id", ['id' => $new_account->id]);
        $this->assertEquals($test_total, $new_account->total);

        $this->browse(function(Browser $browser) use ($institution_id, $new_account) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->within($this->_selector_panel_institutions, function(Browser $panel) use ($institution_id, $new_account) {
                    $panel->within("#institution-".$institution_id, function(Browser $institution_node) use ($new_account) {
                        $institution_node
                            ->click('')         // click institutions node;
                            ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                            ->with($this->_selector_panel_institutions_accounts.' #account-'.$new_account->id, function(Browser $account_node) use ($new_account) {
                                // confirm total value is to two decimal places
                                $account_total_text = $account_node->text($this->_selector_panel_institutions_accounts_account_total);
                                $this->assertEquals($new_account->total, $account_total_text);
                                $this->assertEquals(1, preg_match("/\d+\.\d{2}/", $account_total_text));
                            });
                    });
                });
        });
    }

    private function assertAccountNodeName(Browser $account_node, string $account_name): void {
        // confirm account name is within collection
        $account_node_name = $account_node->text($this->_selector_panel_institutions_accounts_account_name);
        $this->assertStringContainsString($account_name, $account_node_name, "account name NOT found within institution-account node");
    }

    private function assertAccountNodeActiveState(Browser $account_node, bool $isActive): void {
        // account is NOT "active"
        $account_node_classes = $account_node->attribute('', 'class');
        if ($isActive) {
            $this->assertStringContainsString($this->_class_is_active, $account_node_classes);
        } else {
            $this->assertStringNotContainsString($this->_class_is_active, $account_node_classes);
        }
    }

    /**
     * @param Collection $account_types_collection
     */
    private function assertInstitutionPanelAccountNode(Browser $account_node, string $account_name, $account_types_collection, bool $has_tooltip = true): void {
        // account is NOT "active"
        $this->assertAccountNodeActiveState($account_node, false);

        // confirm account name is within collection
        $this->assertAccountNodeName($account_node, $account_name);

        // hover over account element
        $this->interactWithElementToTriggerTooltip($account_node, '');

        if ($has_tooltip) {
            // account-types tooltip appears to right
            $this->assertTooltipVisibleByTriggerElement($account_node, '');
            foreach ($account_types_collection as $account_account_type) {
                $account_type_record_tooltip_text = $account_account_type['name']." (".$account_account_type['last_digits'].")";
                if ($account_account_type['active']) {
                    $this->assertStringInTooltipContentsByTriggerElement($account_node, $account_type_record_tooltip_text, '');
                } else {
                    $this->assertStringNotInTooltipContentsByTriggerElement($account_node, $account_type_record_tooltip_text, '');
                }
            }
        } else {
            $this->assertTooltipMissing($account_node);
        }
    }

    /**
     * @param Collection $account_types_collection
     */
    private function assertInstitutionPanelAccountNodeClickInteraction(Browser $account_node, $account_types_collection): void {
        $account_node->click('');
        // wait for loading to finish
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $this->waitForLoadingToStop($account_node);
        $account_node->resolver->prefix = $account_css_prefix;
        // account is "active"
        $this->assertAccountNodeActiveState($account_node, true);
        // "overview" is NOT active
        $account_css_prefix = $account_node->resolver->prefix;
        $account_node->resolver->prefix = '';
        $overview_class = $account_node->attribute($this->_selector_panel_institutions.' '.$this->_selector_panel_institutions_overview, 'class');
        $this->assertStringNotContainsString($this->_class_is_active, $overview_class);
        $account_node->resolver->prefix = $account_css_prefix;

        // entries table has been updated with entries associated with account
        $account_node->within($this->_selector_table.' '.$this->_selector_table_body, function(Browser $table) use ($account_types_collection) {
            $table_rows = $table->elements('tr');
            foreach ($table_rows as $table_row) {
                $row_entry_account_type = $table_row
                    ->findElement(WebDriverBy::cssSelector($this->_selector_table_row_account_type))
                    ->getText();
                $this->assertContains($row_entry_account_type, $account_types_collection->where('disabled', false)->pluck('name')->all());
            }
        });
    }

    /**
     * @return Collection
     */
    private function getInstitutionsCollection(bool $include_inactive_institutions = true) {
        // make sure we have at least 1 "inactive" institution
        $institutions = $this->getApiInstitutions();
        $institutions_collection = collect($institutions);
        if ($include_inactive_institutions) {
            if ($institutions_collection->where('active', false)->count() == 0) {
                $inactive_institution = Institution::factory()->disabled()->count(1)->create();
                $institutions_collection->push($inactive_institution);
            }
        } else {
            $institutions_collection = $institutions_collection->where('active', true);
        }
        return $institutions_collection;
    }

    /**
     * @param Collection $institutions_collection
     * @return Collection
     */
    private function getAccountsCollection($institutions_collection, bool $include_disabled_accounts = true) {
        // make sure we have at least 1 "disabled" account
        $accounts = $this->getApiAccounts();
        $accounts_collection = collect($accounts);
        if ($include_disabled_accounts) {
            if ($accounts_collection->where('active', false)->count() == 0) {
                $disabled_account = Account::factory()->count(1)->disabled()->create(['institution_id' => $institutions_collection->random(1)->pluck('id')->first()]);
                $accounts_collection->push($disabled_account);
            }
        } else {
            $accounts_collection = $accounts_collection->where('active', true);
        }
        return $accounts_collection;
    }

    /**
     * @return Collection
     */
    private function getAccountTypesCollection() {
        $account_types = $this->getApiAccountTypes();
        return collect($account_types);
    }

}
