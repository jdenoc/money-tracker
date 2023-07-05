import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import _ from "lodash";
import {useInstitutionsStore} from "./stores/institutions";

export class Institution {

  constructor(){
    this.uri = '/api/institution/{institutionId}';
    // this.fetched = false;
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.config.method.toUpperCase()){
        case 'DELETE':
          switch (error.response.status){
            case 404:
              return {type: SnotifyStyle.warning, message: "Institution not found"};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to disabled institution"};
          }
        case 'GET':
          switch(error.response.status){
            case 404:
              return {fetched: false, notification: {type: SnotifyStyle.info, message: "Institution not found"}};
            case 500:
            default:
              return {fetched: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve an institution"}};
          }
        case 'PATCH':
          switch (error.response.status){
            case 404:
              return {type: SnotifyStyle.warning, message: "Institution not found"};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to enable institution"};
          }
        case 'PUT':
          switch(error.response.status){
            case 400:
              return {type: SnotifyStyle.warning, message: error.response.data.error};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to update institution"};
          }
        case 'POST':
          switch(error.response.status){
            case 400:
              return {type: SnotifyStyle.warning, message: error.response.data.error};
            case 500:
            default:
              return {type: SnotifyStyle.error, message: "An error occurred while attempting to create an institution"};
          }
        default:
          return {type: SnotifyStyle.error, message: "An error occurred while attempting an unsupported request"};
      }
    }
  }

  axiosSuccess(response){
    switch(response.config.method.toUpperCase()){
      case 'DELETE':
        return {type: SnotifyStyle.success, message: "Institution has been disabled"};
      case 'GET':
        if(!_.isEmpty(response.data)){
          let institutionData = _.clone(response.data);
          institutionData.fetchStamp = new Date().getTime()
          let institutionIndex = useInstitutionsStore().collection.findIndex(function(institution){
            return institution.id === institutionData.id;
          })
          if(institutionIndex === -1){
            // .length will always be an index above the current highest index
            useInstitutionsStore().collection[useInstitutionsStore().collection.length] = institutionData;
          } else {
            useInstitutionsStore().collection[institutionIndex] = institutionData;
          }
        }
        return {fetched: true, notification: {}};
      case 'PATCH':
        return {type: SnotifyStyle.success, message: "Institution has been enabled"};
      case 'POST':
        return {type: SnotifyStyle.success, message: "New Institution created"};
      case 'PUT':
        return {type: SnotifyStyle.success, message: "Institution updated"};
      default:
        return {};
    }
  }

  disable(institutionId){
    return Axios.delete(this.uri.replace('{institutionId}', institutionId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  enable(institutionId){
    return Axios.patch(this.uri.replace('{institutionId}', institutionId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  fetch(institutionId){
    return Axios.get(this.uri.replace('{institutionId}', institutionId))
      .then(this.axiosSuccess)
      .catch(this.axiosFailure);
  }

  save(institutionData){
    let institutionId = parseInt(institutionData.id);
    delete institutionData.id;
    if(_.isNumber(institutionId) && !isNaN(institutionId)){
      // update institution
      return Axios.put(this.uri.replace('{institutionId}', institutionId), institutionData)
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    } else {
      return Axios.post(
        this.uri.replace('/{institutionId}', ''),
        institutionData,
        {validateStatus:function(status){
          return status === 201
        }}
      )
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    }
  }

}