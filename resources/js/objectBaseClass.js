import Axios from 'axios';
import {store} from './store';

export class ObjectBaseClass {

    constructor(){
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
        return store.getters.getStateOf(this.storeType);
    }

    get isFetched(){
        return store.getters.getFetchedState(this.storeType);
    }

    set assign(newValue){
        store.dispatch('setStateOf', {type:this.storeType, value:newValue});
    }

    set setFetchedState(newValue){
        store.dispatch('setFetchedState', {type:this.storeType, value:newValue});
    }

    processSuccessfulResponseData(responseData){
        let responseCount = parseInt(responseData.count);
        delete responseData.count;

        let objectsInResponse = Object.values(responseData);    // convert responseData object into an array
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
}