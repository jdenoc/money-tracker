import _ from 'lodash';
import {Tags} from "../tags";

export const tagsObjectMixin = {
    data: function(){
        return {
            tagsObject: new Tags(),
        }
    },

    computed: {
        rawTagsData: function(){
            return this.tagsObject.retrieve;
        },
        listTagsAsObject: function(){
            return this.rawTagsData.reduce(function(result, item){
                result[item.id] = item.name;
                return result;
            }, {});
        },
        areTagsSet: function(){
            return !_.isEmpty(this.rawTagsData);
        },
    },

    mounted: function(){
        this.tagsObject.fetch();
    }
};