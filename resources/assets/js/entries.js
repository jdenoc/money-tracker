import { ObjectBaseClass } from './objectBaseClass';
import Axios from "axios";
import Store from './store';

export class Entries extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ENTRIES;
        this.uri = '/api/entries';
        this.sort = {parameter: 'entry_date', direction: 'desc'};
    }

    fetch(pageNumber, filterParameters = {}){
        pageNumber = parseInt(pageNumber);
        pageNumber = isNaN(pageNumber) ? 0 : pageNumber;

        let requestParameters = {};
        for(let parameter in filterParameters){
            if(filterParameters.hasOwnProperty(parameter) && filterParameters[parameter] !== null){
                requestParameters.parameter = filterParameters[parameter];
            }
        }
        requestParameters.sort = this.sort;

//         $.ajax({
//             beforeSend: function(){
//                 loading.start();
//                 filterModal.active = !$.isEmptyObject(filterParameters);
//                 paginate.filterState = filterParameters;
//             },
//             data: JSON.stringify(requestParameters),
//             dataType: 'json',
//             complete: entries.ajaxCompleteProcessing
//         });

        console.log("URL:"+this.uri+'/'+pageNumber+";\nfilter:"+requestParameters);
        return Axios.post(this.uri+'/'+pageNumber, requestParameters)
            .then(this.axiosSuccess.bind(this))
            .catch(this.axiosFailure);
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    // TODO: notify user
                    // notice.display(notice.typeInfo, "No entries were found");
                    break;
                case 500:
                default:
                    // TODO: notify user of issue
                    this.assign = [];
                    // notice.display(notice.typeDanger, "An error occurred while attempting to retrieve "+(filterModal.active?"filtered":"")+" entries");
                    break;
            }
        }
    }

    processSuccessfulResponseData(responseData){
        let responseCount = parseInt(responseData.count);
        delete responseData.count;

        // convert responseData object into an array
        let entriesInResponse = Object.values(responseData).map(function(entry){
            // add a fetchStamp property to each entry
            entry.fetchStamp = null;
            return entry;
        });
        if(responseCount !== entriesInResponse.length) {
            // FIXME: add error checking for request count values
            // notice.display(notice.typeWarning, "Not all "+storeType+" were downloaded");
        }

        return entriesInResponse;
    }

}

// var entries = {
//     value: [],
//     total: 0,

//     load: function(pageNumber){
//         entries.ajaxRequest(pageNumber, {});
//     },

//     filter: function(filterParameters, pageNumber){
//         entries.ajaxRequest(pageNumber, filterParameters);
//     },

//     display: function(){
//         entries.clearDisplay();
//         var isEntryInFuture = function(entryDate){
//             var millisecondsPerMinute = 60000;
//             var timezoneOffset = new Date().getTimezoneOffset()*millisecondsPerMinute;
//             return Date.parse(entryDate)+timezoneOffset > Date.now();
//         };
//         $.each(entries.value, function(index, entryObject){
//             entryObject.isEntryInFuture = isEntryInFuture(entryObject.entry_date);
//             var displayTags = '';
//             $.each(entryObject.tags, function(id, tagId){
//                 displayTags += '<span class="label label-default entry-tag">'+tags.getNameById(tagId)+'</span>';
//             });
//             $('#entries-display-pane tbody').append(
//                 '<tr class="'+(!entryObject.confirm ? 'warning' : (entryObject.expense ? '' : 'success'))+(entryObject.isEntryInFuture ? ' text-muted':'')+'">' +
//                 '<td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="entry.load('+entryObject.id+');">' +
//                 "\t"+'<span class="glyphicon glyphicon-pencil"></span>' +
//                 '</td>' +
//                 '<td>'+entryObject.entry_date+'</td>' +
//                 '<td>'+entryObject.memo+'</td>' +
//                 '<td class="value-col">'+(entryObject.expense ? '' : '$'+entryObject.entry_value)+'</td>' +
//                 '<td class="value-col">'+(entryObject.expense ? '$'+entryObject.entry_value : '')+'</td>' +
//                 '<td>'+accountTypes.getNameById(entryObject.account_type_id)+'</td>' +
//                 '<td><span class="glyphicon glyphicon-'+(entryObject.has_attachments ? 'check' : 'unchecked')+'" aria-hidden="true"></span></td>' +
//                 '<td>'+displayTags+'</td>' +
//                 '</tr>'
//             );
//         });
//     },

//     clearDisplay: function(){
//         $("#entries-display-pane tbody tr").remove();
//     },

//     ajaxCompleteProcessing: function(){
//         $('.is-filtered').toggle(filterModal.active);
//         entries.display();
//         paginate.display.previous(paginate.current !== 0);
//         paginate.display.next(paginate.current < Math.ceil(entries.total/50)-1);
//         loading.end();
//     },

//     reload: function(pageNumber, filterParameters){
//         pageNumber = paginate.processPageNumber(pageNumber);
//         if(filterParameters){
//             entries.filter(filterParameters, pageNumber);
//         } else {
//             entries.load(pageNumber);
//         }
//     }
// };



// var entry = {
//     uri: "/api/entry",
//     value: [],

//     save: function(entryData){
//         var entryId = parseInt(entryData.id);
//         delete entryData.id;
//         var ajaxEntryData = JSON.stringify(entryData);
//         if($.isNumeric(entryId)){
//             // update entry
//             $.ajax({
//                 url: entry.uri+'/'+entryId,
//                 method: 'PUT',
//                 beforeSend: loading.start,
//                 data: ajaxEntryData,
//                 statusCode: {
//                     200: function(){
//                         notice.display(notice.typeSuccess, "Entry updated");
//                     },
//                     400: function(responseObject){
//                         notice.display(notice.typeWarning, responseObject.responseJSON.error);
//                     },
//                     404: function(responseObject){
//                         notice.display(notice.typeWarning, responseObject.responseJSON.error);
//                     },
//                     500: function(){
//                         notice.display(notice.typeError, "An error occurred while attempting to update entry ["+entryId+"]");
//                     }
//                 },
//                 complete: entry.completeEntryUpdate
//             });
//         } else {
//             // new entry
//             $.ajax({
//                 url: entry.uri,
//                 method: 'POST',
//                 beforeSend: loading.start,
//                 data: ajaxEntryData,
//                 statusCode: {
//                     201: function(){
//                         notice.display(notice.typeSuccess, "New entry created");
//                     },
//                     400: function(responseObject){
//                         notice.display(notice.typeWarning, responseObject.responseJSON.error);
//                     },
//                     500: function(){
//                         notice.display(notice.typeError, "An error occurred while attempting to create an entry");
//                     }
//                 },
//                 complete: entry.completeEntryUpdate
//             });
//         }
//     },

//     delete: function(entryId){
//         $.ajax({
//             url: entry.uri+'/'+entryId,
//             method: 'delete',
//             beforeSend: loading.start,
//             dataType: 'json',
//             statusCode: {
//                 204: function(){
//                     notice.display(notice.typeSuccess, "Entry was deleted");
//                 },
//                 404: function(){
//                     notice.display(notice.typeWarning, "Entry ["+entryId+"] does not exist and cannot be deleted");
//                 },
//                 500: function(){
//                     notice.display(notice.typeError, "An error occurred while attempting to delete entry ["+entryId+"]");
//                 }
//             },
//             complete: entry.completeEntryUpdate
//         });
//     },

//     completeEntryUpdate: function(){
//         // re-display entries
//         entries.reload(paginate.current, paginate.filterState);
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
// };