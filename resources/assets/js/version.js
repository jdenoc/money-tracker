import { ObjectBaseClass } from './objectBaseClass';
import Store from './store';

export class Version extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_VERSION;
        this.uri = '/api/version';
    }

    axiosSuccess(response){
        this.set(response);
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                case 500:
                default:
                    this.set("N/A");
                    break;
            }
        }
    }
}

// var version = {
//     display: function(){
//         $('#app-version').text(version.value);
//     }
// };
