<?php

namespace Tests\Feature\Api\Get;

use App\Models\Tag;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class GetTagsTest extends TestCase {

    private string $_uri = '/api/tags';

    public function testObtainingListOfTagsWhenTagsArePresentInDatabase() {
        // GIVEN
        $tag_count = 5;
        $generated_tags = Tag::factory()->count($tag_count)->create();

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertNotEmpty($response_body_as_array);
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($response_body_as_array['count'], $tag_count);
        unset($response_body_as_array['count']);
        $this->assertCount($tag_count, $response_body_as_array);

        $expected_elements = ['id', 'name'];
        foreach ($response_body_as_array as $tag_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($tag_in_response));
            $generated_tag = $generated_tags->where('id', $tag_in_response['id'])->first();
            $this->assertNotEmpty($generated_tag);
            $this->assertEquals($generated_tag->toArray(), $tag_in_response);
        }
    }

    public function testObtainingListOfTagsWhenTagsAreNotPresentInDatabase() {
        // GIVEN - nothing. database should be empty

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertIsArray($response_body_as_array);
        $this->assertEmpty($response_body_as_array);
    }

}
