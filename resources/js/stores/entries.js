import { defineStore } from 'pinia'
import {SnotifyStyle} from "vue-snotify";
import axios from "axios";
import _ from "lodash";
import {baseState, baseActions, baseGetters} from './storeSkeleton';

export const useEntriesStore = defineStore('entries', {

  state: function(){
    return {
      ...baseState('/api/entries/')
    }
  },

  getters: {
    ...baseGetters(),
  },

  actions: {
    ...baseActions(),

    async fetch(pageNumber, filterParameters={}) {
      pageNumber = parseInt(pageNumber);
      pageNumber = isNaN(pageNumber) ? 0 : pageNumber;

      let requestParameters = {};
      for(let parameter in filterParameters){
        if(Object.prototype.hasOwnProperty.call(filterParameters, parameter)
          && !_.isNull(filterParameters[parameter])
          && (
            !_.isEmpty(filterParameters[parameter])
            || _.isBoolean(filterParameters[parameter])
            || (_.isNumber(filterParameters[parameter]) && filterParameters[parameter] !== 0)
          )
        ){
          requestParameters[parameter] = filterParameters[parameter];
        }
      }
      requestParameters.sort = this.sort;

      await axios.post(this.apiUri+pageNumber, requestParameters)
        .then(this.axiosSuccess.bind(this))
        .catch(this.axiosFailure.bind(this));
    },

    axiosFailure(error){
      if(error.response){
        this.$reset();
        switch(error.response.status){
          case 404:
            return {type: SnotifyStyle.info, message: "No entries were found"};
          case 500:
          default:
            // return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve "+(filterModal.active?"filtered":"")+" entries"};    // TODO: after filtering is in place
            return {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve entries"};
        }
      }
    },

  }

})