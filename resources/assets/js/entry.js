/**
 * Created by denis.oconnor on 2018-08-20
 */

import _ from 'lodash';
import { ObjectBaseClass } from './objectBaseClass';
import Axios from "axios";
import Store from './store';

export class Entry extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ENTRIES;
        this.uri = '/api/entry/';
        this.fiveMinutesInMilliseconds = 5*60*1000;
        this.fetched = false;
    }

    fetch(entryId){
        return Axios.get(this.uri+entryId)
            .then(this.axiosSuccess.bind(this))
            .catch(this.axiosFailure);
    }

    delete(entryId){
        return Axios
            .delete(this.uri+entryId, {validateStatus: function(status){
                return status === 204;
            }})
            .then(function(response){
                // TODO: send notice of successful deletion
                //   notice.display(notice.typeSuccess, "Entry was deleted");
            }).catch(this.axiosFailure);
    }

    set assign(newValue){
        if(newValue !== []){
            let entries = this.retrieve;
            let entryIndex = entries.findIndex(function(entry){
                return entry.id === newValue.id;
            });
            entries[entryIndex] = newValue;
            Store.dispatch('setStateOf', {type:this.storeType, value:entries});
        }
    }

    axiosSuccess(response){
        this.assign = this.processSuccessfulResponseData(response.data);
        this.fetched = true;
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    // TODO: send a notice - get entry
                    // notice.display(notice.typeWarning, "Entry does not exist");
                    // TODO: send a notice - delete entry
                    // notice.display(notice.typeWarning, "Entry ["+entryId+"] does not exist and cannot be deleted");
                    break;
                case 500:
                default:
                    // TODO: send a notice - get entry
                    // notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve entries');
                    // TODO: send a notice - delete entry
                    // notice.display(notice.typeError, "An error occurred while attempting to delete entry ["+entryId+"]");
                    break;
            }
        }
        this.fetched = true;
    }

    processSuccessfulResponseData(responseData){
        if(!_.isEmpty(responseData)){
            responseData = this.updateEntryFetchStamp(responseData)
        }
        return responseData;
    }

    isEntryCurrent(entryData){
        let currentTimestamp = new Date().getTime();
        if(entryData.hasOwnProperty('fetchStamp')){
            return Math.abs(currentTimestamp - entryData.fetchStamp) < this.fiveMinutesInMilliseconds;
        } else {
            return false;
        }
    }

    updateEntryFetchStamp(entryData){
        entryData.fetchStamp = new Date().getTime();
        return entryData;
    }

}