import { SnotifyStyle } from 'vue-snotify';
import Axios from "axios";
import {useEntriesStore} from "./stores/entries";

export class Attachment {

  constructor(){
    this.uri = '/api/attachment/{uuid}';
  }

  delete(attachmentUUID, entryId){
    return Axios
      .delete(
        this.uri.replace('{uuid}', attachmentUUID),
        {validateStatus: function(status){
          return status === 204;
        }}
      )
      .then(function(response){
        return this.axiosSuccess(response, attachmentUUID, entryId);
      }.bind(this))
      .catch(this.axiosFailure);
  }

  axiosSuccess(response, attachmentUUID, entryId){
    // remove attachment from store
    let entry = useEntriesStore().find(entryId);
    entry.attachments = entry.attachments.filter(function(attachment){
      return attachment.uuid !== attachmentUUID
    }.bind(this));
    entry.fetchStamp = new Date().getTime();
    let entryIndex = useEntriesStore().collection.findIndex(function(entryRecord){
      return entryRecord.id === entry.id
    })
    useEntriesStore().collection[entryIndex] = entry;

    // inform user of attachment deletion
    entry.notification = {type: SnotifyStyle.info, message: "Attachment has been deleted"};
    return entry;
  }

  axiosFailure(error){
    if(error.response){
      switch(error.response.status){
        case 404:
          return {notification: {type: SnotifyStyle.warning, message: "Could not delete attachment"}};
        case 500:
        default:
          return {notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to delete entry attachment [%s]"}};
      }
    }
  }

}