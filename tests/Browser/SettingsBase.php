<?php

namespace Tests\Browser;

use App\Models\BaseModel;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Notification as DuskTraitNotification;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use App\Traits\Tests\WaitTimes;
use App\Traits\Tests\WithTailwindColors;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Throwable;

abstract class SettingsBase extends DuskTestCase {
    use DuskTraitToggleButton;
    use DuskTraitLoading;
    use DuskTraitNotification;
    use WaitTimes;
    use WithFaker;
    use WithTailwindColors;

    protected static string $SELECTOR_PRIMARY_DIV = '#app-settings';

    protected static string $SELECTOR_SETTINGS_NAV = '#settings-nav';
    private static string $SELECTOR_SETTINGS_NAV_HEADER = '#settings-panel-header';
    private static string $SELECTOR_SETTINGS_NAV_ACTIVE = 'li.settings-nav-option.is-active';
    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = '';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = '';

    protected static string $SELECTOR_SETTINGS_DISPLAY = '#settings-display';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT = 'section#settings-default';
    protected static string $SELECTOR_SETTINGS_HEADER = 'h3';
    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = '';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = '';

    protected static string $LABEL_SETTINGS_FORM_INPUT_NAME = 'Name:';
    protected static string $LABEL_SETTINGS_FORM_LABEL_ACTIVE = 'Active State:';
    protected static string $LABEL_SETTINGS_LABEL_CREATED = "Created:";
    protected static string $LABEL_SETTINGS_LABEL_MODIFIED = "Modified:";
    protected static string $LABEL_SETTINGS_LABEL_DISABLED = "Disabled:";

    protected static string $SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE = '';
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = '';
    protected static string $LABEL_SETTINGS_FORM_BUTTON_CLEAR = "Clear";
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = '';
    private static string $LABEL_SETTINGS_FORM_BUTTON_SAVE = "Save";

    protected static string $SELECTOR_SETTINGS_LOADING_NODES = '';
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = '';

    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = '';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = '';
    protected static string $LABEL_SETTINGS_NOTIFICAITON_RESTORE = '';
    protected static string $LABEL_SETTINGS_NOTIFICAITON_DELETE = '';

    private string $color_button_save;

    public function setUp(): void {
        parent::setUp();
        $this->assertConstantsSet();
        $this->initSettingsColors();
    }

    // ------------ ------------ ------------
    // ------------ tests        ------------
    // ------------ ------------ ------------

    /**
     * @throws Throwable
     *
     * test 1/20
     */
    public function testDefaultSettingsPageState() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new SettingsPage())
                ->assertVisible(self::$SELECTOR_SETTINGS_NAV)
                ->within(self::$SELECTOR_SETTINGS_NAV, function(Browser $side_panel) {
                    $side_panel
                        ->assertVisible(self::$SELECTOR_SETTINGS_NAV_HEADER)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_HEADER, 'Settings')
                        ->assertMissing(self::$SELECTOR_SETTINGS_NAV_ACTIVE);
                })
                ->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                    $settings_display
                        ->assertVisible(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT.' h1', 'Settings')
                        ->assertVisible(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT.' svg');
                });
        });
    }

    /**
     * @throws Throwable
     *
     * test 2/20
     */
    public function testNavigateToSpecificSettingsAndAssertForm() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $section->assertSeeIn(self::$SELECTOR_SETTINGS_HEADER, static::$LABEL_SETTINGS_SECTION_HEADER);
                    $this->assertFormDefaultsFull($section);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 3/20
     */
    public function testObjectListItemsListedUnderFormAreVisible() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);
                    $this->assertObjectListItemsVisible($section);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 4/20
     */
    public function testFormFieldInteractionAndClearButtonFunctionality() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $this->fillForm($section);
                    $this->assertSaveButtonEnabled($section);

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 5/20
     */
    public function testClickExistingNodeWillDisplayDataInFormThenClearFormAndReclickSameNode() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);

                    $node = $this->getObject();
                    $this->interactWithObjectListItem($section, $node);
                    $this->assertFormWithExistingData($section, $node);

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);

                    $this->interactWithObjectListItem($section, $node, false);
                    $this->assertFormWithExistingData($section, $node);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 6/20
     */
    public function testSaveNewSettingNode() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $nodes = $this->getAllObjects();
                    $this->fillForm($section);

                    $this->assertSaveButtonEnabled($section);
                    $this->clickSaveButton($section);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICATION_NEW);
                        $this->dismissNotification($body);
                    });

                    $this->assertFormDefaults($section);

                    $new_node =$this->getAllObjects()->diff($nodes)->first();
                    $this->interactWithObjectListItem($section, $new_node);
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $new_node);
                });
            });
        });
    }

    /**
     * @dataProvider providerSaveExistingSettingNode
     * @throws Throwable
     *
     * test ?/20
     */
    public function testSaveExistingSettingNode(string $form_element) {
        $this->browse(function(Browser $browser) use ($form_element) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) use ($form_element) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) use ($form_element) {
                    $this->assertFormDefaults($section);
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);

                    $node = $this->getObject();
                    $this->interactWithObjectListItem($section, $node);
                    $this->assertFormWithExistingData($section, $node);

                    $this->interactWithFormElement($section, $form_element, $node);

                    $this->assertSaveButtonEnabled($section);
                    $this->clickSaveButton($section);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICATION_UPDATE);
                        $this->dismissNotification($body);
                    });
                    $this->assertFormDefaults($section);

                    $node = $this->getObject($node->id);    // get updated data
                    $this->interactWithObjectListItem($section, $node);
                    $this->assertFormWithExistingData($section, $node);
                });
            });
        });
    }

    /**
     * @dataProvider providerDisablingOrRestoringAccount
     * @throws \Throwable
     *
     * test ?/25
     */
    public function testDisablingOrRestoringAccount(bool $isInitObjectActive) {
        // TODO: remove as soon as other objects have been updated
        if (!in_array(get_class(), ['SettingsAccountsTest', 'SettingsInstitutionsTest'])) {
            $this->markTestSkipped();
        }

        $generated_object = $this->generateObject($isInitObjectActive);
        $this->assertEquals($generated_object->active, $isInitObjectActive);

        $this->browse(function(Browser $browser) use ($generated_object, $isInitObjectActive) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) use ($generated_object, $isInitObjectActive) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) use ($generated_object, $isInitObjectActive) {
                    $this->assertFormDefaults($section);
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);

                    $this->interactWithObjectListItem($section, $generated_object);
                    $this->assertFormWithExistingData($section, $generated_object);

                    $this->interactWithFormElement($section, static::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE, $generated_object);
                    if ($isInitObjectActive) {
                        $this->assertActiveStateToggleInactive($section, static::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
                        $is_object_active = false;
                    } else {
                        $this->assertActiveStateToggleActive($section, static::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
                        $is_object_active = true;
                    }

                    $this->assertSaveButtonEnabled($section);
                    $this->clickSaveButton($section);
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) use ($is_object_active) {
                        if ($is_object_active) {
                            $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICAITON_RESTORE);
                            $this->dismissNotification($body);
                            $this->waitForLoadingToStop($body);
                            $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICATION_UPDATE);
                            $this->dismissNotification($body);
                        } else {
                            $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICAITON_DELETE);
                            $this->dismissNotification($body);
                            $this->waitForLoadingToStop($body);
                        }
                    });
                    $this->assertFormDefaults($section);

                    $updated_object = $this->getObject($generated_object->id);
                    $this->assertEquals($updated_object->active, $is_object_active);

                    // should now be disabled/re-enabled
                    $this->interactWithObjectListItem($section, $updated_object);
                    $this->assertFormWithExistingData($section, $updated_object);
                });
            });
        });
    }

    // ------------ ------------ ------------
    // ------------ to override  ------------
    // ------------ ------------ ------------

    abstract protected function assertFormDefaults(Browser $section);

    abstract protected function assertFormDefaultsFull(Browser $section);

    abstract protected function assertFormWithExistingData(Browser $section, BaseModel $object);

    abstract protected function assertObjectListItemsVisible(Browser $section);

    abstract protected function fillForm(Browser $section);

    abstract protected function generateObject(bool $isInitObjectActive): BaseModel;

    abstract protected function getObject(int $id=null): BaseModel;

    abstract protected function getAllObjects(): Collection;

    abstract protected function interactWithFormElement(Browser $section, string $selector, BaseModel $object=null);

    abstract protected function interactWithObjectListItem(Browser $section, BaseModel $object, bool $is_fresh_load=true);

    abstract public function providerDisablingOrRestoringAccount(): array;

    abstract public function providerSaveExistingSettingNode(): array;

    // ------------ ------------ ------------
    // ------------ asserts      ------------
    // ------------ ------------ ------------

    protected function assertConstantsSet() {
        $constants_to_be_set = [
            'SELECTOR_SETTINGS_NAV_ELEMENT'=>static::$SELECTOR_SETTINGS_NAV_ELEMENT,
            'LABEL_SETTINGS_NAV_ELEMENT'=>static::$LABEL_SETTINGS_NAV_ELEMENT,

            'SELECTOR_SETTINGS_DISPLAY_SECTION'=>static::$SELECTOR_SETTINGS_DISPLAY_SECTION,
            'LABEL_SETTINGS_SECTION_HEADER'=>static::$LABEL_SETTINGS_SECTION_HEADER,

            'SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE'=>static::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE,
            'SELECTOR_SETTINGS_FORM_BUTTON_CLEAR'=>static::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR,
            'SELECTOR_SETTINGS_FORM_BUTTON_SAVE'=>static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE,

            'SELECTOR_SETTINGS_LOADING_NODES'=>static::$SELECTOR_SETTINGS_LOADING_NODES,
            'TEMPLATE_SELECTOR_SETTINGS_NODE_ID'=>static::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID,

            'LABEL_SETTINGS_NOTIFICATION_NEW'=>static::$LABEL_SETTINGS_NOTIFICATION_NEW,
            'LABEL_SETTINGS_NOTIFICATION_UPDATE'=>static::$LABEL_SETTINGS_NOTIFICATION_UPDATE,
            'LABEL_SETTINGS_NOTIFICAITON_RESTORE'=>static::$LABEL_SETTINGS_NOTIFICAITON_RESTORE,
            'LABEL_SETTINGS_NOTIFICAITON_DELETE'=>static::$LABEL_SETTINGS_NOTIFICAITON_DELETE,
        ];

        foreach ($constants_to_be_set as $constant_name=>$constant_value) {
            $this->assertNotEmpty($constant_value, sprintf("The constant %s must have a value set", $constant_name));
        }
    }

    protected function assertActiveStateToggleActive(Browser $settings_display, $toggle_selector) {
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Active', $this->tailwindColors->blue(600));
    }

    protected function assertActiveStateToggleInactive(Browser $settings_display, $toggle_selector) {
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Inactive', $this->tailwindColors->gray(400));
    }

    protected function assertObjectIsOfType($object, string $type) {
        $this->assertTrue(
            get_class($object) === $type,
            sprintf("object of type [%s] was incorrectly provided", get_class($object))
        );
    }

    protected function assertSaveButtonDefault(Browser $section) {
        $section
            ->assertVisible(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE)
            ->assertVisible(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE.' svg')
            ->assertSeeIn(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE, self::$LABEL_SETTINGS_FORM_BUTTON_SAVE);
        $this->assertElementBackgroundColor($section, static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE, $this->color_button_save);
        $this->assertSaveButtonDisabled($section);
    }

    protected function assertSaveButtonDisabled(Browser $section) {
        $save_button_state = $section->attribute(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals('true', $save_button_state, "Save button appears to NOT be disabled");    // no changes; so button remains disabled
    }

    protected function assertSaveButtonEnabled(Browser $section) {
        $save_button_state = $section->attribute(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEmpty($save_button_state, "Save button appears to NOT be enabled");
    }

    protected function assertSettingsSectionDisplayed(Browser $settings_display) {
        $settings_display
            ->assertMissing(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
            ->assertVisible(static::$SELECTOR_SETTINGS_DISPLAY_SECTION);
    }

    // ------------ ------------ ------------
    // ------------ utilities    ------------
    // ------------ ------------ ------------

    private function clickSaveButton(Browser $section) {
        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)
            ->click(static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE);
    }

    private function clickClearButton(Browser $browser) {
        $browser
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)
            ->click(static::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR)
            ->pause($this->toggleButtonTransitionTimeInMilliseconds());   // wait for toggle button transition to complete;
    }

    protected function convertDateToECMA262Format(string $date): string {
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toString
        // ECMA-262 datetime format
        return Carbon::create($date)->format('D M d Y H:i:s \G\M\TO');
    }

    protected function initSettingsColors() {
        $this->color_button_save = $this->tailwindColors->green(500);
    }

    protected function navigateToSettingsSectionOnSettingsPage(Browser $browser) {
        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_NAV)
            ->within(self::$SELECTOR_SETTINGS_NAV, function(Browser $side_panel) {
                $side_panel
                    ->assertMissing(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertVisible(static::$SELECTOR_SETTINGS_NAV_ELEMENT)
                    ->assertSeeIn(static::$SELECTOR_SETTINGS_NAV_ELEMENT, static::$LABEL_SETTINGS_NAV_ELEMENT)
                    ->click(static::$SELECTOR_SETTINGS_NAV_ELEMENT)
                    ->assertVisible(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_ACTIVE, static::$LABEL_SETTINGS_NAV_ELEMENT);
            });
    }

}
