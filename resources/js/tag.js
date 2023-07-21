import { SnotifyStyle } from 'vue-snotify';
import _ from "lodash";
import Axios from "axios";

export class Tag {

  constructor(){
    this.uri = '/api/tag/{tagId}';
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.status){
        case 404:
          // TODO: output a notification
          break;
        case 500:
        default:
          return {type: SnotifyStyle.error, message: "An error occurred while attempting to save tag"};
      }
    }
  }

  axiosSuccess(response) {
    switch(response.config.method.toUpperCase()){
      case "POST":
        return {type: SnotifyStyle.success, message: "New tag created"};
      case "PUT":
        return {type: SnotifyStyle.success, message: "Tag updated"};
      // case "DELETE":
      //   return {deleted: true, notification: {type: SnotifyStyle.success, message: "Entry was deleted"}}
      default:
        return {};
    }
  }

  save(tagData){
    let tagId = parseInt(tagData.id);
    delete tagData.id;
    if(_.isNumber(tagId) && !isNaN(tagId)){
      // update tag
      return Axios.put(this.uri.replace('{tagId}', tagId), tagData)
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    } else {
      // new tag
      return Axios
        .post(
          this.uri.replace('/{tagId}', ''),
          tagData,
          {validateStatus:function(status){
            return status === 201
          }}
        )
        .then(this.axiosSuccess)
        .catch(this.axiosFailure);
    }
  }

}