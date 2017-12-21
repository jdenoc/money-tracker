<?php

use Illuminate\Database\Seeder;

class UiSampleDatabaseSeeder extends Seeder {

    const COUNT_TAG = 3;
    const COUNT_INSTITUTION = 2;
    const COUNT_ACCOUNT = 4;
    const COUNT_ACCOUNT_TYPE = 6;
    const COUNT_ENTRY = 10;
    const COUNT_ATTACHMENT = 2;

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
        for($account_i=0; $account_i<self::COUNT_ACCOUNT; $account_i++){
            $account = factory(App\Account::class)->create(['institution_id'=>$faker->randomElement($institution_ids)]);
            $accounts->push($account);
            unset($account);
        }
        $account_ids = $accounts->pluck('id')->toArray();

        $account_types = collect();
        for($account_type_i=0; $account_type_i<self::COUNT_ACCOUNT_TYPE; $account_type_i++){
            $account_type = factory(App\AccountType::class)->create(['account_id'=>$faker->randomElement($account_ids)]);
            $account_types->push($account_type);
            unset($account_type);
        }
        $account_type_ids = $account_types->pluck('id')->toArray();

        $entries = collect();
        for($entry_i=0; $entry_i<self::COUNT_ENTRY; $entry_i++){
            $entry = factory(App\Entry::class)->create(['account_type_id'=>$faker->randomElement($account_type_ids)]);
            if($faker->boolean){    // randomly assign tags to entries
                $entry_tag_ids = $faker->randomElements($tag_ids, $faker->numberBetween(1, self::COUNT_TAG));
                $entry->tags()->attach($entry_tag_ids);
            }
            $entries->push($entry);
            unset($entry, $entry_tag_ids);
        }
        $entry_ids = $entries->pluck('id')->toArray();

        for($attachment_i=0; $attachment_i<self::COUNT_ATTACHMENT; $attachment_i++){
            factory(App\Attachment::class)->create(['entry_id'=>$faker->randomElement($entry_ids)]);
        }


    }

}