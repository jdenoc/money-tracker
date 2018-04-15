import Store from './store';
import { ObjectBaseClass } from './objectBaseClass';
import { Institutions } from "./institutions";
import { AccountTypes } from "./account-types";

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
                    // TODO: notify user
                    // notice.display(notice.typeInfo, "No accounts currently available");
                    break;
                case 500:
                default:
                    // TODO: notify user of issue
                    // notice.display(notice.typeDanger, "Error occurred when attempting to retrieve accounts");
                    break;
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

// var accounts = {
//     display: function(){
//         institutionsPane.displayAccounts();
//     }
// };