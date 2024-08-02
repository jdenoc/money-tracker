import _ from 'lodash';
import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import {useEntriesStore} from "./stores/entries";

export class Entry {

  constructor(){
    this.uri = '/api/entry/{entryId}';
    // this.fetched = false;
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.config.method.toUpperCase()){
        case "GET":
          switch(error.response.status){
            case 404:
              return {fetched: false, notification: {type: SnotifyStyle.warning, message: "Entry does not exist"}};
            case 500:
            default:
              return {fetched: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve entry"}};
          }
        case "POST":
          switch(error.response.status){
            case 400:
              return {type: SnotifyStyle.warning, message: error.response.data.error};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to create an entry"};
          }
        case "PUT":
          switch(error.response.status){
            case 400:
            case 404:
              return {type: SnotifyStyle.warning, message: error.response.data.error};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to update entry [%s]"};
          }
        case "DELETE":
          switch(error.response.status){
            case 404:
              return {deleted: false, notification: {type: SnotifyStyle.warning, message: "Entry [%s] does not exist and cannot be deleted"}};
            case 500:
            default:
              return {deleted: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to delete entry [%s]"}};
          }
        default:
          return {};
      }
    }
  }

  axiosSuccess(response){
    switch(response.config.method.toUpperCase()){
      case 'GET':
        if(!_.isEmpty(response.data)){
          let entryData = response.data;
          entryData.fetchStamp = new Date().getTime();

          let entryIndex = useEntriesStore().collection.findIndex(function(entry){
            return entry.id === entryData.id;
          });
          if(entryIndex === -1){
            // .length will always be an index above the current highest index
            useEntriesStore().collection[useEntriesStore().collection.length] = entryData
          } else {
            useEntriesStore().collection[entryIndex] = entryData
          }
        }
        return {fetched: true, notification: {}};
      case "POST":
        return {type: SnotifyStyle.success, message: "New entry created"};
      case "PUT":
        return {type: SnotifyStyle.success, message: "Entry updated"};
      case "DELETE":
        return {deleted: true, notification: {type: SnotifyStyle.success, message: "Entry was deleted"}}
      default:
        return {};
    }
  }

  axiosSuccessTransfer(){
    return {type: SnotifyStyle.success, message: "Transfer entry created"};
  }

  delete(entryId){
    return Axios
      .delete(
        this.uri.replace('{entryId}', entryId),
        {validateStatus: function(status){
          return status === 204;
        }}
      )
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  fetch(entryId){
    return Axios.get(this.uri.replace('{entryId}', entryId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  save(entryData){
    let entryId = parseInt(entryData.id);
    delete entryData.id;
    if(_.isNumber(entryId) && !isNaN(entryId)){
      // update entry
      return Axios.put(this.uri.replace('{entryId}', entryId), entryData)
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    } else {
      // new entry
      return Axios
        .post(
          this.uri.replace('/{entryId}', ''),
          entryData,
          {validateStatus:function(status){
            return status === 201
          }}
        )
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    }
  }

  saveTransfer(transferData){
    return Axios.post(this.uri.replace('{entryId}','transfer'), transferData)
      .then(this.axiosSuccessTransfer)
      .catch(this.axiosFailure);
  }

}