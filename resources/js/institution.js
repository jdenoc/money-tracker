import { ObjectBaseClass } from './objectBaseClass';
import { SnotifyStyle } from 'vue-snotify';
import Store from './store';
import _ from "lodash";
import Axios from "axios";

export class Institution extends ObjectBaseClass {

    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_INSTITUTIONS;
        this.uri = '/api/institution/';
        // this.fetched = false;
    }

    set assign(newValue){
        if(!_.isEmpty(newValue)){
            let institutions = this.retrieve;
            let institutionIndex = institutions.findIndex(function(institution){
                return institution.id === newValue.id;
            });
            if(institutionIndex !== -1){
                institutions[institutionIndex] = newValue;
            } else {
                institutions[institutions.length] = newValue; // institutions.length will always be an index above the current highest index
            }
            super.assign = institutions;
        }
    }

    fetch(institutionId){
        return Axios.get(this.uri+institutionId)
            .then(this.axiosSuccess.bind(this)) // NOTE: _DO NOT_ remove the ".bind(this)". Will not work without.
            .catch(this.axiosFailure);
    }

    processSuccessfulResponseData(responseData){
        if(!_.isEmpty(responseData)){
            responseData = this.updateInstitutionFetchStamp(responseData)
        }
        return responseData;
    }

    updateInstitutionFetchStamp(institutionData){
        institutionData.fetchStamp = new Date().getTime();
        return institutionData;
    }

    save(institutionData){
        let institutionId = parseInt(institutionData.id);
        delete institutionData.id;
        if(_.isNumber(institutionId) && !isNaN(institutionId)){
            // update institution
            return Axios.put(this.uri+institutionId, institutionData)
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
        } else {
            // new institution
            return Axios.post(
                    this.uri.replace(/\/$/, ''),
                    institutionData,
                    {validateStatus:function(status){
                        return status === 201
                    }}
                )
                .then(this.axiosSuccess)
                .catch(this.axiosFailure);
        }
    }

    axiosSuccess(response){
        switch(response.config.method.toUpperCase()){
            case 'GET':
                this.assign = this.processSuccessfulResponseData(response.data);
                return {fetched: true, notification: {}};
            case 'POST':
                return {type: SnotifyStyle.success, message: "New Institution created"};
            case 'PUT':
                return {type: SnotifyStyle.success, message: "Institution updated"};
            default:
                return {};
        }
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.config.method.toUpperCase()){
                case 'GET':
                    switch(error.response.status){
                        case 404:
                            return {fetched: false, notification: {type: SnotifyStyle.info, message: "Institution not found"}};
                        case 500:
                        default:
                            return {fetched: false, notification: {type: SnotifyStyle.error, message: "An error occurred while attempting to retrieve an institution"}};
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

}