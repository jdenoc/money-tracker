import _ from 'lodash';
import {Entries} from "../entries";

export const entriesObjectMixin = {
    data: function(){
        return {
            dataLoaded: false,
            entriesObject: new Entries(),
            largeBatchEntryData: [],
        }
    },

    computed: {
        MAX_ENTRY_COUNT: function(){ return 50; },
        rawEntriesData: function(){
            return this.entriesObject.retrieve;
        },
        areEntriesAvailable: function(){
            return !_.isEmpty(this.rawEntriesData);
        },
    },

    methods: {
        fetchData: function(filterParameters){
            this.dataLoaded = false;
            this.entriesObject
                .fetch(0, filterParameters)
                .then(function(notification){
                    this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
                }.bind(this))
                .finally(this.dataHasBeenFetched);
        },
        multiPageDataFetch: function(filterParameters={}){
            // reset for a new request
            this.dataLoaded = false;
            this.largeBatchEntryData = [];

            // init data from page 0
            this.largeBatchEntryData = this.rawEntriesData;
            this.chainBatchRequest(0, filterParameters);
        },
        dataHasBeenFetched: function(){
            this.dataLoaded = true;
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        },
        chainBatchRequest: function(fetchCount, filterParameters, totalPageCount=null){
            this.entriesObject
                .fetch(fetchCount, filterParameters)
                .then(function(notification){
                    // fire off a notification if needed
                    this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);

                    this.largeBatchEntryData = this.largeBatchEntryData.concat(this.rawEntriesData);

                    fetchCount++;
                    if(totalPageCount == null){
                        totalPageCount = Math.ceil(this.entriesObject.count/this.MAX_ENTRY_COUNT);
                    }

                    if(fetchCount < totalPageCount){
                        this.chainBatchRequest(fetchCount, filterParameters, totalPageCount)
                    } else {
                        this.dataLoaded = true;
                        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
                    }
                }.bind(this))
        }
    }
};