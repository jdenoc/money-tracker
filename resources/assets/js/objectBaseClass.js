import Axios from 'axios';
import Store from './store';

export class ObjectBaseClass {

    constructor(){
        this.storeType = '';
        this.uri = '';
    }

    fetch(){
        console.log(this.uri);
        return Axios.get(this.uri)
            .then(this.axiosSuccess.bind(this))
            .catch(this.axiosFailure.bind(this));
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

    set assign(newValue){
        Store.dispatch('setStateOf', {type:this.storeType, value:newValue});
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