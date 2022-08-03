import _ from 'lodash';
import {Tags} from "../tags";

export const tagsObjectMixin = {

    computed: {
        areTagsSet: function(){
                return !_.isEmpty(this.rawTagsData) && this.tagsObject.isFetched;
        },
        listTags: function(){
            return this.rawTagsData;
        },
        tagsObject: function(){
            return new Tags()
        },
        rawTagsData: function(){
            return this.tagsObject.retrieve;
        },
    }

};