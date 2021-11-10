<template>
  <file-pond
      v-show="isVisible"
      v-bind="filePondOptions"
      v-model:files="dragNDropFiles"
      v-bind:disabled="isFilePondDisabled"
      v-on:updatefiles="handleFilePondFileUpdate"
  />
</template>

<script>
import _ from "lodash";
import vueFilePond from "vue-filepond";
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
const FilePond = vueFilePond(
    FilePondPluginFileValidateType    // Needed to stop a warning that states that the acceptedFileTypes property does not exist
);

const EMIT_UPDATE_ATTACHMENTS = 'update:attachments';

export default {
  name: "FileDragNDrop",
  components: {
    FilePond
  },
  emits:[
    EMIT_UPDATE_ATTACHMENTS
  ],
  props: {
    id: {type: String},
    isVisible: {type: Boolean, default: true},
    attachments: {type: Array, default: []}
  },
  data: function(){
    return {
      dragNDropFiles: [],
      isFilePondDisabled: false,
    }
  },
  computed: {
    getFilePondRef: function(){
      return this.id+'.filepond';
    },
    getComponentId: function(){
      return this.id+'-file-upload'
    },
    getAttachmentUploadUrl: function(){
      return this.filePondOptions.server.process.url;
    },
    uploadToken: function(){
      return document.querySelector("meta[name='csrf-token']").getAttribute('content');
    },

    uploadFiles: {
      get: function (){
        return this.attachments;
      },
      set: function (value){
        this.$emit(EMIT_UPDATE_ATTACHMENTS, value);
      }
    },

    filePondOptions: function(){
      return {
        id: this.getComponentId,
        ref: this.getFilePondRef,
        name: 'attachment',
        server: {
          fetch:null,
          load: null,
          patch: null,
          process: {
            url: '/attachment/upload',
            method: 'POST',
            withCredentials: false,
            ondata: this.handleFilePondFormData,
            onload: this.handleFilePondUploadSuccess,
            onerror: this.handleFilePondUploadError,
          },
          restore:null,
          revert: null, // Stops server calls when file is "removed" from drag-n-drop area
        },
        allowMultiple: true,
        allowPaste: false,
        allowReplace: false,
        allowSyncAcceptAttribute: false,
        acceptedFileTypes:["image/*", "text/*", "application/pdf"],
      }
    },
  },
  methods: {
    disable: function(){
      this.isFilePondDisabled = true;
    },
    enable: function(){
      this.isFilePondDisabled = false;
    },
    clearFiles: function(){
      this.$refs[this.getFilePondRef].removeFiles();
    },

    handleFilePondFormData: function(formData){
      formData.append('_token',this.uploadToken);
      return formData;
    },
    handleFilePondUploadSuccess: function(response){
      // response: {'uuid', 'name', 'tmp_filename'}
      let responseObject = JSON.parse(response);
      this.uploadFiles.push(responseObject);
      this.sendNotification('info', "Uploaded: "+responseObject.name);
      return responseObject.uuid; // assign fileId value
    },
    handleFilePondUploadError: function(response){
      // response: {'error'}
      let responseObject = JSON.parse(response);
      this.sendNotification('warn', "File upload failure: "+responseObject.error)
    },
    handleFilePondFileUpdate: function(files){
      // keeps files
      let currentFiles = this.uploadFiles
      let remainingFiles = [];

      _.forEach(files, function(f){
        let filteredFiles = currentFiles.filter(function (uploadedFile) {
              return uploadedFile.uuid === f.serverId;
            }
        );
        if(!_.isEmpty(filteredFiles)){
          remainingFiles.push(filteredFiles[0])
        }
      });
      this.uploadFiles = remainingFiles;
    },

    sendNotification: function(notificationType, notificationMessage){
      this.$eventBus.broadcast(
          this.$eventBus.EVENT_NOTIFICATION(),
          {type: notificationType, message: notificationMessage}
      );
    },
    handleBroadcastEvent: function(broadcastPayload){
      if(broadcastPayload.modal === this.id){
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
            console.warn('Unknown task ['+broadcastPayload.task+'] sent by ['+broadcastPayload.modal+'] to ['+this.$eventBus.EVENT_FILE_DROP_UPDATE+']');
            break;
        }
      }
    }
  },
  created() {
    this.$eventBus.listen(this.$eventBus.EVENT_FILE_DROP_UPDATE(), this.handleBroadcastEvent);
  }
}
</script>

<style lang="scss" scoped>
@import "~filepond/dist/filepond.min.css";
</style>