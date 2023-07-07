import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";
import {baseState, baseActions, baseGetters} from './storeSkeleton';

export const useAccountTypeTypesStore = defineStore('accountTypeTypes', {

  state: function () {
    return {
      ...baseState('/api/account-types/types'),
    }
  },

  getters: {
    ...baseGetters(),

    list(state){
      return _.sortBy(state.collection)
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

    axiosSuccess(response){
      this.collection = response.data;
    },
  },

})
