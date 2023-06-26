import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";
import {baseState, baseActions, baseGetters} from './storeSkeleton';

export const useTagsStore = defineStore('tags', {

  state: function(){
    return {
      ...baseState('/api/tags'),
    }
  },

  getters: {
    ...baseGetters(),

    list(state){
      return _.sortBy(state.collection, ['name']);
    },

  },

  actions: {
    ...baseActions(),

    axiosFailure(error){
      if(error.response){
        switch(error.response.status){
          case 404:
            this.$reset()
            break;
          case 500:
          default:
            return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve tags"};
        }
      }
    },

  }

})