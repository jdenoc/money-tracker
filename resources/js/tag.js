import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';
import _ from "lodash";
import Axios from "axios";

export class Tag extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_TAGS;
        this.uri = '/api/tag/';
    }

    axiosSuccess(response) {
        switch(response.config.method.toUpperCase()){
            case "POST":
                return {type: SnotifyStyle.success, message: "New tag created"};
            case "PUT":
                return {type: SnotifyStyle.success, message: "Tag updated"};
            // case "DELETE":
            //     return {deleted: true, notification: {type: SnotifyStyle.success, message: "Entry was deleted"}}
            default:
                return {};
        }
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

    save(tagData){
        let tagId = parseInt(tagData.id);
        delete tagData.id;
        if(_.isNumber(tagId) && !isNaN(tagId)){
            // update tag
            return Axios.put(this.uri+tagId, tagData)
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
        } else {
            // new tag
            return Axios
                .post(
                    this.uri.replace(/\/$/, ''),
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