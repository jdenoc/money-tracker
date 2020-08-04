import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import Store from './store';

export class Entries extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ENTRIES;
        this.uri = '/api/entries/';
        this.sort = {parameter: 'entry_date', direction: 'desc'};
        this.count = 0;
    }

    fetch(pageNumber, filterParameters = {}){
        pageNumber = parseInt(pageNumber);
        pageNumber = isNaN(pageNumber) ? 0 : pageNumber;

        let requestParameters = {};
        for(let parameter in filterParameters){
            if(filterParameters.hasOwnProperty(parameter)
                && !_.isNull(filterParameters[parameter])
                && (
                    !_.isEmpty(filterParameters[parameter])
                    || _.isBoolean(filterParameters[parameter])
                    || (_.isNumber(filterParameters[parameter]) && filterParameters[parameter] !== 0)
                )
            ){
                requestParameters[parameter] = filterParameters[parameter];
            }
        }
        requestParameters.sort = this.sort;

        return Axios.post(this.uri+pageNumber, requestParameters)
            .then(this.axiosSuccess.bind(this))
            .catch(this.axiosFailure.bind(this));
    }

    axiosFailure(error){
        if(error.response){
            this.count = 0;
            this.assign = [];
            switch(error.response.status){
                case 404:
                    return {type: SnotifyStyle.info, message: "No entries were found"};
                case 500:
                default:
                    // return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve "+(filterModal.active?"filtered":"")+" entries"};    // TODO: after filtering is in place
                    return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve entries"};
            }
        }
    }

    processSuccessfulResponseData(responseData){
        this.count = parseInt(responseData.count);
        delete responseData.count;

        // convert responseData object into an array
        let entriesInResponse = Object.values(responseData).map(function(entry){
            // add a fetchStamp property to each entry
            entry.fetchStamp = null;
            return entry;
        });
        if(this.count !== entriesInResponse.length) {
            // FIXME: add error checking for request count values
            // notice.display(notice.typeWarning, "Not all "+storeType+" were downloaded");
        }

        return entriesInResponse;
    }

}

// var entries = {
//     ajaxCompleteProcessing: function(){
//         $('.is-filtered').toggle(filterModal.active);
//         entries.display();
//         paginate.display.previous(paginate.current !== 0);
//         paginate.display.next(paginate.current < Math.ceil(entries.total/50)-1);
//         loading.end();
//     },
// };