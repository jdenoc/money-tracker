<?php

namespace Tests\Browser;

use App\Models\Tag;
use Facebook\WebDriver\Exception\TimeoutException;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SettingsPage;

class SettingsTagsTest extends SettingsBaseTest {

    private static string $SELECTOR_SETTINGS_NAV_TAGS = 'li.settings-nav-option:nth-child(4)';
    private static string $SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS = 'section#settings-tags';

    private static string $SELECTOR_SETTINGS_TAG_FORM_LABEL_NAME = "label[for='settings-tag-name']:nth-child(1)";
    private static string $SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME = "input#settings-tag-name:nth-child(2)";
    private static string $SELECTOR_SETTINGS_TAG_FORM_BUTTON_CLEAR = 'button:nth-child(3)';
    private static string $SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE = 'button:nth-child(4)';

    private static string $TEMPLATE_SELECTOR_SETTINGS_TAG_TAG_ID = 'span#settings-tag-%d';

    private static string $SELECTOR_SETTINGS_LOADING_TAGS =  '#loading-settings-tags';

    private static string $LABEL_SETTINGS_TAGS = 'Tags';
    private static string $LABEL_SETTINGS_TAG_NOTIFICATION_NEW = 'New tag created';
    private static string $LABEL_SETTINGS_TAG_NOTIFICATION_UPDATE = 'Tag updated';

    protected static string $LABEL_INPUT_NAME = 'Tag:';

    public function testNavigateToTagSettingsAndAssertForm(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $section
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_HEADER, self::$LABEL_SETTINGS_TAGS)

                        ->assertSeeIn(self::$SELECTOR_SETTINGS_TAG_FORM_LABEL_NAME, self::$LABEL_INPUT_NAME)
                        ->assertVisible(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME)
                        ->assertInputValue(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, '');

                    $section
                        ->assertVisible(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_CLEAR)
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_CLEAR, self::$LABEL_BUTTON_CLEAR)

                        ->assertVisible(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE)
                        ->assertVisible(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE.' svg')
                        ->assertSeeIn(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, self::$LABEL_BUTTON_SAVE);
                    $this->assertElementBackgroundColor($section, self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, $this->color_button_save);
                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEquals('true', $save_button_state);
                });
            });
        });
    }

    public function testTagsListedUnderFormAreVisible(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_TAGS, self::$WAIT_SECONDS);

                    $tags = Tag::all();
                    $this->assertCount($tags->count(), $section->elements('hr~div span.tag'));
                    foreach ($tags as $tag){
                        $selector_tag_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_TAG_TAG_ID, $tag->id);
                        $section
                            ->assertVisible($selector_tag_id)
                            ->assertSeeIn($selector_tag_id, $tag->name);
                    }
                });
            });
        });
    }

    public function testFormFieldInteractionAndClearButtonFunctionality(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $section
                        ->type(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, $this->faker->word())
                        ->assertInputValueIsNot(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, '');

                    $save_button_disabled_state = $section->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_disabled_state);    // if not disabled, the attribute isn't even available

                    $this->clickClearButton($section);
                    $this->assertFormDefaults($section);
                });
            });
        });
    }

    public function testClickExistingTagDisplaysDataInFormAndClearingFormThenReclickSameTag(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_TAGS, self::$WAIT_SECONDS);

                    $tag = Tag::get()->random();
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_TAG_TAG_ID, $tag->id);
                    $section->click($selector);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $tag);
                    $this->clickClearButton($section);

                    $this->assertFormDefaults($section);

                    $section->click($selector);
                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $tag);
                });
            });
        });
    }

    public function testSaveNewTag(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $tags = Tag::all();
                    do{
                        $tag_name = $this->faker->word();
                    } while($tags->contains('name', $tag_name));
                    $section->type(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, $tag_name);

                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);
                    $section->click(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_TAG_NOTIFICATION_NEW);
                        $this->dismissNotification($body);
                    });

                    $this->assertFormDefaults($section);

                    $new_tag = Tag::all()->diff($tags)->first();
                    $selector = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_TAG_TAG_ID, $new_tag->id);
                    $section
                        ->assertVisible($selector)
                        ->click($selector);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });
                    $this->assertFormWithExistingData($section, $new_tag);
                });
            });
        });
    }

    public function testSaveExistingTag(){
        $this->browse(function(Browser $browser){
            $browser->visit(new SettingsPage());
            $this->navigateToTagsSettingsOnSettingsPage($browser);
            $browser->within(self::$SELECTOR_SETTINGS_DISPLAY, function(Browser $settings_display){
                $this->assertTagsSettingsDisplayed($settings_display);
                $settings_display->within(self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS, function(Browser $section){
                    $this->assertFormDefaults($section);
                    $section->waitUntilMissing(self::$SELECTOR_SETTINGS_LOADING_TAGS, self::$WAIT_SECONDS);

                    $tag = Tag::get()->random();
                    $selector_tag_id = sprintf(self::$TEMPLATE_SELECTOR_SETTINGS_TAG_TAG_ID, $tag->id);
                    $section
                        ->assertVisible($selector_tag_id)
                        ->click($selector_tag_id);

                    $this->assertFormWithExistingData($section, $tag);

                    $section->type(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, $this->faker->word());

                    $save_button_state = $section->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
                    $this->assertEmpty($save_button_state);
                    $section->click(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                        $this->assertNotificationContents($body, self::$NOTIFICATION_TYPE_SUCCESS, self::$LABEL_SETTINGS_TAG_NOTIFICATION_UPDATE);
                        $this->dismissNotification($body);
                    });
                    $this->assertFormDefaults($section);

                    $tag = Tag::find($tag->id); // get updated tag data
                    $section
                        ->assertVisible($selector_tag_id)
                        ->click($selector_tag_id);

                    $section->elsewhere(self::$SELECTOR_PRIMARY_DIV, function(Browser $body){
                        $this->waitForLoadingToStop($body);
                    });

                    $this->assertFormWithExistingData($section, $tag);
                });
            });
        });
    }

    private function navigateToTagsSettingsOnSettingsPage(Browser $browser){
        $this->navigateToSettingsSectionOnSettingsPage($browser, self::$SELECTOR_SETTINGS_NAV_TAGS, "Tags");
    }

    private function assertTagsSettingsDisplayed(Browser $settings_display){
        $this->assertSettingsSectionDisplayed($settings_display, self::$SELECTOR_SETTINGS_DISPLAY_SECTION_TAGS);
    }

    /**
     * @param Browser $browser
     * @throws TimeOutException
     *
     * Form defaults:
     *   Name: (empty)
     *   Save button [disabled]
     */
    private function assertFormDefaults(Browser $browser){
        $browser
            ->scrollToElement(self::$SELECTOR_SETTINGS_HEADER)
            ->assertInputValue(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, '');
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);
    }

    private function assertFormWithExistingData(Browser $browser, Tag $tag){
        $browser
            ->scrollToElement(self::$SELECTOR_SETTINGS_HEADER)
            ->assertInputValue(self::$SELECTOR_SETTINGS_TAG_FORM_INPUT_NAME, $tag->name);

        $this->assertElementBackgroundColor($browser, self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, $this->color_button_save);
        $save_button_state = $browser->attribute(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_SAVE, 'disabled');
        $this->assertEquals("true", $save_button_state);    // no changes; so button remains disabled
    }

    private function clickClearButton(Browser $browser){
        $browser
            ->scrollToElement(self::$SELECTOR_SETTINGS_HEADER)
            ->click(self::$SELECTOR_SETTINGS_TAG_FORM_BUTTON_CLEAR);
    }

}
