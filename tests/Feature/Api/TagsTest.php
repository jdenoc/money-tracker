<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

Use App\Tag;

class TagsTest extends TestCase {

    use DatabaseMigrations;

    /**
     * SCENARIO: a need to obtain a list of entry tags available
     *  GIVEN:   there are tag values present in the database
     *  WHEN:    visiting GET /api/tags
     *  THEN:    we will receive a 200 status
     *  AND:     display a json output of tags
     *           output example: { {"id": 1, "tag": "tag1"}, {"id": 2, "tag": "tag2"}, "count": 2 }
     */
    public function testObtainingListOfTagsWhenTagsArePresentInDatabase(){
        // GIVEN
        $tag_count = 5;
        $tags = factory(Tag::class, $tag_count)->create();

        // WHEN
        $response = $this->get('/api/tags');

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertNotEmpty($response_body_as_array);
        $this->assertEquals($response_body_as_array['count'], $tag_count);
        unset($response_body_as_array['count']);
        foreach ($tags as $tag){
            $this->assertTrue(in_array($tag->toArray(), $response_body_as_array));
        }
    }

    /**
     * SCENARIO: a need to obtain a list of entry tags available
     *  GIVEN:   there are NO tag values present in the database
     *  WHEN:    visiting GET /api/tags
     *  THEN:    we will receive a 404 status
     *  AND:     display empty json output
     *           output example: []
     */
    public function testObtainingListOfTagsWhenTagsAreNotPresentInDatabase(){
        // GIVEN - nothing. database should be empty

        // WHEN
        $response = $this->get('/api/tags');

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertEmpty($response_body_as_array);
    }

}