import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';
import Axios from "axios";
import _ from "lodash";

export class Account extends ObjectBaseClass {

  constructor(){
    super();
    this.storeType = Store.getters.STORE_TYPE_ACCOUNTS;
    this.uri = '/api/account/';
    // this.fetched = false;
  }

  set assign(newValue){
    if(!_.isEmpty(newValue)){
      let accounts = this.retrieve;
      let accountIndex = accounts.findIndex(function(account){
        return account.id === newValue.id;
      });
      if(accountIndex !== -1){
        accounts[accountIndex] = newValue;
      } else {
        accounts[accounts.length] = newValue; // entries.length will always be an index above the current highest index
      }
      super.assign = accounts;
    }
  }

  fetch(accountId){
    return Axios.get(this.uri+accountId)
      .then(this.axiosSuccess.bind(this)) // NOTE: _DO NOT_ remove the ".bind(this)". Will not work without.
      .catch(this.axiosFailure);
  }

  processSuccessfulResponseData(responseData){
    if(!_.isEmpty(responseData)){
      responseData = this.updateAccountFetchStamp(responseData)
    }
    return responseData;
  }

  updateAccountFetchStamp(accountData){
    accountData.fetchStamp = new Date().getTime();
    return accountData;
  }

  axiosSuccess(response){
    switch(response.config.method.toUpperCase()){
      case 'GET':
        this.assign = this.processSuccessfulResponseData(response.data);
        return {fetched: true, notification: {}};
      case "POST":
        return {type: SnotifyStyle.success, message: "New account created"};
      case "PUT":
        return {type: SnotifyStyle.success, message: "Account updated"};
      // case "DELETE":
      //   return {deleted: true, notification: {type: SnotifyStyle.success, message: "Account was deleted"}}
      default:
        // do nothing
    }
  }

  save(accountData){
    let accountId = parseInt(accountData.id);
    delete accountData.id;
    if(_.isNumber(accountId) && !isNaN(accountId)){
      // update account
      return Axios.put(this.uri+accountId, accountData)
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    } else {
      // new account
      return Axios
        .post(
          this.uri.replace(/\/$/, ''),
          accountData,
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
  //                 return {type: SnotifyStyle.info, message: "No accounts currently available"};
  //             case 500:
  //             default:
  //                 return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve accounts"};
  //         }
  //     }
  // }

  // getInstitution(accountId){
  //     accountId = parseInt(accountId);
  //     let account = this.find(accountId);
  //     if(account.hasOwnProperty('institution_id')){
  //         return new Institutions().find(account.institution_id);
  //     } else {
  //         return {};  // couldn't find the account_type associated with the provided ID
  //     }
  // }

  // getAccountTypes(accountId){
  //     accountId = parseInt(accountId);
  //     return new AccountTypes().retrieve.filter(function(accountType){
  //         return accountType.hasOwnProperty('account_id') && accountId === accountType.account_id;
  //     });
  // }
}