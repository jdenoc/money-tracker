import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import _ from "lodash";
import {useAccountTypesStore} from "./stores/accountTypes";

export class AccountType {

  constructor(){
    this.uri = '/api/account-type/{accountTypeId}';
    // this.fetched = false;
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.status){
        case 404:
          this.assign = [];
          return {type: SnotifyStyle.info, message: "No account-type currently available"};
        case 500:
        default:
          return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve account-type"};
      }
    }
  }

  axiosSuccess(response){
    switch(response.config.method.toUpperCase()){
      case "DELETE":
        return {type: SnotifyStyle.success, message: "Account-type has been disabled"};
      case 'GET':
        if(!_.isEmpty(response.data)){
          let accountTypeData = _.clone(response.data);
          accountTypeData.fetchStamp = new Date().getTime();
          let accountTypeIndex = useAccountTypesStore().collection.findIndex(function(accountType){
            return accountType.id === accountTypeData.id;
          })
          if(accountTypeIndex === -1){
            // .length will always be an index above the current highest index
            useAccountTypesStore().collection[useAccountTypesStore().collection.length] = accountTypeData;
          } else {
            useAccountTypesStore().collection[accountTypeIndex] = accountTypeData;
          }
        }
        return {fetched: true, notification: {}};
      case 'PATCH':
        return {type: SnotifyStyle.success, message: "Account-type has been reactivated"};
      case "POST":
        return {type: SnotifyStyle.success, message: "New Account-type created"};
      case "PUT":
        return {type: SnotifyStyle.success, message: "Account-type updated"};
      default:
        return {};
    }
  }

  disable(accountTypeId){
    return Axios.delete(this.uri.replace('{accountTypeId}', accountTypeId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  enable(accountTypeId){
    return Axios.patch(this.uri.replace('{accountTypeId}', accountTypeId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  fetch(accountTypeId){
    return Axios.get(this.uri.replace('{accountTypeId}', accountTypeId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
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

}