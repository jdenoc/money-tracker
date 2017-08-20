<?php

namespace Tests\Feature\Api;

use App\Account;
use App\Attachment;
use App\Entry;
use App\Tag;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ListEntriesBase extends TestCase {

    use DatabaseMigrations;

    /**
     * @var Generator
     */
    protected $_faker;

    /**
     * @var Account
     */
    protected $_generated_account;

    /**
     * @var Collection[Tag]
     */
    protected $_generated_tags;

    /**
     * @var string
     */
    protected $_uri = '/api/entries';

    public function setUp(){
        parent::setUp();
        $this->_faker = Factory::create();
        $this->_generated_account = factory(Account::class)->create();
        $this->_generated_tags = factory(Tag::class, $this->_faker->randomDigitNotNull)->create();
    }

    /**
     * @param int $account_type_id
     * @param bool $entry_disabled
     * @param array $override_entry_components
     * @return Entry
     */
    protected function generate_entry_record($account_type_id, $entry_disabled, $override_entry_components=[]){
        $default_entry_data = ['account_type'=>$account_type_id, 'disabled'=>$entry_disabled];
        $new_entry_data = array_merge($default_entry_data, $override_entry_components);
        unset($new_entry_data['tags']);
        unset($new_entry_data['has_attachments']);
        $generated_entry = factory(Entry::class)->create($new_entry_data);

        if(!$entry_disabled){    // no sense cluttering up the database with test data for something that isn't supposed to appear anyway
            if(isset($override_entry_components['has_attachments']) && $override_entry_components['has_attachments'] == true){
                $generate_attachment_count = 1;
            } elseif(isset($override_entry_components['has_attachments']) && $override_entry_components['has_attachments'] == false) {
                $generate_attachment_count = 0;
            } else {
                $generate_attachment_count = $this->_faker->randomDigit;
            }
            factory(Attachment::class, $generate_attachment_count)->create(['entry_id' => $generated_entry->id]);

            if(isset($override_entry_components['tags'])){
                foreach($override_entry_components['tags'] as $attachable_tag){
                    $generated_entry->tags()->attach($attachable_tag);
                }
            } else {
                $assign_tag_to_entry_count = $this->_faker->numberBetween(0, $this->_generated_tags->count());
                for($j = 0; $j < $assign_tag_to_entry_count; $j++){
                    $randomly_selected_tag = $this->_generated_tags->random();
                    $generated_entry->tags()->attach($randomly_selected_tag->id);
                }
            }
        }

        return $generated_entry;
    }

    /**
     * @param array $entry_nodes
     */
    protected function assertEntryNodesExist($entry_nodes){
        $this->assertArrayHasKey('entry_date', $entry_nodes);
        $this->assertArrayHasKey('entry_value', $entry_nodes);
        $this->assertArrayHasKey('memo', $entry_nodes);
        $this->assertArrayHasKey('account_type', $entry_nodes);
        $this->assertArrayHasKey('expense', $entry_nodes);
        $this->assertArrayHasKey('confirm', $entry_nodes);
        $this->assertArrayHasKey('create_stamp', $entry_nodes);
        $this->assertArrayHasKey('modified_stamp', $entry_nodes);
        $this->assertArrayHasKey('has_attachments', $entry_nodes);
        $this->assertArrayHasKey('tags', $entry_nodes);
    }

    /**
     * @param array $entry_nodes
     * @param Entry $generated_entry
     */
    protected function assertEntryNodesMatchGeneratedEntry($entry_nodes, $generated_entry){
        $failure_msg = "generated entry:".json_encode($generated_entry)."\nresponse entry:".json_encode($entry_nodes);
        $this->assertEquals($generated_entry->entry_date, $entry_nodes['entry_date'], $failure_msg);
        $this->assertEquals($generated_entry->entry_value, $entry_nodes['entry_value'], $failure_msg);
        $this->assertEquals($generated_entry->memo, $entry_nodes['memo'], $failure_msg);
        $this->assertEquals($generated_entry->account_type, $entry_nodes['account_type'], $failure_msg);
        $this->assertEquals($generated_entry->expense, $entry_nodes['expense'], $failure_msg);
        $this->assertEquals($generated_entry->confirm, $entry_nodes['confirm'], $failure_msg);
        $this->assertDateFormat($entry_nodes['create_stamp'], Carbon::ATOM, $failure_msg);
        $this->assertDatetimeWithinOneSecond($generated_entry->create_stamp, $entry_nodes['create_stamp'], $failure_msg);
        $this->assertDateFormat($entry_nodes['modified_stamp'], Carbon::ATOM, $failure_msg);
        $this->assertDatetimeWithinOneSecond($generated_entry->modified_stamp, $entry_nodes['modified_stamp'], $failure_msg);
        $this->assertTrue(is_bool($entry_nodes['has_attachments']), $failure_msg);
        $this->assertEquals($generated_entry->has_attachments(), $entry_nodes['has_attachments'], $failure_msg);
        $this->assertTrue(is_array($entry_nodes['tags']), $failure_msg);
        $this->assertEquals($generated_entry->get_tag_ids(), $entry_nodes['tags'], $failure_msg);
    }

    /**
     * @param int $generate_entry_count
     * @param array $entries_in_response
     * @param array $generated_entries
     * @param array $generated_disabled_entries
     */
    protected function runEntryListAssertions($generate_entry_count, $entries_in_response, $generated_entries, $generated_disabled_entries=[]){
        $this->assertEquals($generate_entry_count, count($entries_in_response));

        foreach($entries_in_response as $entry_in_response){
            $generated_entry = null;
            $this->assertArrayHasKey('id', $entry_in_response);
            $this->assertNotContains(
                $entry_in_response['id'],
                $generated_disabled_entries,
                'entry ID:'.$entry_in_response['id']."\ndisabled entries:".json_encode($generated_disabled_entries)."\nresponse entries:".json_encode($entries_in_response)
            );
            foreach($generated_entries as $generated_entry){
                if($entry_in_response['id'] == $generated_entry->id){
                    break;
                }
                $generated_entry = null;
            }
            $this->assertNotNull($generated_entry);
            $this->assertEntryNodesExist($entry_in_response);
            $this->assertEntryNodesMatchGeneratedEntry($entry_in_response, $generated_entry);
        }
    }

}