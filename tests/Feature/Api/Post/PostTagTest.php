<?php

namespace Tests\Feature\Api\Post;

use App\Models\Tag;
use App\Traits\TagResponseKeys;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostTagTest extends TestCase {
    use TagResponseKeys;

    // uri
    private string $_uri = '/api/tag';

    public function testCreateTag() {
        // GIVEN
        $tag_data = Tag::factory()->make()->toArray();

        // WHEN
        $response = $this->postJson($this->_uri, $tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_CREATED);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    public function testCreateTagWithoutData() {
        // GIVEN
        $tag_data = [];

        // WHEN
        $response = $this->postJson($this->_uri, $tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    public function testCreateTagWithMissingData() {
        // GIVEN
        $tag_data = Tag::factory()->make(['name' => ''])->toArray();

        // WHEN
        $response = $this->postJson($this->_uri, $tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    /**
     * @param array $response_as_array
     */
    private function assertPostResponseHasCorrectKeys(array $response_as_array): void {
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ERROR, $response_as_array, $failure_message);
        $this->assertCount(2, $response_as_array);
    }

}
