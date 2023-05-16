<?php

namespace Tests\Feature\Api\Put;

use App\Models\Tag;
use App\Traits\TagResponseKeys;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutTagTest extends TestCase {
    use TagResponseKeys;

    const METHOD = 'PUT';

    private string $_uri = '/api/tag/%d';
    private $_generated_tag;

    public function setUp(): void {
        parent::setUp();
        $this->_generated_tag = Tag::factory()->create();
    }

    public function testUpdateExistingTag() {
        // GIVEN
        $existing_tag_id = $this->_generated_tag->id;
        $new_tag_data = Tag::factory()->make()->toArray();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_uri, $existing_tag_id), $new_tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertEquals(self::$ERROR_MSG_NO_ERROR, $response_as_array[self::$RESPONSE_KEY_ERROR]);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEquals($existing_tag_id, $response_as_array[self::$RESPONSE_KEY_ID]);
    }

    public function testUpdateExistingTagButNameIsEmpty() {
        // GIVEN
        $existing_tag_id = $this->_generated_tag->id;
        $new_tag_data = Tag::factory()->make(['name'=>''])->toArray();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_uri, $existing_tag_id), $new_tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEquals(self::$ERROR_MSG_NO_DATA, $response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    public function testUpdateExistingTagWithoutData() {
        // GIVEN
        $existing_tag_id = $this->_generated_tag->id;
        $new_tag_data = [];

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_uri, $existing_tag_id), $new_tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEquals(self::$ERROR_MSG_NO_DATA, $response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    public function testUpdateTagDoesNotExist() {
        // GIVEN
        do {
            $tag_id = fake()->randomDigit();
        } while ($tag_id == $this->_generated_tag->id);
        $new_tag_data = Tag::factory()->make()->toArray();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_uri, $tag_id), $new_tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEquals(self::$ERROR_MSG_DOES_NOT_EXIST, $response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    public function testUpdateTagWithoutChangingValues() {
        // GIVEN
        $tag_data = $this->_generated_tag->toArray();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_uri, $tag_data['id']), $tag_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID]);
        $this->assertEquals(self::$ERROR_MSG_NO_ERROR, $response_as_array[self::$RESPONSE_KEY_ERROR]);
    }

    /**
     * @param array $response_as_array
     */
    private function assertPutResponseHasCorrectKeys(array $response_as_array) {
        $failure_message = self::METHOD." Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ERROR, $response_as_array, $failure_message);
        $this->assertCount(2, $response_as_array);
    }

}
