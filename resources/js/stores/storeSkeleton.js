import _ from "lodash";
import axios from 'axios'

export function baseState(apiUri) {
  return {
    apiUri: apiUri,
    collection: [],
    totalCount: 0,
  }
}

export function baseGetters() {
  return {
    find(state){
      return function(id){
        id = parseInt(id);
        let object = state.collection.find(function(object){ return object.id === id});
        return _.isUndefined(object) ? {} : object;
      }
    },

    isSet(state) {
      return !_.isEmpty(state.collection)
    }
  }
}

export function baseActions() {
  return {
    axiosFailure(error){
      // NOTE: most collections will need to process failures differently
      //       it is ADVISED that you override this function
      if(error.response){
        switch(error.response.status){
          case 400:
          case 404:
          case 500:
          default:
            this.$reset();
            return {};
        }
      }
    },

    axiosSuccess(response){
      // convert responseData object into an array
      let responseData = response.data;
      this.totalCount = parseInt(responseData.count);
      delete responseData.count;
      this.collection = Object.values(responseData)
        .map(function(data){
          // add a fetchStamp property to each data node
          data.fetchStamp = null;
          return data;
        });
    },

    async fetch(){
      return axios.get(this.apiUri)
        .then(this.axiosSuccess.bind(this))
        .catch(this.axiosFailure.bind(this))
    },

    $reset(){
      this.collection = [];
      this.totalCount = 0
    }
  }
}