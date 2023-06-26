import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";
import {baseState, baseGetters, baseActions} from './storeSkeleton'

export const useInstitutionsStore = defineStore('institutions', {

  state: function() {
    return {
      ...baseState('/api/institutions'),
    }
  },

  getters: {
    ...baseGetters(),

    list(state) {
      return _.orderBy(state.collection, ['active', 'name'], ['desc', 'asc']);
    },

    listActive(state) {
      let activeInstitutions = _.filter(state.collection, ['active', true]);
      return _.sortBy(activeInstitutions, ['name']);
    },
  },

  actions: {
    ...baseActions(),

    axiosFailure(error){
      if(error.response){
        switch(error.response.status){
          case 404:
            this.$reset()
            return {type: SnotifyStyle.info, message: "No institutions currently available"};
          case 500:
          default:
            return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve institutions"};
        }
      }
    },
  }

})