<template>
  <vue-dropzone
      v-bind:id="getId+'-file-upload'"
      v-bind:ref="getDropzoneRef"
      v-bind:options="dropzoneOptions"
      v-on:vdropzone-success="handleUploadSuccess"
      v-on:vdropzone-error="handleUploadError"
      v-on:vdropzone-removed-file="handleUploadRemoval"
  ></vue-dropzone>
</template>

<script lang="js">
import _ from "lodash";
import vue2Dropzone from "vue2-dropzone";
import {SnotifyStyle} from "vue-snotify";

const EMIT_UPDATE_ATTACHMENTS = 'update:attachments';

export default {
  name: "file-drag-n-drop",
  components: {
    VueDropzone: vue2Dropzone,
  },
  props: {
    attachments: {type: Array, default: function(){ return [] }},
    id: {type: String, required: true},
  },
  data: function(){
    return {
      dragNDropFiles: this.attachments,
    }
  },
  computed: {
    dropzoneOptions: function(){
      return {
        url: '/attachment/upload',
        method: 'post',
        addRemoveLinks: true,
        paramName: 'attachment',
        params: {_token: this.uploadToken},
        dictDefaultMessage: this.defaultMessage,
        hiddenInputContainer: '#'+this.getId,
        init: function(){
          document.querySelector('#'+this.getId+' .dz-hidden-input').setAttribute('id', this.getId+'-hidden-file-input');
        }.bind(this)
      }
    },
    defaultMessage: function(){
      return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />' +
        '</svg>Drag & Drop';
    },
    getDropzoneRef: function(){
      return _.camelCase(this.id)+'.dropzone';
    },
    getId: function(){
      return _.kebabCase(this.id);
    },
    uploadToken: function(){
      return document.querySelector("meta[name='csrf-token']").getAttribute('content');
    },
  },
  methods: {
    clearFiles: function(){
      this.$refs[this.getDropzoneRef].removeAllFiles();
    },
    disable: function(){
      this.$refs[this.getDropzoneRef].disable();
    },
    enable: function(){
      this.$refs[this.getDropzoneRef].enable();
    },
    handleUploadError(file, message){
      // response: {'error'}
      this.sendNotification(SnotifyStyle.warning, "File upload failure: "+message.error);
    },
    handleUploadRemoval(file){
      let removedAttachmentObject = JSON.parse(file.xhr.response);
      this.dragNDropFiles = this.dragNDropFiles.filter(function(attachment){
        return attachment.uuid !== removedAttachmentObject.uuid;
      });
    },
    handleUploadSuccess(file, response){
      // response: {'uuid', 'name', 'tmp_filename'}
      this.dragNDropFiles.push(response);
      this.sendNotification(SnotifyStyle.info, "Uploaded: "+response.name);
    },

    sendNotification: function(notificationType, notificationMessage){
      this.$eventHub.broadcast(
        this.$eventHub.EVENT_NOTIFICATION,
        {type: notificationType, message: notificationMessage}
      );
    },
    handleBroadcastEvent: function(broadcastPayload){
      if (broadcastPayload.modal === this.getId){
        switch(broadcastPayload.task){
          case 'disable':
            this.disable();
            break;
          case 'enable':
            this.enable();
            break;
          case 'clear':
            this.clearFiles();
            break;
          default:
            console.warn('Unknown task [' + broadcastPayload.task + '] sent by [' + broadcastPayload.modal + '] to [' + this.$eventHub.EVENT_FILE_DROP_UPDATE + ']');
            break;
        }
      }
    }
  },
  watch:{
    attachments: function(newValue){
      // changes passed to the prop.attachments after initial setup
      this.dragNDropFiles = newValue;
    },
    dragNDropFiles: function(newValue){
      // changes made to the data.dragNDropFiles
      this.$emit(EMIT_UPDATE_ATTACHMENTS, newValue);
    }
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_FILE_DROP_UPDATE, this.handleBroadcastEvent);
  }
}
</script>

<!--
vite/vue2 setup is broken for scoped styles stored in node_modules.
style element order is all mixed up
-->
<style lang="css" src="vue2-dropzone/dist/vue2Dropzone.min.css"></style>