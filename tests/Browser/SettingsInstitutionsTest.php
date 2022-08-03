<?php

namespace Tests\Browser;

use App\Models\Institution;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\WaitTimes;
use App\Traits\Tests\WithTailwindColors;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;

/**
 * @group settings
 * @group settings-institutions
 */
class SettingsInstitutionsTest extends DuskTestCase {

    use DuskTraitLoading;
    use DuskTraitNotification;
    use DuskTraitToggleButton;
    use WaitTimes;
    use WithFaker;
    use WithTailwindColors;

    private static string $SELECTOR_PRIMARY_DIV = '#app-settings';

    private static string $SELECTOR_SETTINGS_NAV = '#settings-nav';
    private static string $SELECTOR_SETTINGS_NAV_HEADER = '#settings-panel-header';
    private static string $SELECTOR_SETTINGS_NAV_ACTIVE = 'li.settings-nav-option.is-active';
    private static string $SELECTOR_SETTINGS_NAV_INSTITUTIONS = 'li.settings-nav-option:nth-child(1)';
    private static string $SELECTOR_SETTINGS_NAV_ACCOUNTS = 'li.settings-nav-option:nth-child(2)';
    private static string $SELECTOR_SETTINGS_NAV_ACCOUNT_TYPES = 'li.settings-nav-option:nth-child(3)';
    private static string $SELECTOR_SETTINGS_NAV_TAGS = 'li.settings-nav-option:nth-child(4)';

    private static string $SELECTOR_SETTINGS_DISPLAY = '#settings-display';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT = 'section#settings-default';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS = 'section#settings-institutions';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNTS = 'section#settings-accounts';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_ACCOUNT_TYPES = 'section#settings-account-types';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS = 'section#settings-tags';

    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_NAME = "label[for='settings-institution-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME = 'input#settings-institution-name:nth-child(2)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_ACTIVE = "label:nth-child(3)";
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE = '#settings-institution-active';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED = 'div:nth-child(5)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED = 'div:nth-child(6)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED = 'div:nth-child(7)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED = 'div:nth-child(8)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR = 'button:nth-child(9)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE = 'button:nth-child(10)';
    private static string $TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID = '#institution-%d';

    private static string $SELECTOR_SETTINGS_LOADING_INSTITUTIONS =  '#loading-institutions';

    private static string $LABEL_SETTINGS_INSTITUTION_TOGGLE_ACTIVE = 'Active';
    private static string $LABEL_SETTINGS_INSTITUTION_TOGGLE_INACTIVE = 'Inactive';
    private static string $LABEL_SETTINGS_INSTITUTION_LABEL_CREATED = 'Created:';
    private static string $LABEL_SETTINGS_INSTITUTION_LABEL_MODIFIED = 'Modified:';
    private static string $LABEL_SETTINGS_INSTITUTION_BUTTON_CLEAR = 'Clear';
    private static string $LABEL_SETTINGS_INSTITUTION_BUTTON_SAVE = 'Save';
    private static string $LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_NEW = 'New Institution created';
    private static string $LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_UPDATE = 'Institution updated';

    private static int $TOGGLE_TIME_MILLISECONDS = 75;

    private string $color_toggle_active;
    private string $color_toggle_inactive;
    private string $color_button_save;

    public function setUp(): void{
        parent::setUp();
        $this->color_toggle_active = $this->tailwindColors->blue(600);
        $this->color_toggle_inactive = $this->tailwindColors->gray(400);
        $this->color_button_save = $this->tailwindColors->green(500);
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 1/25
     */
    public function testDefaultSettingsPageState(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new SettingsPage())
                ->assertVisible(self::$SELECTOR_SETTINGS_NAV)
                ->within(self::$SELECTOR_SETTINGS_NAV, function(Browser $side_panel){
                    $side_panel
                        ->assertVisible(self::$SELECTOR_SETTINGS_NAV_HEADER)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_HEADER, 'Settings')
                        ->assertMissing(self::$SELECTOR_SETTINGS_NAV_ACTIVE);
                })
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $settings_display
                        ->assertVisible(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT.' h1', 'Settings')
                        ->assertVisible(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT.' svg');
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutiuons-1
     * test 2/25
     */
    public function testNavigateToInstitutionSettingsAndAssertForm(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $selector_form_header = 'h3';

                            $section
                                ->assertSeeIn($selector_form_header, 'Institutions')

                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_NAME, 'Name:')
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME)
                                ->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, '')

                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_ACTIVE, 'Active State:')
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
                            $this->assertToggleButtonState($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE, self::$LABEL_SETTINGS_INSTITUTION_TOGGLE_ACTIVE, $this->color_toggle_active);

                            $section
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED)

                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR)
                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR, self::$LABEL_SETTINGS_INSTITUTION_BUTTON_CLEAR)

                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE)
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE.' svg')
                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, self::$LABEL_SETTINGS_INSTITUTION_BUTTON_SAVE);
                            $this->assertElementBackgroundColor($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, $this->color_button_save);
                            $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
                            $this->assertEquals('true', $save_button_state);
                        });
                });
        });
    }

    /**
     * @return void
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 3/25
     */
    public function testInstitutionsVisible(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_INSTITUTIONS, self::$WAIT_SECONDS);

                            $institutions = Institution::all();
                            $this->assertCount($institutions->count(), $section->elements('hr~ul li'));
                            foreach ($institutions as $institution){
                                $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID, $institution->id);
                                $section
                                    ->assertVisible($selector_institution_id)
                                    ->assertSeeIn($selector_institution_id, $institution->name);
                                $class_is_disabled = 'is-disabled';
                                $institution_node_classes = $section->attribute($selector_institution_id, 'class');
                                if($institution->active){
                                    $this->assertStringNotContainsString($class_is_disabled, $institution_node_classes);
                                } else {
                                    $this->assertStringContainsString($class_is_disabled, $institution_node_classes);
                                }
                            }
                        });
                })
            ;
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 4/25
     */
    public function testFormFieldInteractionAndClearButtonFunctionality(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $section->type(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, $this->faker->word());
                            $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, '');
                            $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
                            $this->assertToggleButtonState($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE, self::$LABEL_SETTINGS_INSTITUTION_TOGGLE_INACTIVE, $this->color_toggle_inactive);

                            $save_button_disabled_state = $section->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
                            $this->assertEmpty($save_button_disabled_state);    // if not disabled, the attribute isn't even available

                            $this->clickClearButton($section);
                            $this->assertFormDefaults($section);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 5/25
     */
    public function testClickExistingInstitutionDisplaysDataInFormAndClearingFormThenReclickSameInstitution(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_INSTITUTIONS, self::$WAIT_SECONDS);

                            $institution = Institution::get()->random();
                            $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID.' span', $institution->id);
                            $section->click($selector);

                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                            });

                            $this->assertFormWithExistingData($section, $institution);
                            $this->clickClearButton($section);

                            $this->assertFormDefaults($section);
                            $section
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED);

                            $section->click($selector);
                            $this->assertFormWithExistingData($section, $institution);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 6/25
     */
    public function testSaveNewInstitution(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $institutions = Institution::all();
                            do{
                                $institution_name = $this->faker->word();
                            } while($institutions->contains('name', $institution_name));

                            $section->type(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, $institution_name);
                            $is_institution_active = $this->faker->boolean();
                            if(!$is_institution_active){
                                $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
                            }

                            $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
                            $this->assertEmpty($save_button_state);
                            $section->click(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE);

                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                                $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_NEW);
                                $this->dismissNotification($body);
                            });

                            $this->assertFormDefaults($section);

                            $new_institution = Institution::all()->diff($institutions)->first();
                            $this->assertEquals($is_institution_active, $new_institution->active);
                            $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID.'%s', $new_institution->id, ($is_institution_active?'.is-active':'.is-disabled'));
                            $section
                                ->assertVisible($selector)
                                ->click($selector.' span');
                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                            });
                            $this->assertFormWithExistingData($section, $new_institution);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 7/25
     */
    public function testSaveExistingInstitution(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                    $this->assertInstitutionsSettingsDisplayed($settings_display);
                    $settings_display
                        ->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                            $this->assertFormDefaults($section);
                            $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_INSTITUTIONS, self::$WAIT_SECONDS);

                            $institution = Institution::all()->random();
                            $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID, $institution->id);
                            $section
                                ->assertVisible($selector_institution_id)
                                ->click($selector_institution_id.' span');

                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                            });

                            $this->assertFormWithExistingData($section, $institution);

                            $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE, self::$TOGGLE_TIME_MILLISECONDS);
                            $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
                            $this->assertEmpty($save_button_state);

                            $section->click(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE);

                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                                $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_UPDATE);
                                $this->dismissNotification($body);
                            });
                            $this->assertFormDefaults($section);

                            $institution = Institution::find($institution->id); // get updated institution data
                            $institution_class_state = $institution->active ? '.is-active' : '.is-disabled';
                            $section->assertVisible($selector_institution_id.$institution_class_state);
                            $section->click($selector_institution_id.' span');
                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                            });

                            $this->assertFormWithExistingData($section, $institution);
                        });
                });
        });
    }

    private function navigateToInstitutionsSettingsOnSettingsPage(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_NAV)
            ->within(self::$SELECTOR_SETTINGS_NAV, function(Browser $side_panel){
                $side_panel
                    ->assertMissing(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertVisible(self::$SELECTOR_SETTINGS_NAV_INSTITUTIONS)
                    ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_INSTITUTIONS, 'Institutions')
                    ->click(self::$SELECTOR_SETTINGS_NAV_INSTITUTIONS)
                    ->assertVisible(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_ACTIVE, 'Institutions');
            });
    }

    private function assertInstitutionsSettingsDisplayed(Browser $settings_display){
        $settings_display
            ->assertMissing(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
            ->assertVisible(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS);
    }

    /**
     * @param Browser $browser
     *
     * Form defaults:
     *   Name: (empty)
     *   Active State: "Active"
     *   Save button [disabled]
     */
    private function assertFormDefaults(Browser $browser){
        $browser->pause(self::$TOGGLE_TIME_MILLISECONDS);   // allow time for toggle to transition
        $browser->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, '');
        $this->assertToggleButtonState($browser, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE, self::$LABEL_SETTINGS_INSTITUTION_TOGGLE_ACTIVE, $this->color_toggle_active);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);
    }

    private function assertFormWithExistingData(Browser $browser, Institution $institution){
        $browser->pause(self::$TOGGLE_TIME_MILLISECONDS);   // allow time for toggle to transition
        $browser->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, $institution->name);
        $toggle_label = $institution->active ? self::$LABEL_SETTINGS_INSTITUTION_TOGGLE_ACTIVE : self::$LABEL_SETTINGS_INSTITUTION_TOGGLE_INACTIVE;
        $toggle_color = $institution->active ? $this->color_toggle_active : $this->color_toggle_inactive;
        $this->assertToggleButtonState($browser, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE, $toggle_label, $toggle_color);

        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED, $this->convertDateToECMA262Format($institution->create_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED, $this->convertDateToECMA262Format($institution->modified_stamp));

        $save_button_disabled_state = $browser->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals('true', $save_button_disabled_state);    // no changes; so button remains disabled
    }

    private function convertDateToECMA262Format(string $date):string{
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toString
        // ECMA-262 datetime format
        return Carbon::create($date)->format('D M d Y H:i:s \G\M\TO');
    }

    private function clickClearButton(Browser $browser){
        $browser
            ->click(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR);
    }

}
