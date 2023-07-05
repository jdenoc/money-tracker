import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import _ from "lodash";
import {useAccountsStore} from "./stores/accounts";

export class Account {

  constructor(){
    this.uri = '/api/account/{accountId}';
    // this.fetched = false;
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.config.method.toUpperCase()){
        case 'GET':
          switch(error.response.status){
            case 404:
              return {fetched: false, notification: {type: SnotifyStyle.info, message: "Account not found"}};
            case 500:
            default:
              return {fetched: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve an account"}};
          }
        default:
          return {};
      }
    }
  }

  axiosSuccess(response){
    switch(response.config.method.toUpperCase()){
      case 'DELETE':
        return {type: SnotifyStyle.success, message: "Account has been disabled"};
      case 'GET':
        if(!_.isEmpty(response.data)){
          let accountData = _.clone(response.data);
          accountData.fetchStamp = new Date().getTime();

          let accountIndex = useAccountsStore().collection.findIndex(function(account){
            return account.id === accountData.id;
          });
          if(accountIndex === -1){
            // .length will always be an index above the current highest index
            useAccountsStore().collection[useAccountsStore().collection.length] = accountData;
          } else {
            useAccountsStore().collection[accountIndex] = accountData;
          }
        }
        return {fetched: true, notification: {}};
      case 'PATCH':
        return {type: SnotifyStyle.success, message: "Account has been reactivated"};
      case "POST":
        return {type: SnotifyStyle.success, message: "New account created"};
      case "PUT":
        return {type: SnotifyStyle.success, message: "Account updated"};
      default:
        return {};
    }
  }

  disable(accountId){
    return Axios.delete(this.uri.replace('{accountId}', accountId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  enable(accountId){
    return Axios.patch(this.uri.replace('{accountId}', accountId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  fetch(accountId){
    return Axios.get(this.uri.replace('{accountId}', accountId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  save(accountData){
    let accountId = parseInt(accountData.id);
    delete accountData.id;
    if(_.isNumber(accountId) && !isNaN(accountId)){
      // update account
      return Axios.put(this.uri.replace('{accountId}', accountId), accountData)
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    } else {
      // new account
      return Axios
        .post(
          this.uri.replace('/{accountId}', ''),
          accountData,
          {validateStatus:function(status){
            return status === 201
          }}
        )
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    }
  }

}