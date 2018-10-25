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
            entries[entryIndex] = newValue;
            Store.dispatch('setStateOf', {type:this.storeType, value:entries});
        }
    }

    axiosSuccess(response){
        switch(response.config.method.toUpperCase()){
            case 'GET':
                this.assign = this.processSuccessfulResponseData(response.data);
                this.fetched = true;
                break;

            case "POST":
                // TODO: send notice of successful entry creation
                // notice.display(notice.typeSuccess, "New entry created");
                break;

            case "PUT":
                // TODO: send notice of successful entry update
                // notice.display(notice.typeSuccess, "Entry updated");
                break;

            case "DELETE":
                // TODO: send notice of successful deletion
                // notice.display(notice.typeSuccess, "Entry was deleted");
                break;
        }
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.config.method.toUpperCase()){
                case "GET":
                    switch(error.response.status){
                        case 404:
                            // TODO: send a notice
                            // notice.display(notice.typeWarning, "Entry does not exist");
                            break;
                        case 500:
                        default:
                            // TODO: send a notice
                            // notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve entries');
                    }
                    this.fetched = true;
                    break;

                case "POST":
                    switch(error.response.status){
                        case 400:
                            // TODO: send a notice
                            // notice.display(notice.typeWarning, error.response.error);
                            break;
                        case 500:
                        default:
                            // TODO: send a notice
                            // notice.display(notice.typeError, "An error occurred while attempting to create an entry");
                    }
                    break;

                case "PUT":
                    switch(error.response.status){
                        case 400:
                        case 404:
                            // TODO: send a notice
                            // notice.display(notice.typeWarning, error.response.error);
                            break;
                        case 500:
                        default:
                            // TODO: send a notice
                            // notice.display(notice.typeError, "An error occurred while attempting to update entry ["+entryId+"]");
                    }
                    break;

                case "DELETE":
                    switch(error.response.status){
                        case 404:
                            // TODO: send a notice - delete entry
                            // notice.display(notice.typeWarning, "Entry ["+entryId+"] does not exist and cannot be deleted");
                            break;
                        case 500:
                        default:
                            // TODO: send a notice - delete entry
                            // notice.display(notice.typeError, "An error occurred while attempting to delete entry ["+entryId+"]");
                    }
                    break;
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

//     completeEntryUpdate: function(){
//         // re-display institutes-pane contents
//         institutionsPane.clear();
//         institutionsPane.displayInstitutions();
//         accounts.load();
//         institutionsPane.displayAccountTypes();
//
//         var intervalSetAccountActive = setInterval(function(){
//             // need to wait for the accounts to be re-displayed
//             if(institutionsPane.accountsDisplayed){
//                 var accountId = ($.isEmptyObject(paginate.filterState)) ? '' : paginate.filterState.account;
//                 institutionsPane.setActiveState(accountId);
//                 clearInterval(intervalSetAccountActive);    // stops interval from running again
//             }
//         }, 50);
//     }