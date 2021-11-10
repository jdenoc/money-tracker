<template>
    <div class="existing-attachment box field is-grouped">
        <div class="control is-expanded">
            <div class="attachment-name" v-text="name"></div>
        </div>
        <div class="control">
            <button class="view-attachment button" v-on:click="viewEntryAttachment"><i class="fas fa-search"></i></button>
            <button class="delete-attachment button is-danger" v-on:click="deleteEntryAttachment"><i class="fas fa-trash-alt"></i></button>
        </div>
    </div>
</template>

<script>
    import {Attachment} from "../attachment";
    import {Entry} from "../entry";

    export default {
        name: "entry-modal-attachment",
        props: [ 'uuid','name', 'entryId'],
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
                    this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());

                    this.attachmentObject
                        .delete(this.uuid, this.entryId)
                        .then(function(newEntryData){
                            // inform user of attachment deletion
                            this.$eventBus.broadcast(this.$eventBus.EVENT_NOTIFICATION(), {type: newEntryData.notification.type, message: newEntryData.notification.message.replace("%s", this.entryId)});
                            delete newEntryData.notification;
                            if(!_.isEmpty(newEntryData)){
                                // update entryData in entry-modal component
                                this.$eventBus.broadcast(this.$eventBus.EVENT_ENTRY_MODAL_UPDATE_DATA(), newEntryData);
                            }
                        }.bind(this))
                        .finally(function(){
                            this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_HIDE());
                        }.bind(this));
                }
            },
        }
    }
</script>

<style scoped>
    .box{
        padding: 0.5rem
    }
    .attachment-name{
        padding: 0.375rem 0 0.375rem 1.25rem;
    }
</style>