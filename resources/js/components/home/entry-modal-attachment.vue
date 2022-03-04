<template>
  <div class="existing-attachment flex justify-between p-2 mb-2 rounded border-t border-gray-100 shadow-md">
    <div class="attachment-name p-2" v-text="name"></div>
    <div class="flex">
      <button class="view-attachment inline-flex justify-center rounded-md border border-gray-300 bg-white px-3 py-2 mr-1.5 opacity-75 hover:opacity-100"
          v-on:click="viewEntryAttachment"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </button>

      <button type="button" class="delete-attachment inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 bg-red-600 text-white opacity-95 hover:opacity-100"
          v-on:click="deleteEntryAttachment"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script lang="js">
import _ from 'lodash';
import {Attachment} from "../../attachment";
import {Entry} from "../../entry";

export default {
  name: "entry-modal-attachment",
  props: {
    entryId: Number,
    name: String,
    uuid: String,
  },
  data: function(){
    return {
      attachmentObject: new Attachment(),
      entryObject: new Entry(),
    }
  },
  methods: {
    viewEntryAttachment: function(){
      window.open("/attachment/"+this.uuid, "_blank");
    },
    deleteEntryAttachment: function(){
      if(confirm('Are you sure you want to delete attachment: '+this.name)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        this.attachmentObject
          .delete(this.uuid, this.entryId)
          .then(function(newEntryData){
            // inform user of attachment deletion
            this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: newEntryData.notification.type, message: newEntryData.notification.message.replace("%s", this.entryId)});
            delete newEntryData.notification;
            if(!_.isEmpty(newEntryData)){
              // update entryData in entry-modal component
              this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_UPDATE_DATA, newEntryData);
            }
          }.bind(this))
          .finally(function(){
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
          }.bind(this));
      }
    },
  }
}
</script>

<style scoped>
</style>