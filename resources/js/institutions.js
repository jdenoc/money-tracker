import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';

export class Institutions extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_INSTITUTIONS;
        this.uri = '/api/institutions';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    return {type: SnotifyStyle.info, message: "No institutions currently available"};
                case 500:
                default:
                    return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve institutions"};
            }
        }
    }
}