<?php

namespace Tests\Browser;

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

class SettingsBaseTest extends DuskTestCase {

    use DuskTraitToggleButton;
    use DuskTraitLoading;
    use DuskTraitNotification;
    use WaitTimes;
    use WithFaker;
    use WithTailwindColors;

    protected static string $SELECTOR_PRIMARY_DIV = '#app-settings';

    protected static string $SELECTOR_SETTINGS_NAV = '#settings-nav';
    protected static string $SELECTOR_SETTINGS_NAV_HEADER = '#settings-panel-header';
    protected static string $SELECTOR_SETTINGS_NAV_ACTIVE = 'li.settings-nav-option.is-active';

    protected static string $SELECTOR_SETTINGS_DISPLAY = '#settings-display';
    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT = 'section#settings-default';
    protected static string $SELECTOR_SETTINGS_HEADER = 'h3';

    protected static string $LABEL_INPUT_NAME = 'Name:';
    protected static string $LABEL_INPUT_ACTIVE_STATE = 'Active State:';
    protected static string $LABEL_LABEL_CREATED = "Created:";
    protected static string $LABEL_LABEL_MODIFIED = "Modified:";
    protected static string $LABEL_LABEL_DISABLED = "Disabled:";
    protected static string $LABEL_BUTTON_CLEAR = "Clear";
    protected static string $LABEL_BUTTON_SAVE = "Save";

    protected string $color_button_save;

    public function setUp(): void{
        // TODO: vvv remove vvv
        self::$DUMP_READY = true;
        self::$FRESH_RUN = false;
        self::$SNAPSHOT_NAME = class_basename(get_called_class());
        // TODO: ^^^ remove ^^^

        parent::setUp();
        $this->initSettingsColors();
    }

    protected function initSettingsColors(){
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

    protected function navigateToSettingsSectionOnSettingsPage(Browser $browser, string $section_selector, string $label){
        $browser
            ->assertVisible(self::$SELECTOR_SETTINGS_NAV)
            ->within(self::$SELECTOR_SETTINGS_NAV, function(Browser $side_panel) use ($section_selector, $label){
                $side_panel
                    ->assertMissing(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertVisible($section_selector)
                    ->assertSeeIn($section_selector, $label)
                    ->click($section_selector)
                    ->assertVisible(self::$SELECTOR_SETTINGS_NAV_ACTIVE)
                    ->assertSeeIn(self::$SELECTOR_SETTINGS_NAV_ACTIVE, $label);
            });
    }

    protected function assertSettingsSectionDisplayed(Browser $settings_display, string $section_selector){
        $settings_display
            ->assertMissing(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_DEFAULT)
            ->assertVisible($section_selector);
    }

    protected function assertActiveStateToggleActive(Browser $settings_display, $toggle_selector){
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Active', $this->tailwindColors->blue(600));
    }

    protected function assertActiveStateToggleInactive(Browser $settings_display, $toggle_selector){
        $this->assertToggleButtonState($settings_display, $toggle_selector, 'Inactive', $this->tailwindColors->gray(400));
    }

    protected function convertDateToECMA262Format(string $date):string{
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toString
        // ECMA-262 datetime format
        return Carbon::create($date)->format('D M d Y H:i:s \G\M\TO');
    }

}
