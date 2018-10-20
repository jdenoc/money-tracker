/**
 * Created by denis.oconnor on 2018-08-20
 */

import { Entry } from './entry';
import { ObjectBaseClass } from './objectBaseClass';
import Axios from "axios";

export class Attachment extends ObjectBaseClass {

    constructor(){
        super();
        this.uri = '/api/attachment/';
        this.entryObject = new Entry();
    }

    delete(attachmentUUID, entryId){
        // this.entryId = entryId;
        return Axios
            .delete(this.uri+attachmentUUID, {validateStatus: function(status){
                return status === 204;
            }})
            .then(function(response){
                return this.axiosSuccess(response, attachmentUUID, entryId);
            }.bind(this))
            .catch(this.axiosFailure);
    }

    axiosSuccess(response, attachmentUUID, entryId){
        // remove attachment from store
        let entry = this.entryObject.find(entryId);
        entry.attachments = entry.attachments.filter(function(attachment){
            return attachment.uuid !== attachmentUUID
        }.bind(this));
        entry = this.entryObject.updateEntryFetchStamp(entry);
        this.entryObject.assign = entry;

        // TODO: inform user of attachment deletion
        // notice.display(notice.typeInfo, "Attachment has been deleted");
        return entry;
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    // TODO: inform user of error
                    // notice.display(notice.typeDanger, 'Could not delete attachment');
                    break;
                case 500:
                default:
                    // TODO: send a notice - delete entry
                    // notice.display(notice.typeError, "An error occurred while attempting to delete entry attachment ["+entryId+"]");
                    break;
            }
        }
    }

}