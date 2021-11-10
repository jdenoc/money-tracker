import { ObjectBaseClass } from './objectBaseClass';
import { store } from './store';

export class Institutions extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = store.getters.STORE_TYPE_INSTITUTIONS;
        this.uri = '/api/institutions';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    return {type: 'info', message: "No institutions currently available"};
                case 500:
                default:
                    return {type: "error", message: "An error occurred while attempting to retrieve institutions"};
            }
        }
    }
}