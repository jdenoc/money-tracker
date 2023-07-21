import {useEntriesStore} from "../stores/entries";

export const batchEntriesMixin = {
  data: function(){
    return {
      batchedEntriesLoaded: false,
      largeBatchEntryData: [],
    }
  },

  computed: {
    MAX_ENTRY_COUNT: function(){ return 50; },
  },

  methods: {
    fetchData: function(filterParameters){
      this.batchedEntriesLoaded = false;
      useEntriesStore()
        .fetch(0, filterParameters)
        .then(function(notification){
          this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
        }.bind(this))
        .finally(this.dataHasBeenFetched);
    },
    multiPageDataFetch: function(filterParameters={}){
      // reset for a new request
      this.batchedEntriesLoaded = false;
      this.largeBatchEntryData = [];

      // init data from page 0
      this.chainBatchRequest(0, filterParameters);
    },
    dataHasBeenFetched: function(){
      this.batchedEntriesLoaded = true;
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
    },
    chainBatchRequest: function(fetchCount, filterParameters, totalPageCount=null){
      useEntriesStore()
        .fetch(fetchCount, filterParameters)
        .then(function(notification){
          // fire off a notification if needed
          this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);

          this.largeBatchEntryData = this.largeBatchEntryData.concat(useEntriesStore().collection);

          fetchCount++;
          if(totalPageCount == null){
            totalPageCount = Math.ceil(useEntriesStore().totalCount/this.MAX_ENTRY_COUNT);
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