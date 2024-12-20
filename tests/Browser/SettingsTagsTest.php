<?php

namespace Tests\Browser;

use App\Models\BaseModel;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Dusk\Browser;

/**
 * @group settings
 * @group settings-tags
 */
class SettingsTagsTest extends SettingsBase {

    // selectors
    protected static string $SELECTOR_SETTINGS_NAV_ELEMENT = 'li.settings-nav-option:nth-child(4)';
    protected static string $LABEL_SETTINGS_NAV_ELEMENT = 'Tags';
    protected static string $SELECTOR_SETTINGS_DISPLAY_SECTION = 'section#settings-tags';
    protected static string $LABEL_SETTINGS_SECTION_HEADER = 'Tags';
    private static string $SELECTOR_SETTINGS_FORM_LABEL_NAME = "label[for='settings-tag-name']:nth-child(1)";
    protected static string $LABEL_SETTINGS_INPUT_NAME = 'Tag:';
    private static string $SELECTOR_SETTINGS_FORM_INPUT_NAME = "input#settings-tag-name:nth-child(2)";
    protected static string $SELECTOR_SETTINGS_FORM_TOGGLE_ACTIVE = ''; // this page doesn't use this element
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_CLEAR = 'button:nth-child(3)';
    protected static string $SELECTOR_SETTINGS_FORM_BUTTON_SAVE = 'button:nth-child(4)';
    protected static string $SELECTOR_SETTINGS_LOADING_OBJECTS =  '#loading-settings-tags';
    protected static string $TEMPLATE_SELECTOR_SETTINGS_NODE_ID = 'span#settings-tag-%d';

    // labels
    protected static string $LABEL_SETTINGS_NOTIFICATION_NEW = 'New tag created';
    protected static string $LABEL_SETTINGS_NOTIFICATION_UPDATE = 'Tag updated';
    protected static string $LABEL_SETTINGS_NOTIFICATION_RESTORE = 'Tag has been enabled';  // TODO: confirm
    protected static string $LABEL_SETTINGS_NOTIFICATION_DELETE = 'Tag has been disabled';  // TODO: confirm

    public static function providerDisablingOrRestoringObject(): array {
        return ['unsupported' => [false]];
    }

    public static function providerSaveExistingSettingObject(): array {
        return [
            'tag' => [self::$SELECTOR_SETTINGS_FORM_INPUT_NAME],  // test 7/20
        ];
    }

    /**
     * Form defaults:
     *   Name: (empty)
     *   Save button [disabled]
     */
    protected function assertFormDefaults(Browser $section): void {
        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');
        $this->assertSaveButtonDisabled($section);
    }

    protected function assertFormDefaultsFull(Browser $section): void {
        $section
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_LABEL_NAME, self::$LABEL_SETTINGS_INPUT_NAME)
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');

        $section
            ->assertVisible(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR)
            ->assertSeeIn(self::$SELECTOR_SETTINGS_FORM_BUTTON_CLEAR, self::$LABEL_SETTINGS_FORM_BUTTON_CLEAR);

        $this->assertSaveButtonDefault($section);
    }

    protected function assertFormWithExistingData(Browser $section, BaseModel $object): void {
        $this->assertObjectIsOfType($object, Tag::class);

        $section
            ->scrollIntoView(self::$SELECTOR_SETTINGS_HEADER)
            ->assertInputValue(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, $object->name);
        $this->assertSaveButtonDisabled($section);
    }

    protected function assertNodesVisible(Browser $section): void {
        $tags = $this->getAllObjects();
        $this->assertCount($tags->count(), $section->elements('hr~div span.tag'));
        foreach ($tags as $tag) {
            $selector_tag_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID, $tag->id);
            $section
                ->assertVisible($selector_tag_id)
                ->assertSeeIn($selector_tag_id, $tag->name);
        }
    }

    protected function fillForm(Browser $section) {
        $this->interactWithFormElement($section, self::$SELECTOR_SETTINGS_FORM_INPUT_NAME);
        $section->assertInputValueIsNot(self::$SELECTOR_SETTINGS_FORM_INPUT_NAME, '');
    }

    protected function generateObject(bool $isInitObjectActive): BaseModel {
        return Tag::factory()->create();
    }

    protected function getObject(int $id = null): BaseModel {
        return Tag::get()->random();
    }

    protected function getAllObjects(): Collection {
        return Tag::all();
    }

    protected function interactWithObjectListItem(Browser $section, BaseModel $object, bool $is_fresh_load = true): void {
        $this->assertObjectIsOfType($object, Tag::class);

        $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_NODE_ID, $object->id);
        $section
            ->assertVisible($selector)
            ->click($selector);
    }

    protected function interactWithFormElement(Browser $section, string $selector, ?BaseModel $object = null): void {
        if (is_null($object)) {
            $object = new Tag();
        }
        $this->assertObjectIsOfType($object, Tag::class);

        switch ($selector) {
            case self::$SELECTOR_SETTINGS_FORM_INPUT_NAME:
                $tags = $this->getAllObjects();
                do {
                    $name = fake()->word();
                } while ($object->name == $name || $tags->contains('name', $name));
                $section
                    ->clear($selector)
                    ->type($selector, fake()->word());
                break;
            default:
                throw new \UnexpectedValueException(sprintf("Unexpected form element [%s] provided", $selector));
        }
    }

}
