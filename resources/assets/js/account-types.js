import Store from './store';
import {ObjectBaseClass} from "./objectBaseClass";
import {Accounts} from "./accounts";

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
                    // TODO: inform user
                    // notice.display(notice.typeInfo, "No account types available");
                    break;
                case 500:
                default:
                    // TODO: inform user of issue
                    // notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve account types');
                    break;
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

//     display: function(){
//         entryModal.initAccountTypeSelect();
//         filterModal.initAccountTypeSelect();
//         institutionsPane.displayAccountTypes();
//     },