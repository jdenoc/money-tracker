import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';

export class Tags extends ObjectBaseClass {
    
    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_TAGS;
        this.uri = '/api/tags';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    break;
                case 500:
                default:
                    return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve tags"};
            }
        }
    }

    getNameById(id){
        let tag = this.find(id);
        return (tag.hasOwnProperty('name')) ? tag.name : '';
    }

    getAllNames(){
        return this.get().map(function(element){
            return element.name;
        });
    }

}