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
    listAccounts: function(){
      return _.orderBy(this.rawAccountsData, ['active', 'name'], ['desc', 'asc']);
    },
  },

  methods: {
    fetchAccounts: function(){
      return this.accountsObject.fetch();
    }
  }
};