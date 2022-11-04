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

class SettingsBase extends DuskTestCase {
    use DuskTraitToggleButton;
    use DuskTraitLoading;
    use DuskTraitNotification;
    use WaitTimes;
    use WithFaker;
    use WithTailwindColors;

    protected static string $SELECTOR_PRIMARY_DIV = '#app-settings';

    private static string $SELECTOR_SETTINGS_NAV = '#settings-nav';
    private static string $SELECTOR_SETTINGS_NAV_HEADER = '#settings-panel-header';
    private static string $SELECTOR_SETTINGS_NAV_ACTIVE = 'li.settings-nav-option.is-active';
    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = '';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = '';

    private static string $SELECTOR_SETTINGS_DISPLAY = '#settings-display';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT = 'section#settings-default';
    protected static string $SELECTOR_SETTINGS_HEADER = 'h3';
    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = '';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = '';

    protected static string $LABEL_SETTINGS_FORM_INPUT_NAME = 'Name:';
    protected static string $LABEL_SETTINGS_FORM_LABEL_ACTIVE = 'Active State:';
    protected static string $LABEL_SETTINGS_LABEL_CREATED = "Created:";
    protected static string $LABEL_SETTINGS_LABEL_MODIFIED = "Modified:";
    protected static string $LABEL_SETTINGS_LABEL_DISABLED = "Disabled:";

    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = '';
    protected static string $LABEL_SETTINGS_FORM_BUTTON_CLEAR = "Clear";
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = '';
    private static string $LABEL_SETTINGS_FORM_BUTTON_SAVE = "Save";

    protected static string $SELECTOR_SETTINGS_LOADING_NODES = '';
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = '';

    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = '';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = '';

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
     * test 1/25
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
     * test 2/25
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
     * test 3/25
     */
    public function testNodesListedUnderFormAreVisible() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);
                    $this->assertNodesVisible($section);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 4/25
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
     * test 5/25
     */
    public function testClickExistingNodeWillDisplayDataInFormThenClearFormAndReclickSameNode() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $section->waitUntilMissing(static::$SELECTOR_SETTINGS_LOADING_NODES, self::$WAIT_SECONDS);

                    $node = $this->getNode();
                    $this->interactWithNode($section, $node);
                    $this->assertFormWithExistingData($section, $node);

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);

                    $this->interactWithNode($section, $node, false);
                    $this->assertFormWithExistingData($section, $node);
                });
            });
        });
    }

    /**
     * @throws Throwable
     *
     * test 6/25
     */
    public function testSaveNewSettingNode() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new SettingsPage());
            $this->navigateToSettingsSectionOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display) {
                $this->assertSettingsSectionDisplayed($settings_display);
                $settings_display->within(static::$SELECTOR_SETTINGS_DISPLAY_SECTION, function(Browser $section) {
                    $nodes = $this->getAllNodes();
                    $this->fillForm($section);

                    $this->assertSaveButtonEnabled($section);
                    $this->clickSaveButton($section);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, static::$LABEL_SETTINGS_NOTIFICATION_NEW);
                        $this->dismissNotification($body);
                    });

                    $this->assertFormDefaults($section);

                    $new_node =$this->getAllNodes()->diff($nodes)->first();
                    $this->interactWithNode($section, $new_node);
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $new_node);
                });
            });
        });
    }

    public function providerSaveExistingSettingNode(): array {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    /**
     * @dataProvider providerSaveExistingSettingNode
     * @throws Throwable
     *
     * test ?/25
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

                    $node = $this->getNode();
                    $this->interactWithNode($section, $node);
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

                    $node = $this->getNode($node->id);    // get updated data
                    $this->interactWithNode($section, $node);
                    $this->assertFormWithExistingData($section, $node);
                });
            });
        });
    }

    // ------------ ------------ ------------
    // ------------ to override  ------------
    // ------------ ------------ ------------

    private function throwEmptyMethodException(string $method) {
        throw new \Exception("The method '".$method."' needs to be filled in.");
    }

    protected function assertFormDefaults(Browser $section) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function assertFormDefaultsFull(Browser $section) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function assertFormWithExistingData(Browser $section, BaseModel $node) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function assertNodesVisible(Browser $section) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function fillForm(Browser $section) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function getNode(int $id=null): BaseModel {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function getAllNodes(): Collection {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function interactWithNode(Browser $section, BaseModel $node, bool $is_fresh_load=true) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    protected function interactWithFormElement(Browser $section, string $selector, BaseModel $node=null) {
        $this->throwEmptyMethodException(__FUNCTION__);
    }

    // ------------ ------------ ------------
    // ------------ asserts      ------------
    // ------------ ------------ ------------

    protected function assertConstantsSet() {
        $constants_to_be_set = [
            'SELECTOR_SETTINGS_NAV_ELEMENT'=>static::$SELECTOR_SETTINGS_NAV_ELEMENT,
            'LABEL_SETTINGS_NAV_ELEMENT'=>static::$LABEL_SETTINGS_NAV_ELEMENT,

            'SELECTOR_SETTINGS_DISPLAY_SECTION'=>static::$SELECTOR_SETTINGS_DISPLAY_SECTION,
            'LABEL_SETTINGS_SECTION_HEADER'=>static::$LABEL_SETTINGS_SECTION_HEADER,

            'SELECTOR_SETTINGS_FORM_BUTTON_CLEAR'=>static::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR,
            'SELECTOR_SETTINGS_FORM_BUTTON_SAVE'=>static::$SELECTOR_SETTINGS_FORM_BUTTON_SAVE,

            'SELECTOR_SETTINGS_LOADING_NODES'=>static::$SELECTOR_SETTINGS_LOADING_NODES,
            'TEMPLATE_SELECTOR_SETTINGS_NODE_ID'=>static::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID,

            'LABEL_SETTINGS_NOTIFICATION_NEW'=>static::$LABEL_SETTINGS_NOTIFICATION_NEW,
            'LABEL_SETTINGS_NOTIFICATION_UPDATE'=>static::$LABEL_SETTINGS_NOTIFICATION_UPDATE,
        ];

        foreach ($constants_to_be_set as $constant_name=>$constant_value) {
            $this->assertNotEmpty($constant_value, sprintf("The constant %s must have a value set", $constant_name));
        }
    }

    private function assertSettingsSectionDisplayed(Browser $settings_display) {
        $settings_display
            ->assertMissing(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
            ->assertVisible(static::$SELECTOR_SETTINGS_DISPLAY_SECTION);
    }

    protected function assertActiveStateToggleActive(Browser $settings_display, $toggle_selector) {
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Active', $this->tailwindColors->blue(600));
    }

    protected function assertActiveStateToggleInactive(Browser $settings_display, $toggle_selector) {
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Inactive', $this->tailwindColors->gray(400));
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

    protected function assertNodeIsOfType($node, string $type) {
        $this->assertTrue(
            get_class($node) === $type,
            sprintf("node of type [%s] was incorrectly provided", get_class($node))
        );
    }

    // ------------ ------------ ------------
    // ------------ utilities    ------------
    // ------------ ------------ ------------

    protected function initSettingsColors() {
        $this->color_button_save = $this->tailwindColors->green(500);
    }

    private function navigateToSettingsSectionOnSettingsPage(Browser $browser) {
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

}
