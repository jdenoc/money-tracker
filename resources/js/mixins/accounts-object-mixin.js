import _ from 'lodash';
import {Accounts} from "../accounts";

export const accountsObjectMixin = {
  computed: {
    accountsObject: function(){
      return new Accounts();
    },
    rawAccountsData: function(){
      return this.accountsObject.retrieve;
    },
    areAccountsAvailable: function(){
      return !_.isEmpty(this.rawAccountsData);
    },
  },

  methods: {
    fetchAccounts: function(){
      return this.accountsObject.fetch();
    }
  }
};