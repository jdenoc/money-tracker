import { ObjectBaseClass } from './objectBaseClass';
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
                // TODO: notify users
                //  notice.display(notice.typeInfo, "No institutions currently available");
                case 500:
                default:
                    // TODO: notify users of issue
                    //  notice.display(notice.typeDanger, "Error occurred when attempting to retrieve institutions");
                    break;
            }
        }
    }
}

// var institutions = {
//     display: function(){
//         institutionsPane.clear();
//         institutionsPane.displayInstitutions();
//     }
// };