import { ObjectBaseClass } from './objectBaseClass';
import {store} from './store';

export class Version extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = store.getters.STORE_TYPE_VERSION;
        this.uri = '/api/version';
    }

    axiosSuccess(response){
        this.assign = response.data;
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                case 500:
                default:
                    this.assign = "N/A";
                    break;
            }
        }
    }
}
