<?php

use App\Http\Controllers\Api\EntryController;
use Illuminate\Database\Seeder;

class UiSampleDatabaseSeeder extends Seeder {

    const COUNT_ACCOUNT = 2;
    const COUNT_ACCOUNT_TYPE = 3;
    const COUNT_ATTACHMENT = 4;
    const COUNT_ENTRY = 5;
    const COUNT_INSTITUTION = 2;
    const COUNT_MIN = 1;
    const COUNT_TAG = 5;

    const OUTPUT_PREFIX = "<info>".__CLASS__.":</info> ";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $faker = Faker\Factory::create();

        // ***** TAGS *****
        $tags = factory(App\Tag::class, self::COUNT_TAG)->create();
        $tag_ids = $tags->pluck('id')->toArray();
        $this->command->line(self::OUTPUT_PREFIX."Tags seeded [".$tags->count()."]");

        // ***** INSTITUTIONS *****
        $institutions = factory(App\Institution::class, self::COUNT_INSTITUTION)->create(['active'=>1]);
        $institution_ids = $institutions->pluck('id')->toArray();
        $this->command->line(self::OUTPUT_PREFIX."Institutions seeded [".$institutions->count()."]");

        // ***** ACCOUNTS *****
        $accounts = collect();
        foreach($institution_ids as $institution_id){
            $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$institution_id, 'disabled'=>false]);
        }
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'disabled'=>true]);
        // See resources/assets/js/currency.js for list of supported currencies
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'currency'=>'USD']);
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'currency'=>'CAD']);
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'currency'=>'EUR']);
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'currency'=>'GBP']);
        $this->command->line(self::OUTPUT_PREFIX."Accounts seeded [".$accounts->count()."]");

        // ***** ACCOUNT-TYPES *****
        $account_types = collect();
        foreach($accounts->pluck('id') as $account_id){
            $account_types = $this->addAccountTypeToCollection($account_types, ['account_id'=>$account_id, 'disabled'=>false], $faker);
        }
        $account_types = $this->addAccountTypeToCollection($account_types, ['account_id'=>$accounts->where('disabled', false)->pluck('id')->random(), 'disabled'=>true], $faker);
        $account_types = $this->addAccountTypeToCollection($account_types, ['account_id'=>$accounts->where('disabled', true)->pluck('id')->random(), 'disabled'=>true], $faker);
        $this->command->line(self::OUTPUT_PREFIX."Account-types seeded [".$account_types->count()."]");

        // ***** ENTRIES *****
        $entries = collect();
        foreach($account_types->pluck('id') as $account_type_id){
            $entries = $this->addEntryToCollection($entries, ['account_type_id'=>$account_type_id, 'disabled'=>false], $faker);
        }
        $entries = $this->addEntryToCollection($entries, ['account_type_id'=>$account_types->pluck('id')->random(), 'disabled'=>true], $faker);
        $this->command->line(self::OUTPUT_PREFIX."Entries seeded [".$entries->count()."]");

        foreach($entries as $entry){
            if($faker->boolean){    // randomly assign tags to entries
                $this->attachTagToEntry($faker, $tag_ids, $entry);
            }
        }

        // no point in selecting disabled entries. they're not going to be tested.
        $entries_not_disabled = $entries->where('disabled', 0);
        $entries_confirmed = $entries_not_disabled->where('confirm', 1);
        $entries_not_confirmed = $entries_not_disabled->where('confirm', 0);

        // just in case we missed an entry necessary for testing, we're going to assign tags to random confirmed & unconfirmed entries
        $this->attachTagToEntry($faker, $tag_ids, $entries_not_confirmed->where('expense', 1)->random());
        $this->attachTagToEntry($faker, $tag_ids, $entries_not_confirmed->where('expense', 0)->random());
        $this->attachTagToEntry($faker, $tag_ids, $entries_confirmed->where('expense', 1)->random());
        $this->attachTagToEntry($faker, $tag_ids, $entries_confirmed->where('expense', 0)->random());
        $this->command->line(self::OUTPUT_PREFIX."Randomly assigned tags to entries");

        // randomly select some entries and mark them as transfers
        $transfer_to_entries = $entries_not_disabled->where('expense', 0)->random(self::COUNT_ENTRY);
        $transfer_from_entries = $entries_not_disabled->where('expense', 1)->random(self::COUNT_ENTRY);
        for($transfer_i=0; $transfer_i<self::COUNT_ENTRY; $transfer_i++){
            $transfer_from_entry = $transfer_from_entries->get($transfer_i);
            $transfer_to_entry = $transfer_to_entries->get($transfer_i);
            $transfer_from_entry->transfer_entry_id = $transfer_to_entry->id;
            $transfer_from_entry->save();
            $transfer_to_entry->transfer_entry_id = $transfer_from_entry->id;
            $transfer_to_entry->save();
        }
        $external_transfer_entry = $entries_not_disabled->where('transfer_entry_id', null)
            ->sortByDesc('entry_date')->chunk(EntryController::MAX_ENTRIES_IN_RESPONSE)[0]->random();
        $external_transfer_entry->transfer_entry_id = EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
        $external_transfer_entry->save();
        $this->command->line(self::OUTPUT_PREFIX."Randomly marked entries as transfers");

        // assign attachments to entries. if entry is a "transfer", then add an attachment of the same name to its counterpart
        for($attachment_i=0; $attachment_i<self::COUNT_ATTACHMENT; $attachment_i++){
            // income entries
            $entry_income_ids = $entries_not_disabled->where('expense', 0)->pluck('id')->toArray();
            $this->assignAttachmentToEntry($faker, $entry_income_ids, $transfer_to_entries->pluck('id')->toArray(), $entries);
            // expense entries
            $entry_expense_ids = $entries_not_disabled->where('expense', 1)->pluck('id')->toArray();
            $this->assignAttachmentToEntry($faker, $entry_expense_ids, $transfer_from_entries->pluck('id')->toArray(), $entries);
        }
        $this->command->line(self::OUTPUT_PREFIX."Randomly assigned Attachments to entries");

        // income confirmed
        $this->assignAttachmentToEntry(
            $faker,
            $entries_confirmed->where('expense', 0)->pluck('id')->toArray(),
            $transfer_to_entries->pluck('id')->toArray(),
            $entries
        );
        // income unconfirmed
        $this->assignAttachmentToEntry(
            $faker,
            $entries_not_confirmed->where('expense', 0)->pluck('id')->toArray(),
            $transfer_to_entries->pluck('id')->toArray(),
            $entries
        );
        // expense confirmed
        $this->assignAttachmentToEntry(
            $faker,
            $entries_confirmed->where('expense', 1)->pluck('id')->toArray(),
            $transfer_from_entries->pluck('id')->toArray(),
            $entries
        );
        // expense unconfirmed
        $this->assignAttachmentToEntry(
            $faker,
            $entries_not_confirmed->where('expense', 1)->pluck('id')->toArray(),
            $transfer_from_entries->pluck('id')->toArray(),
            $entries
        );
        $this->command->line(self::OUTPUT_PREFIX."Assigned Attachments to all varieties of entries");
    }

    /**
     * @param Illuminate\Support\Collection $account_collection
     * @param array $data
     * @return Illuminate\Support\Collection
     */
    private function addAccountToCollection($account_collection, $data){
        return $this->addToCollection($account_collection, App\Account::class, $data, self::COUNT_ACCOUNT);
    }

    /**
     * @param Illuminate\Support\Collection $account_type_collection
     * @param array $data
     * @param Faker\Generator $faker
     * @return Illuminate\Support\Collection
     */
    private function addAccountTypeToCollection($account_type_collection, $data, $faker){
        return $this->addToCollection($account_type_collection, App\AccountType::class, $data, $faker->numberBetween(self::COUNT_MIN, self::COUNT_ACCOUNT_TYPE));
    }

    /**
     * @param Illuminate\Support\Collection $entry_collection
     * @param array $data
     * * @param Faker\Generator $faker
     * @return Illuminate\Support\Collection
     */
    private function addEntryToCollection($entry_collection, $data, $faker){
        return $this->addToCollection($entry_collection, App\Entry::class, $data, $faker->numberBetween(self::COUNT_MIN, self::COUNT_ENTRY*2));
    }

    /**
     * @param Illuminate\Support\Collection $collection
     * @param $type_class
     * @param array $data
     * @param int $count
     * @return Illuminate\Support\Collection mixed
     */
    private function addToCollection($collection, $type_class, $data, $count=1){
        $object = factory($type_class, $count)->create($data);  // when passing a count value to a factory, a collection is ALWAYS returned
        return $collection->merge($object);
    }

    /**
     * @param Faker\Generator $faker
     * @param int[] $tag_ids
     * @param App\Entry $entry
     */
    private function attachTagToEntry($faker, $tag_ids, $entry){
        $entry_tag_ids = $faker->randomElements($tag_ids, $faker->numberBetween(self::COUNT_MIN, self::COUNT_TAG));
        $entry->tags()->attach($entry_tag_ids);
    }

    /**
     * @param Faker\Generator $faker
     * @param int[] $entry_ids
     * @param int[] $transfer_entry_ids
     * @param Illuminate\Support\Collection $entries_collection
     */
    private function assignAttachmentToEntry($faker, $entry_ids, $transfer_entry_ids, $entries_collection){
        $random_entry_id = $faker->randomElement($entry_ids);
        if(in_array($random_entry_id, $transfer_entry_ids)){
            $new_attachment = factory(App\Attachment::class)->create(['entry_id'=>$random_entry_id]);
            $transfer_entry = $entries_collection->where('id', $random_entry_id)->first();
            factory(App\Attachment::class)->create(['entry_id'=>$transfer_entry->transfer_entry_id, 'name'=>$new_attachment->name]);
        } else {
            factory(App\Attachment::class)->create(['entry_id'=>$random_entry_id]);
        }
    }

}