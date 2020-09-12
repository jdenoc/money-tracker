<?php

namespace App\Traits\Tests\Dusk;

use App\Http\Controllers\Api\EntryController;
use App\Traits\MaxEntryResponseValue;
use Illuminate\Support\Collection;

trait BatchFilterEntries {

    use MaxEntryResponseValue;

    /**
     * @param array $filter_data
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    private function generateFilterArrayElementDatepicker($filter_data, $start_date, $end_date){
        $filter_data[EntryController::FILTER_KEY_START_DATE] = $start_date;
        $filter_data[EntryController::FILTER_KEY_END_DATE] = $end_date;
        return $filter_data;
    }

    /**
     * @param array $filter_data
     * @param bool $is_account_switch_toggled
     * @param int|null $account_or_account_type_id
     * @return mixed
     */
    private function generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_account_switch_toggled, $account_or_account_type_id){
        if(!empty($account_or_account_type_id)){
            if($is_account_switch_toggled){
                $filter_data[EntryController::FILTER_KEY_ACCOUNT_TYPE] = $account_or_account_type_id;
            } else {
                $filter_data[EntryController::FILTER_KEY_ACCOUNT] = $account_or_account_type_id;
            }
        }
        return $filter_data;
    }

    /**
     * @param array $filter_data
     * @param bool $is_expense
     * @return array
     */
    private function generateFilterArrayElementExpense($filter_data, $is_expense){
        $filter_data[EntryController::FILTER_KEY_EXPENSE] = $is_expense;
        return $filter_data;
    }

    /**
     * @param array $filter_data
     * @param Collection|null $tags
     * @return mixed
     */
    private function generateFilterArrayElementTags($filter_data, $tags){
        if(!is_null($tags)){
            $filter_data[EntryController::FILTER_KEY_TAGS] = $tags->pluck('id')->toArray();
        }
        return $filter_data;
    }

    /**
     * @param array $filter_data
     * @return Collection
     */
    private function getBatchedFilteredEntries($filter_data){
        $entries = $this->getApiEntries(0, $filter_data);
        if(empty($entries)){
            throw new \UnexpectedValueException("Entries not available with filter ".json_encode($filter_data));
        }

        $total_pages = (int) ceil($entries['count']/self::$MAX_ENTRIES_IN_RESPONSE);
        $entries_collection = collect($this->removeCountFromApiResponse($entries));

        for($i=1; $i<$total_pages; $i++){
            $entries_collection = $entries_collection->merge(collect($this->removeCountFromApiResponse($this->getApiEntries($i, $filter_data))));
        }
        $this->assertCount($entries['count'], $entries_collection);

        return $entries_collection;
    }

}
