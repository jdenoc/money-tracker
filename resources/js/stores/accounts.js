import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";
import {baseState, baseActions, baseGetters} from './storeSkeleton';

export const useAccountsStore = defineStore('accounts', {

  state: function(){
    return {
      ...baseState('/api/accounts')
    }
  },

  getters: {
    ...baseGetters(),

    list(state){
      return _.orderBy(state.collection, ['active', 'name'], ['desc', 'asc']);
    },

    listActive(state){
      let activeAccounts = _.filter(state.collection, ['active', true])
      return _.sortBy(activeAccounts, ['name']);
    },

    listInactive(state){
      let inactiveAccounts = _.filter(state.collection, ['active', false])
      return _.sortBy(inactiveAccounts, ['name']);
    },
  },

  actions: {
    ...baseActions(),

    axiosFailure(error){
      if(error.response){
        switch(error.response.status){
          case 404:
            this.$reset()
            return {type: SnotifyStyle.info, message: "No accounts currently available"};
          case 500:
          default:
            return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve accounts"};
        }
      }
    },
  }

})