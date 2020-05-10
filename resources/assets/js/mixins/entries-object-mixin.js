import _ from 'lodash';
import {Entries} from "../entries";

export const entriesObjectMixin = {
    data: function(){
        return {
            entriesObject: new Entries(),
        }
    },

    computed: {
        rawEntriesData: function(){
            return this.entriesObject.retrieve;
        },
        areEntriesAvailable: function(){
            return !_.isEmpty(this.rawEntriesData);
        },
    },

    methods: {
        fetchData: function(filterParameters){
            this.entriesObject
                .fetch(0, filterParameters)
                .then(function(notification){
                    this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
                }.bind(this))
                .finally(function(){
                    this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
                    this.dataLoaded = true;
                }.bind(this));
        },
    }
};