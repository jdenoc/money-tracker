import { AccountTypes } from "./account-types";
import { Institutions } from "./institutions";
import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';

export class Accounts extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ACCOUNTS;
        this.uri = '/api/accounts';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.assign = [];
                    return {type: SnotifyStyle.info, message: "No accounts currently available"};
                case 500:
                default:
                    return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve accounts"};
            }
        }
    }

    getInstitution(accountId){
        accountId = parseInt(accountId);
        let account = this.find(accountId);
        if(account.hasOwnProperty('institution_id')){
            return new Institutions().find(account.institution_id);
        } else {
            return {};  // couldn't find the account_type associated with the provided ID
        }
    }

    getAccountTypes(accountId){
        accountId = parseInt(accountId);
        return new AccountTypes().retrieve.filter(function(accountType){
            return accountType.hasOwnProperty('account_id') && accountId === accountType.account_id;
        });
    }
}