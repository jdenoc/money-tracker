import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";
import {baseState, baseActions, baseGetters} from './storeSkeleton';

export const useAccountTypesStore = defineStore('accountTypes', {

  state: function () {
    return {
      ...baseState('/api/account-types'),
    }
  },

  getters: {
    ...baseGetters(),

    list(state){
      return _.orderBy(state.collection, ['active', 'name'], ['desc', 'asc']);
    },

    listActive(state){
      let activeAccountTypes = _.filter(state.collection, ['active', true]);
      return _.sortBy(activeAccountTypes, ['name']);
    },
  },

  actions: {
    ...baseActions(),

    axiosFailure(error){
      if(error.response){
        switch(error.response.status){
          case 404:
            this.$reset();
            return {type: SnotifyStyle.info, message: "No account types currently available"};
          case 500:
          default:
            return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve account types"};
        }
      }
    },

  },

})
