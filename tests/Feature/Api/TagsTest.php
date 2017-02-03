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
     * test GET /api/tags
     */
    public function testGetTags(){
        $tag_count = 5;
        $tags = factory(Tag::class, $tag_count)->create();

        $response = $this->get('/api/tags');
        $response->assertStatus(200);
        $response->assertJson(['count'=>$tag_count]);
        $response->assertJson($tags->toArray());
    }

}