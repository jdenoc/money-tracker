<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait TagsInput {
    use WaitTimes;

    private static $SELECTOR_TAGS_INPUT_CONTAINER = ".tags-input";
    private static $SELECTOR_TAGS_INPUT_LOADING = '.is-loading .tags-input';
    private static $SELECTOR_TAGS_INPUT_INPUT = ".tags-input input";
    private static $SELECTOR_TAGS_INPUT_TAG = "span.tags-input-badge-pill";
    private static $SELECTOR_TAG_AUTOCOMPLETE_OPTIONS = '.typeahead-badges span.tags-input-badge';

    public function assertDefaultStateOfTagsInput(Browser $browser) {
        $browser
            ->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER)
            ->waitUntilMissing(self::$SELECTOR_TAGS_INPUT_LOADING, self::$WAIT_SECONDS)
            ->assertVisible(self::$SELECTOR_TAGS_INPUT_INPUT)
            ->assertMissing(self::$SELECTOR_TAGS_INPUT_TAG)
            ->assertInputValue(self::$SELECTOR_TAGS_INPUT_INPUT, '')
            ->assertDontSee(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS);
    }

    private function fillTagsInputUsingAutocomplete(Browser $browser, string $tag) {
        $browser->waitUntilMissing(self::$SELECTOR_TAGS_INPUT_LOADING, self::$WAIT_SECONDS);
        // using colorName as our tag, we can be guaranteed that a tag can be between 3 and 20 characters
        // that is a large range; so we'll keep typing up 75% of the characters to guarantee that we'll get the correct tag to show up first
        $character_limit = max(3, ceil(strlen($tag)*0.75));  // character limit should be a minimum of 3
        for ($tag_character_i = 0; $tag_character_i < $character_limit; $tag_character_i++) {
            $browser->keys(self::$SELECTOR_TAGS_INPUT_INPUT, substr($tag, $tag_character_i, 1));
        }
        $browser
            ->waitFor(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS)
            ->assertSeeIn(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS, $tag)
            ->click(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS);
    }

    public function assertTagsInputHasTagsInInput(Browser $browser) {
        // the very existence of a tag in the input field indicates that they are present
        $browser->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER.' '.self::$SELECTOR_TAGS_INPUT_TAG);
    }

    public function assertTagInInput(Browser $browser, string $tag) {
        $this->assertTagsInputHasTagsInInput($browser);
        $browser->assertSeeIn(self::$SELECTOR_TAGS_INPUT_CONTAINER, $tag);
    }

    public function assertCountOfTagsInInput(Browser $browser, int $expectedTagCount) {
        if ($expectedTagCount === 0) {
            $browser->assertMissing(self::$SELECTOR_TAGS_INPUT_CONTAINER.' '.self::$SELECTOR_TAGS_INPUT_TAG);
        } else {
            $this->assertTagsInputHasTagsInInput($browser);
            $tags = $browser->elements(self::$SELECTOR_TAGS_INPUT_CONTAINER.' '.self::$SELECTOR_TAGS_INPUT_TAG);
            $this->assertCount($expectedTagCount, $tags);
        }
    }

}
