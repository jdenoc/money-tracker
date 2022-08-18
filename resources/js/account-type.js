import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';
import Axios from "axios";
import _ from "lodash";

export class AccountType extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_ACCOUNT_TYPES;
        this.uri = '/api/account-type/';
        // this.fetched = false;
    }

    set assign(newValue){
        if(!_.isEmpty(newValue)){
            let accountTypes = this.retrieve;
            let accountTypeIndex = accountTypes.findIndex(function(accountType){
                return accountType.id === newValue.id;
            });
            if(accountTypeIndex !== -1){
                accountTypes[accountTypeIndex] = newValue;
            } else {
                accountTypes[accountTypes.length] = newValue; // entries.length will always be an index above the current highest index
            }
            super.assign = accountTypes;
        }
    }

    fetch(accountTypeId){
        return Axios.get(this.uri+accountTypeId)
            .then(this.axiosSuccess.bind(this)) // NOTE: _DO NOT_ remove the ".bind(this)". Will not work without.
            .catch(this.axiosFailure);
    }

    processSuccessfulResponseData(responseData){
        if(!_.isEmpty(responseData)){
            responseData = this.updateAccountTypeFetchStamp(responseData)
        }
        return responseData;
    }

    updateAccountTypeFetchStamp(accountTypeData){
        accountTypeData.fetchStamp = new Date().getTime();
        return accountTypeData;
    }

    axiosSuccess(response){
        switch(response.config.method.toUpperCase()){
            case 'GET':
                this.assign = this.processSuccessfulResponseData(response.data);
                return {fetched: true, notification: {}};
            case "POST":
                return {type: SnotifyStyle.success, message: "New Account-type created"};
            case "PUT":
                return {type: SnotifyStyle.success, message: "Account-type updated"};
            // case "DELETE":
            //     return {deleted: true, notification: {type: SnotifyStyle.success, message: "Account was deleted"}}
        }
    }

    save(accountTypeData){
        let accountId = parseInt(accountTypeData.id);
        delete accountTypeData.id;
        if(_.isNumber(accountId) && !isNaN(accountId)){
            // update account
            return Axios.put(this.uri+accountId, accountTypeData)
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
        } else {
            // new account
            return Axios
                .post(
                    this.uri.replace(/\/$/, ''),
                    accountTypeData,
                    {validateStatus:function(status){
                        return status === 201
                    }}
                )
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
        }
    }

    // axiosFailure(error){
    //     if(error.response){
    //         switch(error.response.status){
    //             case 404:
    //                 this.assign = [];
    //                 return {type: SnotifyStyle.info, message: "No account-type currently available"};
    //             case 500:
    //             default:
    //                 return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve account-type"};
    //         }
    //     }
    // }

}