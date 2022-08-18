<?php

namespace Tests\Browser;

use App\Models\Institution;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;

/**
 * @group settings
 * @group settings-institutions
 */
class SettingsInstitutionsTest extends SettingsBaseTest {

    use DuskTraitToggleButton;

    private static string $SELECTOR_SETTINGS_NAV_INSTITUTIONS = 'li.settings-nav-option:nth-child(1)';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS = 'section#settings-institutions';

    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_NAME = "label[for='settings-institution-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME = 'input#settings-institution-name:nth-child(2)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_ACTIVE = "label:nth-child(3)";
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE = 'div:nth-child(4) #settings-institution-active';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED = 'div:nth-child(5)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED = 'div:nth-child(6)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED = 'div:nth-child(7)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED = 'div:nth-child(8)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR = 'button:nth-child(9)';
    private static string $SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE = 'button:nth-child(10)';
    private static string $TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID = '#settings-institution-%d';

    private static string $SELECTOR_SETTINGS_LOADING_INSTITUTIONS =  '#loading-settings-institutions';

    private static string $LABEL_SETTINGS_INSTITUTION = 'Institutions';
    private static string $LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_NEW = 'New Institution created';
    private static string $LABEL_SETTINGS_INSTITUTION_NOTIFICATION_INSTITUTION_UPDATE = 'Institution updated';

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
                            $section
                                ->assertSeeIn(self::$SELECTOR_SETTINGS_HEADER, self::$LABEL_SETTINGS_INSTITUTION)

                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_NAME, self::$LABEL_INPUT_NAME)
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME)
                                ->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, '')

                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_ACTIVE, self::$LABEL_INPUT_ACTIVE_STATE)
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
                            $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);

                            $section
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED)
                                ->assertMissing(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED)

                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR)
                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR, self::$LABEL_BUTTON_CLEAR)

                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE)
                                ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE.' svg')
                                ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, self::$LABEL_BUTTON_SAVE);
                            $this->assertElementBackgroundColor($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, $this->color_button_save);
                            $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
                            $this->assertEquals('true', $save_button_state);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group settings-institutions-1
     * test 3/25
     */
    public function testInstitutionsListedUnderFormAreVisible(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToInstitutionsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertInstitutionsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_INSTITUTIONS, self::$WAIT_SECONDS);

                    $institutions = Institution::all();
                    $this->assertCount($institutions->count(), $section->elements('hr~ul li'));
                    foreach ($institutions as $institution){
                        $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID, $institution->id);
                        $section
                            ->assertVisible($selector_institution_id)
                            ->assertSeeIn($selector_institution_id, $institution->name);
                        $class_is_disabled = 'is-disabled';
                        $class_is_active = 'is-active';
                        $institution_node_classes = $section->attribute($selector_institution_id, 'class');
                        if($institution->active){
                            $this->assertStringNotContainsString($class_is_disabled, $institution_node_classes);
                            $this->assertStringContainsString($class_is_active, $institution_node_classes);
                        } else {
                            $this->assertStringNotContainsString($class_is_active, $institution_node_classes);
                            $this->assertStringContainsString($class_is_disabled, $institution_node_classes);
                        }
                    }
                });
            });
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
                            $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);

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

                            $institution = Institution::get()->random();
                            $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_INSTITUTION_INSTITUTION_ID, $institution->id);
                            $section
                                ->assertVisible($selector_institution_id)
                                ->click($selector_institution_id.' span');

                            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                                $this->waitForLoadingToStop($body);
                            });

                            $this->assertFormWithExistingData($section, $institution);

                            $this->toggleToggleButton($section, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
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
        $this->navigateToSettingsSectionOnSettingsPage($browser, self::$SELECTOR_SETTINGS_NAV_INSTITUTIONS, self::$LABEL_SETTINGS_INSTITUTION);
    }

    private function assertInstitutionsSettingsDisplayed(Browser $settings_display){
        $this->assertSettingsSectionDisplayed($settings_display, self::$SELECTOR_SETTINGS_DISPLAY_SECTION_INSTITUTIONS);
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
        $browser->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, '');
        $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);
    }

    private function assertFormWithExistingData(Browser $browser, Institution $institution){
        $browser->assertInputValue(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_INPUT_NAME, $institution->name);
        if($institution->active){
            $this->assertActiveStateToggleActive($browser, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleInactive($browser, self::$SELECTOR_SETTINGS_INSTITUTION_FORM_TOGGLE_ACTIVE);
        }

        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_CREATED, $this->convertDateToECMA262Format($institution->create_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_MODIFIED, $this->convertDateToECMA262Format($institution->modified_stamp));

        $save_button_disabled_state = $browser->attribute(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals('true', $save_button_disabled_state);    // no changes; so button remains disabled
    }

    private function clickClearButton(Browser $browser){
        $browser->click(self::$SELECTOR_SETTINGS_INSTITUTION_FORM_BUTTON_CLEAR);
    }

}
