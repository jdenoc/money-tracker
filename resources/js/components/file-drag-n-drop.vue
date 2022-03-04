<template>
<!--  <file-pond-->
<!--      v-show="isVisible"-->
<!--      v-bind="filePondOptions"-->
<!--      v-model:files="dragNDropFiles"-->
<!--      v-bind:disabled="isFilePondDisabled"-->
<!--      v-on:updatefiles="handleFilePondFileUpdate"-->
<!--  />-->

  <!-- FIXME: not working -->
  <vue-dropzone
      v-bind:options="dropzoneOptions"
      v-on:vdropzone-success="handleDropzoneUploadSuccess"
      v-on:vdropzone-error="handleDropzoneUploadError"
      v-on:vdropzone-removed-file="handleDropzoneUploadRemoval"
      v-show="isVisible"
  ></vue-dropzone>
</template>

<script lang="js">
// import _ from "lodash";
// // const EMIT_UPDATE_ATTACHMENTS = 'update:attachments';
import vue2Dropzone from "vue2-dropzone";
import {SnotifyStyle} from "vue-snotify";

export default {
  name: "file-drag-n-drop",
  components: {
    VueDropzone: vue2Dropzone,
  },
  props: {
    attachments: {type: Array, default: []},
    id: {type: String, required: true},
    isVisible: {type: Boolean, default: true},
  },
  data: function(){
    return {
      dragNDropFiles: [],
      uploadFiles: [],
    }
  },
  computed: {
    dropzoneOptions: function(){
      return {
        id: this.id+'-file-upload',
        ref: this.getDropzoneRef,
        url: '/attachment/upload',
        method: 'post',
        addRemoveLinks: true,
        paramName: 'attachment',
        params: {_token: this.uploadToken},
        dictDefaultMessage: this.defaultMessage,
        hiddenInputContainer: '#'+this.id,
        init: function(){
          document.querySelector('#'+this.id+' .dz-hidden-input').setAttribute('id', this.id+'-hidden-file-input');
        }.bind(this)
      }
    },
    defaultMessage: function(){
      return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />' +
        '</svg>Drag & Drop';
    },
    getDropzoneRef: function(){
      return this.id+'.dropzone';
    },
    uploadToken: function(){
      return document.querySelector("meta[name='csrf-token']").getAttribute('content');
    },
//     uploadFiles: {
//       get: function (){
//         return this.attachments;
//       },
//       set: function (value){
//         this.$emit(EMIT_UPDATE_ATTACHMENTS, value);
//       }
//     },
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
    handleDropzoneUploadError(file, message, xhr){
      // response: {'error'}
      this.sendNotification(SnotifyStyle.warning, "File upload failure: "+message.error);
    },
    handleDropzoneUploadRemoval(file){
      let removedAttachmentObject = JSON.parse(file.xhr.response);
      this.uploadFiles = this.uploadFiles.filter(function(attachment){
        return attachment.uuid !== removedAttachmentObject.uuid;
      });
    },
    handleDropzoneUploadSuccess(file, response){
      // response: {'uuid', 'name', 'tmp_filename'}
      this.uploadFiles.push(response);
      this.sendNotification(SnotifyStyle.info, "Uploaded: "+response.name);
    },

//     handleFilePondFormData: function(formData){
//       formData.append('_token',this.uploadToken);
//       return formData;
//     },

//     handleFilePondFileUpdate: function(files){
//       // keeps files
//       let currentFiles = this.uploadFiles
//       let remainingFiles = [];
//       _.forEach(files, function(f){
//         let filteredFiles = currentFiles.filter(function (uploadedFile) {
//               return uploadedFile.uuid === f.serverId;
//             }
//         );
//         if(!_.isEmpty(filteredFiles)){
//           remainingFiles.push(filteredFiles[0])
//         }
//       });
//       this.uploadFiles = remainingFiles;
//     },
    sendNotification: function(notificationType, notificationMessage){
      this.$eventHub.broadcast(
          this.$eventHub.EVENT_NOTIFICATION(),
          {type: notificationType, message: notificationMessage}
      );
    },
//     handleBroadcastEvent: function(broadcastPayload){
//       if(broadcastPayload.modal === this.id){
//         switch(broadcastPayload.task){
//           case 'disable':
//             this.disable();
//             break;
//           case 'enable':
//             this.enable();
//             break;
//           case 'clear':
//             this.clearFiles();
//             break;
//           default:
//             console.warn('Unknown task ['+broadcastPayload.task+'] sent by ['+broadcastPayload.modal+'] to ['+this.$eventBus.EVENT_FILE_DROP_UPDATE+']');
//             break;
//         }
//       }
//     }
  },
//   created() {
//     this.$eventBus.listen(this.$eventBus.EVENT_FILE_DROP_UPDATE(), this.handleBroadcastEvent);
//   }
}
</script>

<style lang="scss" scoped>
@import '~dropzone/dist/min/dropzone.min.css';
@import "~vue2-dropzone/dist/vue2Dropzone.min.css";
</style>