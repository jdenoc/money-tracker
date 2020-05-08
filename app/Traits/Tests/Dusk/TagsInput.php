<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait TagsInput {

    use WaitTimes;

    private static $SELECTOR_TAGS_INPUT_CONTAINER = ".tags-input";
    private static $SELECTOR_TAGS_INPUT_LOADING = '.is-loading .tags-input';
    private static $SELECTOR_TAGS_INPUT_INPUT = ".tags-input input";
    private static $SELECTOR_TAGS_INPUT_TAG = "span.badge.badge-pill.badge-light";
    private static $SELECTOR_TAG_AUTOCOMPLETE_OPTIONS = '.typeahead span.badge';

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
    private function fillTagsInputUsingAutocomplete(Browser $browser, $tag){
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
     * @param string $tag
     */
    public function assertTagInInput(Browser $browser, $tag){
        $browser
            ->assertVisible(self::$SELECTOR_TAGS_INPUT_TAG)
            ->assertSeeIn(self::$SELECTOR_TAGS_INPUT_CONTAINER, $tag);
    }

}
