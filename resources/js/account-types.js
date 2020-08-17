import {Accounts} from "./accounts";
import {ObjectBaseClass} from "./objectBaseClass";
import {SnotifyStyle} from "vue-snotify";
import Store from './store';

export class AccountTypes extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ACCOUNT_TYPES;
        this.uri = '/api/account-types';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    return {type: SnotifyStyle.info, message: "No account types currently available"};
                case 500:
                default:
                    return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve account types"};
            }
        }
    }

    getAccount(accountTypeId){
        let accountType = this.find(accountTypeId);
        if(accountType.hasOwnProperty('account_id')){
            return new Accounts().find(accountType.account_id);
        } else {
            return {};  // couldn't find the account_type associated with the provided ID
        }
    }

    getNameById(accountTypeId) {
        let accountType = this.find(accountTypeId);
        if(accountType.hasOwnProperty('name')){
            return accountType.name;
        } else {
            return "";  // couldn't find the account_type.name associated with the provided ID
        }
    }
}