<?php

namespace Tests\Feature\Api;

use App\Entry;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteEntryTest extends TestCase {

    use DatabaseMigrations;

    private $_base_uri = '/api/entry/';

    public function testMarkingEntryDeleted(){
        // GIVEN
        $entry = factory(Entry::class)->create();

        // WHEN
        $get_response1 = $this->get($this->_base_uri.$entry->id);
        $delete_response = $this->delete($this->_base_uri.$entry->id);
        $get_response2 = $this->get($this->_base_uri.$entry->id);

        // THEN
        $get_response1->assertStatus(200);
        $delete_response->assertStatus(204);
        $get_response2->assertStatus(404);
    }

    public function testMarkingEntryDeletedWhenEntryDoesNotExist(){
        // GIVEN
        $entry_id = 99999;

        // WHEN
        $get_response = $this->get($this->_base_uri.$entry_id);
        $delete_response = $this->delete($this->_base_uri.$entry_id);

        // THEN
        $get_response->assertStatus(404);
        $delete_response->assertStatus(404);
    }

}