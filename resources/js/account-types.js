import {Accounts} from "./accounts";
import {ObjectBaseClass} from "./objectBaseClass";
import {SnotifyStyle} from "vue-snotify";
import Store from './store';
import Axios from "axios";

export class AccountTypes extends ObjectBaseClass {

  constructor(){
    super();

    this.apiUri = {
      default: '/api/account-types',
      types: '/api/account-types/types'
    }
    this.apiStoreTypes = {
      default: Store.getters.STORE_TYPE_ACCOUNT_TYPES,
      types: Store.getters.STORE_TYPE_ACCOUNT_TYPE_TYPES
    }

    this.storeType = this.apiStoreTypes.default;
    this.uri = this.apiUri.default;
  }

  fetchTypes(){
    this.storeType = this.apiStoreTypes.types;
    if(!this.isFetched){
      this.setFetchedState = true;
      return Axios.get(this.apiUri.types)
        .then(this.axiosSuccessTypes.bind(this))
        .catch(this.axiosFailure.bind(this));
    } else {
      return Promise.resolve({});
    }
  }

  get retrieveTypes(){
    this.storeType = this.apiStoreTypes.types;
    let types = super.retrieve;
    this.storeType = this.apiStoreTypes.default;
    return types;
  }

  axiosSuccessTypes(response){
    this.storeType = this.apiStoreTypes.types;
    this.assign = response.data;
    this.storeType = this.apiStoreTypes.default;
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
    if(Object.prototype.hasOwnProperty.call(accountType, 'account_id')){
      return new Accounts().find(accountType.account_id);
    } else {
      return {};  // couldn't find the account_type associated with the provided ID
    }
  }

  getNameById(accountTypeId) {
    let accountType = this.find(accountTypeId);
    if(Object.prototype.hasOwnProperty.call(accountType, 'name')){
      return accountType.name;
    } else {
      return "";  // couldn't find the account_type.name associated with the provided ID
    }
  }

}