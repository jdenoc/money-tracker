import _ from 'lodash';
import {AccountTypes} from "../account-types";

export const accountTypesObjectMixin = {
  data: function(){
    return {
      accountTypesObject: new AccountTypes()
    }
  },

  computed: {
    rawAccountTypesData: function(){
      return this.accountTypesObject.retrieve;
    },
    areAccountTypesAvailable: function(){
      return !_.isEmpty(this.rawAccountTypesData);
    },
  },

  methods: {
    fetchAccountTypes: function(){
      return this.accountTypesObject.fetch();
    }
  }
};