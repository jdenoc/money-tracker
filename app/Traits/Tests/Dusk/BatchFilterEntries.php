<?php

namespace App\Traits\Tests\Dusk;

use App\Http\Controllers\Api\EntryController;
use Illuminate\Support\Collection;

trait BatchFilterEntries {

    /**
     * @param string $start_date
     * @param string $end_date
     * @param int|string $account_or_account_type_id
     * @param bool $is_switch_toggled
     * @param Collection|null $tags
     * @return Collection
     */
    private function getBatchedFilteredEntries($start_date, $end_date, $account_or_account_type_id, $is_switch_toggled, $tags=null){
        $filter_data = [
            EntryController::FILTER_KEY_START_DATE=>$start_date,
            EntryController::FILTER_KEY_END_DATE=>$end_date,
        ];

        if(!empty($account_or_account_type_id)){
            if($is_switch_toggled){
                $filter_data[EntryController::FILTER_KEY_ACCOUNT_TYPE] = $account_or_account_type_id;
            } else {
                $filter_data[EntryController::FILTER_KEY_ACCOUNT] = $account_or_account_type_id;
            }
        }

        if(!is_null($tags)){
            $filter_data[EntryController::FILTER_KEY_TAGS] = $tags->pluck('id')->toArray();
        }

        $entries = $this->getApiEntries(0, $filter_data);
        if(empty($entries)){
            throw new \UnexpectedValueException("Entries not available with filter ".print_r($filter_data, true));
        }

        $total_pages = intval( ceil($entries['count']/EntryController::MAX_ENTRIES_IN_RESPONSE) );
        $entries_collection = collect($this->removeCountFromApiResponse($entries));

        for($i=1; $i<$total_pages; $i++){
            $entries_collection = $entries_collection->merge(collect($this->removeCountFromApiResponse($this->getApiEntries($i, $filter_data))));
        }
        $this->assertCount($entries['count'], $entries_collection);

        return $entries_collection;
    }

}
