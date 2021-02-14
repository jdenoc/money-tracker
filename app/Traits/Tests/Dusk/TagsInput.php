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

    public function assertDefaultStateOfTagsInput(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER)
            ->waitUntilMissing(self::$SELECTOR_TAGS_INPUT_LOADING, self::$WAIT_SECONDS)
            ->assertVisible(self::$SELECTOR_TAGS_INPUT_INPUT)
            ->assertMissing(self::$SELECTOR_TAGS_INPUT_TAG)
            ->assertInputValue(self::$SELECTOR_TAGS_INPUT_INPUT, '')
            ->assertDontSee(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS);
    }

    /**
     * @param Browser $browser
     * @param string $tag
     */
    private function fillTagsInputUsingAutocomplete(Browser $browser, string $tag){
        $browser
            ->waitUntilMissing(self::$SELECTOR_TAGS_INPUT_LOADING, self::$WAIT_SECONDS)
            // using safeColorName as our tag, we can be guaranteed after 3 characters we will have a unique word available
            ->keys(self::$SELECTOR_TAGS_INPUT_INPUT, substr($tag, 0, 1))  // 1st char
            ->keys(self::$SELECTOR_TAGS_INPUT_INPUT, substr($tag, 1, 1))  // 2nd char
            ->keys(self::$SELECTOR_TAGS_INPUT_INPUT, substr($tag, 2, 1))  // 3rd char
            ->waitFor(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS)
            ->assertSeeIn(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS, $tag)
            ->click(self::$SELECTOR_TAG_AUTOCOMPLETE_OPTIONS);
    }

    /**
     * @param Browser $browser
     */
    public function assertTagsInputHasTagsInInput(Browser $browser){
        // the very existance of a tag in the input field indicates that they are present
        $browser->assertVisible(self::$SELECTOR_TAGS_INPUT_CONTAINER.' '.self::$SELECTOR_TAGS_INPUT_TAG);
    }

    /**
     * @param Browser $browser
     * @param string $tag
     */
    public function assertTagInInput(Browser $browser, string $tag){
        $this->assertTagsInputHasTagsInInput($browser);
        $browser->assertSeeIn(self::$SELECTOR_TAGS_INPUT_CONTAINER, $tag);
    }

}
