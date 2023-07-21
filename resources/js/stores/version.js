import { defineStore } from 'pinia'
import _ from 'lodash'
import {baseActions} from "./storeSkeleton";

export const useVersionStore = defineStore('version', {

  state: function() {
    return {
      apiUri: '/api/version',
      version: ''
    }
  },

  getters: {
    isSet(state) {
      return !_.isEmpty(state.version)
    }
  },

  actions: {
    ...baseActions(),

    axiosSuccess(response){
      this.version = response.data;
    },

    axiosFailure(error){
      if(error.response){
        switch(error.response.status){
          case 404:
          case 500:
          default:
            this.version = "N/A";
            break;
        }
      }
    },
  }

})