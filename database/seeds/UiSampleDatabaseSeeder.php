<?php

use Illuminate\Database\Seeder;

class UiSampleDatabaseSeeder extends Seeder {

    const COUNT_ACCOUNT = 2;
    const COUNT_ACCOUNT_TYPE = 3;
    const COUNT_ATTACHMENT = 3;
    const COUNT_ENTRY = 5;
    const COUNT_INSTITUTION = 2;
    const COUNT_MIN = 1;
    const COUNT_TAG = 5;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $faker = Faker\Factory::create();

        $tags = factory(App\Tag::class, self::COUNT_TAG)->create();
        $tag_ids = $tags->pluck('id')->toArray();

        $institutions = factory(App\Institution::class, self::COUNT_INSTITUTION)->create(['active'=>1]);
        $institution_ids = $institutions->pluck('id')->toArray();

        $accounts = collect();
        foreach($institution_ids as $institution_id){
            $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$institution_id, 'disabled'=>false]);
        }
        $accounts = $this->addAccountToCollection($accounts, ['institution_id'=>$faker->randomElement($institution_ids), 'disabled'=>true]);
        $account_ids = $accounts->pluck('id')->toArray();

        $account_types = collect();
        foreach($account_ids as $account_id){
            $account_types = $this->addAccountTypeToCollection($account_types, ['account_id'=>$account_id, 'disabled'=>false], $faker);
        }
        $account_types = $this->addAccountTypeToCollection($account_types, ['account_id'=>$faker->randomElement($account_ids), 'disabled'=>true], $faker);
        $account_type_ids = $account_types->pluck('id')->toArray();

        $entries = collect();
        foreach($account_type_ids as $account_type_id){
            $entries = $this->addEntryToCollection($entries, ['account_type_id'=>$account_type_id, 'disabled'=>false], $faker);
        }
        $entries = $this->addEntryToCollection($entries, ['account_type_id'=>$faker->randomElement($account_type_ids), 'disabled'=>true], $faker);

        foreach($entries as $entry){
            if($faker->boolean){    // randomly assign tags to entries
                $this->attachTagToEntry($faker, $tag_ids, $entry);
            }
        }
        // just in case we missed an entry necessary for testing, we're going to assign tags to a random confirmed & unconfirmed entries
        $this->attachTagToEntry($faker, $tag_ids, $entries->where('confirm', 0)->random(1)->first());
        $this->attachTagToEntry($faker, $tag_ids, $entries->where('confirm', 1)->random(1)->first());

        $entry_income_ids = $entries->where('expense', 1)->pluck('id')->toArray();
        $entry_expense_ids = $entries->where('expense', 0)->pluck('id')->toArray();
        for($attachment_i=0; $attachment_i<self::COUNT_ATTACHMENT; $attachment_i++){
            factory(App\Attachment::class)->create(['entry_id'=>$faker->randomElement($entry_income_ids)]);
            factory(App\Attachment::class)->create(['entry_id'=>$faker->randomElement($entry_expense_ids)]);
        }
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
     * @return \Illuminate\Support\Collection
     */
    private function addAccountTypeToCollection($account_type_collection, $data, $faker){
        return $this->addToCollection($account_type_collection, App\AccountType::class, $data, $faker->numberBetween(self::COUNT_MIN, self::COUNT_ACCOUNT_TYPE));
    }

    /**
     * @param Illuminate\Support\Collection $entry_collection
     * @param array $data
     * * @param Faker\Generator $faker
     * @return \Illuminate\Support\Collection
     */
    private function addEntryToCollection($entry_collection, $data, $faker){
        return $this->addToCollection($entry_collection, App\Entry::class, $data, $faker->numberBetween(self::COUNT_MIN, self::COUNT_ENTRY));
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
     * @param \App\Entry $entry
     */
    private function attachTagToEntry($faker, $tag_ids, $entry){
        $entry_tag_ids = $faker->randomElements($tag_ids, $faker->numberBetween(self::COUNT_MIN, self::COUNT_TAG));
        $entry->tags()->attach($entry_tag_ids);
    }

}