<?php

namespace Tests\Feature\Api;

use App\Account;
use App\AccountType;
use App\Entry;
use Faker\Factory as FakerFactory;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DeleteEntryTest extends TestCase {

    private $_base_uri = '/api/entry/';

    public function testMarkingEntryDeleted(){
        // GIVEN
        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);
        $entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);

        // WHEN
        $get_response1 = $this->get($this->_base_uri.$entry->id);
        $delete_response = $this->delete($this->_base_uri.$entry->id);
        $get_response2 = $this->get($this->_base_uri.$entry->id);

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_NOT_FOUND);

        $this->assertEmpty($delete_response->getContent());
        $this->assertTrue(is_array($get_response2->json()));
        $this->assertEmpty($get_response2->json());
    }

    public function testMarkingEntryDeletedWhenEntryDoesNotExist(){
        $faker = FakerFactory::create();
        // GIVEN
        $entry_id = $faker->randomNumber();

        // WHEN
        $get_response = $this->get($this->_base_uri.$entry_id);
        $delete_response = $this->delete($this->_base_uri.$entry_id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertTrue(is_array($get_response->json()));
        $this->assertEmpty($get_response->json());
        $this->assertEmpty($delete_response->getContent());
    }

}