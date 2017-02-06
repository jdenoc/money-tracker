<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

Use App\Tag;

class TagsTest extends TestCase {

    use DatabaseMigrations;

    public function testObtainingListOfTagsWhenTagsArePresentInDatabase(){
        // GIVEN
        $tag_count = 5;
        $generated_tags = factory(Tag::class, $tag_count)->create();

        // WHEN
        $response = $this->get('/api/tags');

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertNotEmpty($response_body_as_array);
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($response_body_as_array['count'], $tag_count);
        unset($response_body_as_array['count']);
        foreach($response_body_as_array as $tag_in_response){
            $this->assertArrayHasKey('id', $tag_in_response);
            $this->assertArrayHasKey('tag', $tag_in_response);
        }
        foreach($generated_tags as $generated_tag){
            $this->assertTrue(
                in_array($generated_tag->toArray(), $response_body_as_array),
                "Factory generate tag in JSON: ".$generated_tag->toJson()."\nResponse Body:".$response_body
            );
        }
    }

    public function testObtainingListOfTagsWhenTagsAreNotPresentInDatabase(){
        // GIVEN - nothing. database should be empty

        // WHEN
        $response = $this->get('/api/tags');

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

}