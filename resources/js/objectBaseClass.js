import Axios from 'axios';
import Store from './store';

export class ObjectBaseClass {

    constructor(){
        this.fiveMinutesInMilliseconds = 5*60*1000;

        this.storeType = '';
        this.uri = '';
    }

    fetch(){
        if(!this.isFetched){
            this.setFetchedState = true;
            return Axios.get(this.uri)
                .then(this.axiosSuccess.bind(this))
                .catch(this.axiosFailure.bind(this));
        } else {
            return Promise.resolve({});
        }
    }

    axiosSuccess(response){
        this.assign = this.processSuccessfulResponseData(response.data);
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    // TODO: fill in stuff here
                    break;
                case 500:
                default:
                    // TODO: fill in stuff here
                    break;
            }
        }
    }

    get retrieve(){
        return Store.getters.getStateOf(this.storeType);
    }

    get isFetched(){
        return Store.getters.getFetchedState(this.storeType);
    }

    set assign(newValue){
        Store.dispatch('setStateOf', {type:this.storeType, value:newValue});
    }

    set setFetchedState(newValue){
        Store.dispatch('setFetchedState', {type:this.storeType, value:newValue});
    }

    processSuccessfulResponseData(responseData){
        let responseCount = parseInt(responseData.count);
        delete responseData.count;

        // convert responseData object into an array
        let objectsInResponse = Object.values(responseData)
            .map(function(data){
                // add a fetchStamp property to each data node
                data.fetchStamp = null;
                return data;
            });
        if(responseCount !== objectsInResponse.length) {
            // FIXME: add error checking for request count values
            // notice.display(notice.typeWarning, "Not all "+storeType+" were downloaded");
        }

        return objectsInResponse;
    }

    find(id){
        id = parseInt(id);
        let foundObjects = this.retrieve.filter(function(object){
            return id === object.id;
        });
        if(foundObjects.length > 0){
            return foundObjects[0];
        } else {
            return {};  // could not find an object associated with the provided ID
        }
    }

    isDataUpToDate(data){
        if(data.hasOwnProperty('fetchStamp')){
            let currentTimestamp = new Date().getTime();
            return Math.abs(currentTimestamp - data.fetchStamp) < this.fiveMinutesInMilliseconds;
        } else {
            return false;
        }
    }
}