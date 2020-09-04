/**
 * Created by denis.oconnor on 2018-08-20
 */

import _ from 'lodash';
import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
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
            .then(this.axiosSuccess.bind(this)) // NOTE: _DO NOT_ remove the ".bind(this)". Will not work without.
            .catch(this.axiosFailure);
    }

    save(entryData){
        let entryId = parseInt(entryData.id);
        delete entryData.id;
        if(_.isNumber(entryId) && !isNaN(entryId)){
            // update entry
            return Axios.put(this.uri+entryId, entryData)
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
                // complete: entry.completeEntryUpdate
        } else {
            // new entry
            return Axios
                .post(this.uri.replace(/\/$/, ''), entryData, {validateStatus:function(status){
                    return status === 201
                }})
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
                // complete: entry.completeEntryUpdate
        }
    }

    saveTransfer(transferData){
        return Axios.post(this.uri+'transfer', transferData)
            .then(this.axiosSuccessTransfer)
            .catch(this.axiosFailure);
    }

    delete(entryId){
        return Axios
            .delete(this.uri+entryId, {validateStatus: function(status){
                return status === 204;
            }})
            .then(this.axiosSuccess)
            .catch(this.axiosFailure);
            // complete: entry.completeEntryUpdate
    }

    set assign(newValue){
        if(!_.isEmpty(newValue)){
            let entries = this.retrieve;
            let entryIndex = entries.findIndex(function(entry){
                return entry.id === newValue.id;
            });
            if(entryIndex !== -1){
                entries[entryIndex] = newValue;
            } else {
                entries[entries.length] = newValue; // entries.length will always be an index above the current highest index
            }
            Store.dispatch('setStateOf', {type:this.storeType, value:entries});
        }
    }

    axiosSuccess(response){
        switch(response.config.method.toUpperCase()){
            case 'GET':
                this.assign = this.processSuccessfulResponseData(response.data);
                return {fetched: true, notification: {}};
            case "POST":
                return {type: SnotifyStyle.success, message: "New entry created"};
            case "PUT":
                return {type: SnotifyStyle.success, message: "Entry updated"};
            case "DELETE":
                return {deleted: true, notification: {type: SnotifyStyle.success, message: "Entry was deleted"}}
        }
    }

    axiosSuccessTransfer(response){
        return {type: SnotifyStyle.success, message: "Transfer entry created"};
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.config.method.toUpperCase()){
                case "GET":
                    switch(error.response.status){
                        case 404:
                            return {fetched: false, notification: {type: SnotifyStyle.warning, message: "Entry does not exist"}};
                        case 500:
                        default:
                            return {fetched: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve entry"}};
                    }
                case "POST":
                    switch(error.response.status){
                        case 400:
                            return {type: SnotifyStyle.warning, message: error.response.data.error};
                        case 500:
                        default:
                            return {type: SnotifyStyle.error, message: "An error occurred while attempting to create an entry"};
                    }
                case "PUT":
                    switch(error.response.status){
                        case 400:
                        case 404:
                            return {type: SnotifyStyle.warning, message: error.response.data.error};
                        case 500:
                        default:
                            return {type: SnotifyStyle.error, message: "An error occurred while attempting to update entry [%s]"};
                    }
                case "DELETE":
                    switch(error.response.status){
                        case 404:
                            return {deleted: false, notification: {type: SnotifyStyle.warning, message: "Entry [%s] does not exist and cannot be deleted"}};
                        case 500:
                        default:
                            return {deleted: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to delete entry [%s]"}};
                    }
            }
        }
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