import _ from 'lodash';
import {AccountTypes} from "../account-types";

export const accountTypesObjectMixin = {
  data: function(){
    return {

    }
  },

  computed: {
    accountTypesObject: function(){
      return new AccountTypes();
    },
    rawAccountTypesData: function(){
      return this.accountTypesObject.retrieve;
    },
    areAccountTypesAvailable: function(){
      return !_.isEmpty(this.rawAccountTypesData);
    },
    listAccountTypes: function(){
      return _.orderBy(this.rawAccountTypesData, ['active', 'name'], ['desc', 'asc']);
    },
  }
};