<?php

namespace Tests\Browser;

use App\Models\BaseModel;
use App\Models\Institution;
use App\Traits\Tests\Dusk\ToggleButton as DuskTraitToggleButton;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;

/**
 * @group settings
 * @group settings-institutions
 */
class SettingsInstitutionsTest extends SettingsBase {
    use DuskTraitToggleButton;

    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = 'li.settings-nav-option:nth-child(1)';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = 'Institutions';

    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = 'section#settings-institutions';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = 'Institutions';

    private static string $SELECTOR_SETTINGS_FORM_LABEL_NAME = "label[for='settings-institution-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_FORM_INPUT_NAME = 'input#settings-institution-name:nth-child(2)';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_ACTIVE = "label:nth-child(3)";
    protected static string $SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE = 'div:nth-child(4) #settings-institution-active';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_CREATED = 'div:nth-child(5)';
    private static string $SELECTOR_SETTINGS_FORM_CREATED = 'div:nth-child(6)';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_MODIFIED = 'div:nth-child(7)';
    private static string $SELECTOR_SETTINGS_FORM_MODIFIED = 'div:nth-child(8)';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_DISABLED = 'div:nth-child(9)';
    private static string $SELECTOR_SETTINGS_FORM_DISABLED = 'div:nth-child(10)';
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = 'button:nth-child(11)';
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = 'button:nth-child(12)';

    protected static string $SELECTOR_SETTINGS_LOADING_OBJECTS = '#loading-settings-institutions';
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = '#settings-institution-%d';

    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = 'New Institution created';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = 'Institution updated';
    protected static string $LABEL_SETTINGS_NOTIFICATION_RESTORE = 'Institution has been enabled';
    protected static string $LABEL_SETTINGS_NOTIFICATION_DELETE = 'Institution has been disabled';

    public function providerSaveExistingSettingObject(): array {
        return [
            'name'=>[self::$SELECTOR_SETTINGS_FORM_INPUT_NAME],         // test 7/20
        ];
    }

    public function providerDisablingOrRestoringObject(): array {
        return [
            'disabling institution'=>['isInitInstitutionActive'=>true],     // test 8/20
            'restoring institution'=>['isInitInstitutionActive'=>false],    // test 9/20
        ];
    }

    protected function assertFormDefaultsFull(Browser $section): void {
        $section
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_NAME, self::$LABEL_SETTINGS_FORM_INPUT_NAME)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '')

            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_ACTIVE, self::$LABEL_SETTINGS_FORM_LABEL_ACTIVE)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);

        $section
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED)

            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR, self::$LABEL_SETTINGS_FORM_BUTTON_CLEAR);
        $this->assertSaveButtonDefault($section);
    }

    /**
     * Form defaults:
     *   Name: ""
     *   Active State: "Active"
     *   Save button [disabled]
     */
    protected function assertFormDefaults(Browser $section): void {
        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)

            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');
        $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);

        $section
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_CREATED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_MODIFIED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED)
            ->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED);
        $this->assertSaveButtonDisabled($section);
    }

    protected function assertFormWithExistingData(Browser $section, BaseModel $object): void {
        $this->assertObjectIsOfType($object, Institution::class);
        $section->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, $object->name);
        if ($object->active) {
            $this->assertActiveStateToggleActive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        } else {
            $this->assertActiveStateToggleInactive($section, self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE);
        }

        $section
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_CREATED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_CREATED, $this->convertDateToECMA262Format($object->create_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_MODIFIED)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_MODIFIED, $this->convertDateToECMA262Format($object->modified_stamp))
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_LABEL_DISABLED);
        if($object->active) {
            $section->assertMissing(self::$SELECTOR_SETTINGS_FORM_DISABLED);
        } else {
            $section->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_DISABLED, $this->convertDateToECMA262Format($object->disabled_stamp));
        }

        $this->assertSaveButtonDisabled($section);
    }

    protected function assertNodesVisible(Browser $section): void {
        $institutions = Institution::all();
        $this->assertCount($institutions->count(), $section->elements('hr~ul li'));
        foreach ($institutions as $institution) {
            $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID, $institution->id);
            $section
                ->assertVisible($selector_institution_id)
                ->assertSeeIn($selector_institution_id, $institution->name);
            $class_is_disabled = 'is-disabled';
            $class_is_active = 'is-active';
            $institution_node_classes = $section->attribute($selector_institution_id, 'class');
            if ($institution->active) {
                $this->assertStringNotContainsString($class_is_disabled, $institution_node_classes);
                $this->assertStringContainsString($class_is_active, $institution_node_classes);
            } else {
                $this->assertStringNotContainsString($class_is_active, $institution_node_classes);
                $this->assertStringContainsString($class_is_disabled, $institution_node_classes);
            }
        }
    }

    protected function fillForm(Browser $section) {
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_NAME);
        $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');
        // don't interact with the "active" toggle button
        // doing so would clear the form
    }

    protected function generateObject(bool $isInitObjectActive): Institution {
        $institution = Institution::factory();
        if(!$isInitObjectActive) {
            return $institution->disabled()->create();
        } else {
            return $institution->create();
        }
    }

    protected function getObject(int $id=null): Institution {
        if (is_null($id)) {
            return Institution::get()->random();
        } else {
            return Institution::withTrashed()->find($id);
        }
    }

    protected function getAllObjects(): Collection {
        return Institution::withTrashed()->get();
    }

    protected function interactWithObjectListItem(Browser $section, BaseModel $object, bool $is_fresh_load=true): void {
        $this->assertObjectIsOfType($object, Institution::class);
        $institution_class_state = $object->active ? '.is-active' : '.is-disabled';
        $selector_institution_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID.$institution_class_state, $object->id);
        $section
            ->assertVisible($selector_institution_id)
            ->click($selector_institution_id.' span');

        if ($is_fresh_load) {
            $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body) {
                $this->waitForLoadingToStop($body);
            });
        }
        $section->pause($this->toggleButtonTransitionTimeInMilliseconds()); // wait for toggle to transition
    }

    protected function interactWithFormElement(Browser $section, string $selector, ?BaseModel $object=null): void {
        if (is_null($object)) {
            $object = new Institution();
        }
        $this->assertObjectIsOfType($object, Institution::class);

        switch($selector) {
            case self::$SELECTOR_SETTINGS_FORM_INPUT_NAME:
                $institutions = $this->getAllObjects();
                do {
                    $name = fake()->word();
                } while ($object->name == $name || $institutions->contains('name', $name));
                $section->type($selector, $name);
                break;
            case self::$SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE:
                $this->toggleToggleButton($section, $selector);
                break;
            default:
                throw new \UnexpectedValueException(sprintf("Unexpected form element [%s] provided", $selector));
        }
    }

}
